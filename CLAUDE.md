# Africs — public site + CMS + Console ERP

One Laravel + Inertia + Vue app serving three surfaces:

| Surface | Routes | Pages | Layout |
| --- | --- | --- | --- |
| **Public site** | `routes/web.php` | `resources/js/Pages/Site/*` | — |
| **`/cms`** — website content | `routes/panels/cms.php` (`cms.*`) | `Pages/Cms/*` | `CmsLayout.vue` |
| **`/console`** — internal ERP | `routes/panels/console.php` (`console.*`) | `Pages/Console/*` | `ConsoleLayout.vue` |

URL + route-name prefixes and the `panel:cms` / `panel:console` gate middleware
are wired in `bootstrap/app.php`. `routes/console.php` stays the Artisan file.

## Stack

PHP 8.3 · Laravel 13 · Inertia 2 + Vue 3 (`<script setup>`) · **plain CSS**
in `resources/css/app.css` (no Tailwind/utility framework) · MySQL · Vite.

Key packages: `laravel/sanctum` (API PATs), `spatie/laravel-permission` (RBAC),
`spatie/laravel-activitylog` **v5**, `brick/money`, `barryvdh/laravel-dompdf`.
Tests are PHPUnit (`vendor/bin/pest` not used).

## Commands

```bash
composer dev            # serve + queue + pail + vite (concurrently)
php artisan test        # or: composer test
npm run build
php artisan migrate
php artisan db:seed --class=RolesAndPermissionsSeeder   # AFTER adding a permission
php artisan db:seed --class=FinanceDemoSeeder           # local demo data (finance, assets, vault)
./vendor/bin/pint --dirty                                # format before committing
```

`FinanceDemoSeeder` auto-runs from `DatabaseSeeder` when `app()->environment('local')`.
Local login: `admin@africsinc.com` / `password` (super-admin).

## RBAC

`app/Support/Rbac.php` is the single source of truth — permission slug constants,
`permissions()`, `apiAbilities()` (token abilities; excludes panel gates and
`vault.*`), and `roles()` (role → permission map). Super-admin bypasses every
check via a `Gate::before` hook in `AppServiceProvider`.

**Adding a permission:** add the const, add it to `permissions()` and the
relevant role in `roles()`, then re-run `db:seed --class=RolesAndPermissionsSeeder`.
Frontend mirror: `resources/js/Composables/useAuth.js` `can()`.

## Console module recipe

Every module follows the same shape — look at Projects, Proformas or Assets as
the reference:

1. `App\Support\{Model}Meta` — status/category enums + `*Keys()` helpers.
2. Model: `#[Fillable(...)]`, `SoftDeletes`, `LogsActivity` (`getActivitylogOptions`
   with per-event descriptions), `#[Scope] search()`. `protected $attributes`
   for DB-default columns so freshly-`new`'d models serialise correctly.
3. `App\Policies\{Model}Policy` (auto-discovered) — `view*`/`create`/`update`/
   `delete`/`restore`, plus a `manage` ability for status/assign actions.
4. `Console\{Model}Controller` — `index` (filters default to "open" statuses via
   the `when()` else-branch), resource CRUD, `restore(int $id)`, extra POST/PUT
   action routes, a private `present()` shaping the Inertia payload, a
   `formOptions()` helper.
5. `Http\Requests\Console\{Model}Request` — `prepareForValidation` normalises
   currency/etc., an `*Attributes()` accessor.
6. Vue `Pages/Console/{Models}/{Index,Create,Edit,Show}` + `Partials/{Model}Form`.
   UI kit in `resources/js/Components/Panel/*`: `PanelTable`, `PanelPagination`,
   `PanelField`, `PanelButton`, `PanelPageHeader`, `PanelFlash`,
   `PanelConfirm` (optional `v-model` for menu-driven confirms),
   `PanelActions` (detail-page "Actions" dropdown), `PanelNavIcon`, `PanelClock`.
7. `Api\V1\{Model}Controller` + `Http\Resources\Api\V1\{Model}Resource` under
   `routes/api.php`, gated by `abilities:{perm}`.
8. Sidebar entry in `ConsoleLayout.vue` (`{ label, icon, routeName, activeMatch,
   permission }`); remove the matching `soon(...)` placeholder and its
   `RoadmapController::MODULES` entry.
9. `tests/Feature/Console/{Model}ManagementTest` — `setUp` seeds
   `RolesAndPermissionsSeeder`; a `manager()` helper.

## Money & currency

- Multi-currency (GMD/USD/EUR) from `App\Support\Settings` (`currency.enabled`,
  `currency.base` = GMD). `Settings` is one row per group, cached, shallow-merged
  over `defaults()`.
- `App\Support\Money` — decimal-string maths over `brick/money`. Note this
  `brick/math` uses PascalCase `RoundingMode::HalfUp` (**not** `HALF_UP`), and
  `BigDecimal::min()/max()` are **static** (`$x->max($y)` silently returns `$y` —
  clamp with `->isNegative()`/`->isGreaterThan()`).
- `ExchangeRate` + `App\Support\ExchangeRates::toBase()` — value of 1 foreign
  unit in the base currency, latest on/before a date. Daily fetch:
  `erp:fetch-exchange-rates` (exchangerate.host, `EXCHANGERATE_ACCESS_KEY`).
- Proformas/invoices/payments snapshot their `fx_rate` at creation so historical
  totals never move. One shared `App\Support\Sequence` (`PRO-`/`INV-`/`RCT-`,
  resets per year, row-locked).

## PDFs

`resources/views/pdf/document.blade.php` (shared proforma/invoice), `receipt`,
`invoice-receipt`. `App\Http\Controllers\Concerns\RendersPdf` streams **inline**
by default (`?download=1` forces attachment). Colour logo: `public/images/logo.png`
(rasterised from `logo.svg` — the SVG renderer has no gradient support).

## Modules

Built: Users & access · Leads · Clients · Projects · Proformas · Invoices ·
Payments & receipts · Receivables (AR ageing) · Exchange rates · Assets
(+ assignments + straight-line/reducing-balance depreciation, see
`App\Support\Depreciation`) · **Secrets vault** (see `docs/vault.md` — encrypted
credential store, password-confirm to reveal, KeePass XML/.kdbx export) ·
Dashboard · Tags · Settings · Activity log · API tokens.

Pending: **Subscriptions & infrastructure** (recurring digital services;
`vault_entries.related_subscription_id` is reserved for the link).

Roadmap & scoping answers: `docs/roadmap.md`, `docs/answers.md`.

## Testing notes

- Feature tests seed `RolesAndPermissionsSeeder` in `setUp()`.
- `Sanctum::actingAs()` uses a Mockery token (`->abilities` is null) — test real
  abilities with `createToken('t', [...])->plainTextToken` + `$this->withToken()`.
- Two `$this->withToken()` calls in one test method don't switch cleanly — use
  separate test methods.
- `bootstrap/app.php` renders JSON error bodies for any `expectsJson()` request
  (not just `api/*`).

## Deployment

Laravel Forge. See `docs/deployment.md`. `composer require` fails in some
environments on security-advisory pool filtering — workaround: temporarily set
`config.audit.block-insecure = false` in `composer.json`, run, revert (don't
commit it).
