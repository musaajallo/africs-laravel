# Roadmap

## Phase 1 — Website + CMS

Goal: public website for Africs (Business / Technology / Design pillars) with an admin CMS so content can be managed without a developer.

Rough build order:

1. **Foundation** (done)
   - Laravel scaffold, MySQL connection (`africs_laravel`), app key, base migrations
2. **Auth**
   - Install Laravel Breeze (Vue + Inertia stack) for login/register/password reset
   - Define admin vs. public user roles/permissions
3. **Content plan**
   - Finalize site structure, page list, and content model — see [content-plan.md](./content-plan.md)
4. **CMS data model**
   - Migrations + Eloquent models for pages/services/posts/team/testimonials/etc. per content plan
   - Admin CRUD screens (Vue/Inertia) for each content type
5. **Public website**
   - Public-facing pages consuming the CMS content (home, about, pillar pages, services, blog, contact)
6. **Polish**
   - SEO basics (meta tags, sitemap), contact form handling/notifications, image/media handling on local disk

## Phase 2 — Full ERP

Goal: extend the same application into an internal ERP for running the business.

Not yet scoped in detail — to be planned once Phase 1 ships and real operational needs are clearer. Likely candidate modules (to validate with the user before committing):

- Client/project management
- Finance (invoicing, expenses)
- HR (staff, contracts)
- Inventory/assets (if applicable to Business pillar work)

**Architectural note for Phase 1:** since Phase 2 builds on the same codebase, prefer:
- A `users` table/roles design that can extend from "public + admin" to distinct internal-staff roles later
- Clean module boundaries (e.g. namespaced controllers/models per domain) so ERP modules can be added alongside the CMS without entangling with public-website code

## Infra Roadmap

- **Now:** local disk storage, self-hosted MySQL
- **Later (cost/scale-driven, no fixed date):** move file storage to S3, move MySQL to a managed DB service — see [tech-stack.md](./tech-stack.md)
