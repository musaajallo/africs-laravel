# Tech Stack

## Current (Phase 1)

| Layer       | Choice                          | Notes |
|-------------|----------------------------------|-------|
| Backend     | Laravel 13 (PHP 8.3)             | Already scaffolded |
| Frontend    | Vue 3                            | Via Inertia.js (recommended pairing with Laravel Breeze — avoids building/maintaining a separate API layer while still giving a modern SPA-feel frontend) |
| Auth        | Laravel Breeze (Vue/Inertia stack) | Next task after this planning pass |
| Database    | MySQL                            | Local dev DB: `africs_laravel` |
| File storage| Local disk                       | `local` filesystem disk for now |
| Sessions/Cache/Queue | Database driver          | Laravel defaults, fine at current scale |

## Planned Later (as the site grows)

| Layer        | Move to                         | Trigger / reasoning |
|--------------|----------------------------------|----------------------|
| File storage | AWS S3                          | Once media/CMS uploads grow beyond what's comfortable on local disk, or multi-server deployment needs shared storage |
| Database     | Managed MySQL (e.g. RDS/PlanetScale/DO Managed DB) | Once self-managing backups/scaling/HA becomes a cost/time burden vs. a managed service |

Laravel's filesystem and database abstractions mean both of these are config changes (`FILESYSTEM_DISK`, `DB_*` env vars) rather than code rewrites, provided we avoid hardcoding local-disk paths or MySQL-specific raw queries where the query builder/Eloquent would do.

## Why Inertia + Vue over a separate API + SPA

- Single codebase/repo, single deploy — matches "keep cost low early" goal.
- No need to hand-roll auth token handling, CORS, or a separate API versioning strategy.
- Still gives full Vue component interactivity for CMS/admin screens and any dynamic public pages.
- If Phase 2 ERP ever needs a true separate API (e.g. for a mobile app), Laravel Sanctum can be added on top later without discarding this.

## Decisions Still Open

- Managed DB / S3 provider choice — deferred until needed (per user: cost-driven decision to make later, not now).
- Specific ERP module scope for Phase 2 — deferred to its own planning pass once Phase 1 ships.
