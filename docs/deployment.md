# Deployment (Laravel Forge)

How to get this app running on a Forge-managed server. Written for this specific repo — it isn't a generic Laravel/Forge tutorial.

## 1. Server

- Create (or use an existing) Forge server: PHP 8.3, MySQL, Nginx.
- Install the **Redis** service on the server (Forge server dashboard → Redis) — see [tech-stack.md](./tech-stack.md) for why.

## 2. Create the site

- Forge → server → **New Site**
- Root domain: `africsinc.com` (or a staging subdomain first, e.g. `staging.africsinc.com`)
- Web directory: `/public`
- PHP version: 8.3
- Connect the GitHub repo (`musaajallo/africs-laravel`), branch `main`

## 3. PHP extensions

- Forge → site → **PHP** settings → enable the **phpredis** extension for this site (needed once `SESSION_DRIVER`/`CACHE_STORE`/`QUEUE_CONNECTION` are switched to `redis` — see tech-stack.md).

## 4. Environment variables

Forge generates a base `.env` for the site with DB credentials already filled in. Edit it (Forge → site → **Environment**) to add/confirm:

```
APP_NAME=Africs
APP_ENV=production
APP_DEBUG=false
APP_URL=https://africsinc.com

DB_CONNECTION=mysql
# DB_HOST/DB_DATABASE/DB_USERNAME/DB_PASSWORD are pre-filled by Forge

SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=resend
RESEND_KEY=            # once Resend is wired up — see backend-architecture.md
MAIL_FROM_ADDRESS=info@africsinc.com
MAIL_FROM_NAME="${APP_NAME}"
```

Generate the app key if Forge hasn't already: `php artisan key:generate` (via Forge's SSH/command runner), or paste one you generate locally.

## 5. Deploy script

Forge's default deploy script does **not** build frontend assets — this project uses Vite, so that step must be added. Forge → site → **App / Deploy Script**, replace with:

```bash
cd /home/forge/africsinc.com
git pull origin $FORGE_SITE_BRANCH

$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service $FORGE_PHP_FPM reload ) 9>/tmp/fpmlock

if [ -f artisan ]; then
    $FORGE_PHP artisan migrate --force
    $FORGE_PHP artisan storage:link
    $FORGE_PHP artisan config:cache
    $FORGE_PHP artisan route:cache
    $FORGE_PHP artisan view:cache
fi

nvm use 22 2>/dev/null || true
npm ci
npm run build
```

(Adjust the Node version line to whatever's available on the server — `nvm ls` via SSH to check, or install Node via Forge's server-wide Node/NPM installer first.)

## 6. Queue worker

Once anything is actually dispatched to a queue (WhatsApp webhook processing, outbound email, etc. — see backend-architecture.md), add a worker: Forge → site → **Queue** → New Worker, connection `redis`.

## 7. SSL + DNS

- Point the domain's DNS **A record** at the server's IP (at your registrar).
- Once DNS resolves, Forge → site → **SSL** → request a Let's Encrypt certificate.

## 8. First deploy

- Forge → site → **Deploy Now** for the first manual deploy.
- Enable **Quick Deploy** so future pushes to `main` auto-deploy via webhook.

## 9. Post-deploy checklist

- [ ] Visit the site — homepage, `/contact`, `/login` all load
- [ ] Submit the contact form — check `contact_submissions` table
- [ ] Confirm `/storage` symlink resolves (uploaded files aren't 404ing)
- [ ] Confirm Redis is actually being hit: `redis-cli -h 127.0.0.1 keys '*'` after a page load should show session/cache keys
- [ ] Create the real superadmin user (see the tinker snippet used locally, or build a proper seeder — see backend-architecture.md's Admin panel plan)
