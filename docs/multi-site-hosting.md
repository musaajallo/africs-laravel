# Multi-site hosting on one Forge server

How to host several apps — the Africs site/ERP plus **limitlessafrics.org** and
**hackathon.org**, each on its own domain — on a single Laravel Forge server,
without paying for extra servers.

**Verdict: supported and recommended for this tier.** Forge is built to run many
sites per server. Three related small apps on one properly-isolated box is the
right call. Only split onto a second server once one app grows real sustained
traffic, or when you decide the ERP (secrets vault + financial data) should not
share a host with a public event site at all — a security-posture choice, not a
technical requirement.

## The shared-server tradeoff

You are buying one failure domain: server down = all three sites down, and one
app's runaway process (memory leak, fork bomb, a `npm` build) can degrade its
neighbours. Acceptable here — just know that is the deal.

## Setup per site

Each app is a separate **Forge → New Site**: its own domain, nginx vhost, deploy
script, `.env`, SSL cert, and (if needed) queue worker. Repeat for each domain.

### 1. Isolate the sites from each other

By default Forge runs every site as the same `forge` Linux user, so any app can
read the others' `.env` — DB credentials, `APP_KEY`, and the vault-encrypting key.
For unrelated apps that matters:

- **Enable "Isolated Users"** when creating each site (Forge gives the site its
  own system user; other sites can't read its files).
- **One MySQL database + one dedicated DB user per site**, each user scoped to
  only its own database. Never share a DB user across sites.
- Shared Redis is fine, but set a distinct **`REDIS_PREFIX`** per app (or a
  separate Redis DB index) so cache/session/queue keys don't collide.

### 2. Databases

| Site | DB name (example) | DB user |
| --- | --- | --- |
| africsinc.com | `africs` | `africs` |
| limitlessafrics.org | `limitlessafrics` | `limitlessafrics` |
| hackathon.org | `hackathon` | `hackathon` |

Forge → server → Database → create each database and user.

### 3. Memory — the real constraint

Each site adds a PHP-FPM pool; each queue worker is a persistent PHP process
(~30–60 MB). The thing that actually breaks small servers is the frontend build:
`npm run build` (Vite/Rollup) can spike **500 MB–1 GB** and trigger the OOM
killer, which usually takes MySQL down with it.

Rough budget for three Inertia + Vite apps:

| Server RAM | Approach |
| --- | --- |
| **4 GB+** | Build on-server (keep the `npm ci && npm run build` step in each deploy script). Comfortable. |
| **2 GB** | Workable with **3–4 GB swap** added, and deploy the three sites one at a time (don't let two builds overlap). |
| **1 GB** | Don't build on-server. Build in CI (GitHub Actions) and deploy prebuilt assets, or commit `public/build`. The server then only runs `composer install` + `migrate`. |

Add swap on the server (once, as root) if RAM is tight:

```bash
fallocate -l 4G /swapfile && chmod 600 /swapfile
mkswap /swapfile && swapon /swapfile
echo '/swapfile none swap sw 0 0' >> /etc/fstab
```

### 4. Deploy script

Start from the Africs deploy script in [deployment.md](./deployment.md) §5 for
each site (it already adds the Vite build step Forge omits). Adjust the site
path and, if building in CI instead, drop the `npm` lines.

### 5. Queue workers & scheduler

- Add a worker per site **only once that app dispatches jobs** (Forge → site →
  Queue). Each worker is always-on memory — count it in the budget above.
- The scheduler cron (`php artisan schedule:run`) is **per site** in Forge —
  set it up for any site that has scheduled commands (Africs has
  `erp:fetch-exchange-rates`).

### 6. Put Cloudflare (free plan) in front of every domain

- Caches static assets and absorbs traffic spikes — important for
  **hackathon.org**, which will get event-driven bursts that could otherwise
  starve the ERP's PHP-FPM pool.
- Hides the origin server IP; basic DDoS protection.
- Point each domain's nameservers at Cloudflare, set DNS records to the server
  IP (proxied), then request the Let's Encrypt cert in Forge as usual.

## Shared vs. per-site resources

| Resource | Shared across sites | Per site |
| --- | --- | --- |
| Server, PHP runtime, Nginx, MySQL server, Redis server | ✅ | |
| System user | | ✅ (Isolated Users) |
| Domain, SSL cert, nginx vhost, deploy script, `.env` | | ✅ |
| MySQL database + DB user | | ✅ |
| Redis instance | ✅ | key prefix per site |
| Queue worker, scheduler cron | | ✅ |

## When to move off one server

- `limitlessafrics.org` or `hackathon.org` grows steady traffic that visibly
  competes with the ERP for CPU/RAM.
- You want the ERP (vault + finance) on a host with no public-facing event site
  on it at all.
- You need zero-downtime deploys / horizontal scaling (then also: move file
  storage to S3 and sessions are already on Redis — see
  [tech-stack.md](./tech-stack.md)).

Until one of those is true, one 4 GB (or 2 GB + swap) Forge server with Isolated
Users and per-site databases is the correct, cost-effective setup.

## Open decision

- **Current server RAM** — determines whether the two new sites build on-server
  or in CI (§3). Check with `free -h` over SSH.
