# Roadmap

## Phase 1 — Website + CMS

Goal: public website for Africs (Business / Technology / Design pillars) with an admin CMS so content can be managed without a developer.

Rough build order:

1. **Foundation** (done)
   - Laravel scaffold, MySQL connection (`africs_laravel`), app key, base migrations
2. **Auth** (done)
   - Laravel Breeze (Vue + Inertia stack) installed for login/register/password reset
   - Basic `role` column on `users` (superadmin/admin/user) — to be replaced by proper RBAC, see step 4
3. **Content plan** (done)
   - Site structure, page list, and content model — see [content-plan.md](./content-plan.md)
4. **Public website** (in progress)
   - Homepage, Contact page (with working form), Cookie Policy page shipped
   - Content is currently hardcoded in Vue (divisions, testimonials, client logos) — becomes CMS-managed in step 5
5. **CMS + Admin panels**
   - Two separate panels: `/cms` (web content) and `/admin` (users, RBAC, logs) — full plan in [backend-architecture.md](./backend-architecture.md)
   - RBAC foundation (roles/permissions) must land before the panels can be properly gated
   - Migrate hardcoded homepage content into DB-backed models + CRUD
6. **Integrations**
   - Resend (transactional email), AI provider abstraction (Claude/Gemini), WhatsApp two-way messaging — see [backend-architecture.md](./backend-architecture.md)
7. **Polish**
   - SEO basics (meta tags done on Home/Contact; sitemap still open), image/media handling on local disk (moves to S3 later, see tech-stack.md)

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
