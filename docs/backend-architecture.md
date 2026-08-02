# Backend Architecture — CMS, Admin, and Integrations

Planning doc for the next phase of work: two admin panels, full content CRUD, and three integrations (Resend, AI, WhatsApp). None of this is built yet — this records the plan and the decisions still needed before starting.

## Two panels

Two separate areas, both behind auth, scoped by role/permission:

| Panel | Route prefix | Purpose |
|-------|--------------|---------|
| **CMS** | `/cms` | Manage everything that appears on the public website |
| **Admin** | `/admin` | Manage the system itself — users, roles/permissions, activity logs, integration health |

Both sit behind the existing Breeze auth (same `users` table, same login) — access to each panel is gated by permission, not a separate login system. A superadmin can reach both; a content editor might only reach `/cms`.

### CMS scope — full CRUD for:

Pulling from the content model already drafted in [content-plan.md](./content-plan.md), plus content that's currently hardcoded and needs to move to the database:

- `TeamMember`
- `BlogCategory`, `BlogTag`, `BlogPost`
- `CaseStudy`
- `NewsEvent`
- `Course` → `Module` → `Lesson` (Academy)
- `SystemSettings` (site name, logo, primary color, currency)
- **`Testimonial`** *(new — currently a hardcoded array in `Home.vue`, needs a migration + model + CMS CRUD)*
- **`ClientLogo`** *(new — same situation, currently hardcoded placeholder tiles in `Home.vue`)*
- Divisions/Capabilities content (currently hardcoded in `Home.vue`'s capability cards) — needs its own model if these should be editable without a code deploy

**Open question:** "full CRUD for all pages" could mean either (a) CRUD for the structured content blocks above, with page layout staying in Vue/code (my assumption, and the far more standard approach), or (b) a true drag-and-drop page builder where the layout itself is editable. Confirm which — (b) is a substantially bigger build.

### Admin scope:

- **Users** — full CRUD, role assignment
- **Roles & Permissions (RBAC)** — see below
- **Activity/Audit logs** — who did what, when (logins, content changes, user changes)
- **WhatsApp inbox** — staff view of conversations (see integration section) — arguably CMS content, but it's operational customer communication rather than web content, so placed here. Flag if you'd rather it live in CMS.
- **Contact submissions** — same judgment call as above; currently just stored via `ContactController`, needs a management UI somewhere. Proposed: Admin, since it's inbound customer communication like the WhatsApp inbox, not editable web content.

### RBAC approach

The `role` column added to `users` earlier (`user`/`admin`/`superadmin`) is a single string — fine for a quick superadmin login, not sufficient for "full RBAC" with granular permissions. Recommendation: adopt **spatie/laravel-permission** (the standard for this in Laravel) — gives roles + granular permissions (e.g. `manage-blog`, `manage-users`, `access-admin`, `access-cms`), assignable per user, checkable via `$user->can('manage-blog')`. Migrating the existing `role` column's values into the new system is a one-time data migration.

## Integrations

### 1. Resend (email)

Replaces the current `MAIL_MAILER=log`. Laravel has first-party support for Resend as a mail transport.

- `composer require resend/resend-php`
- `.env`: `MAIL_MAILER=resend`, `RESEND_KEY=re_...`
- Used for: contact form notifications, password reset / email verification (already flowing through Laravel's mail system, just currently logged instead of sent), and later any CMS-triggered notifications (new blog post, etc.)

### 2. AI (provider-agnostic — Claude, Gemini, etc.)

You want to build AI features over time without being locked to one provider. Plan: a small internal abstraction rather than calling Anthropic/Google SDKs directly from feature code.

- `App\Services\Ai\AiProviderInterface` — one method to start: `generate(string $prompt, array $options = []): string` (add streaming/structured-output methods later as needed)
- Concrete implementations: `AnthropicProvider` (Claude), `GeminiProvider` — selected via `.env`: `AI_PROVIDER=anthropic|gemini`
- `.env`: `ANTHROPIC_API_KEY`, `GEMINI_API_KEY` (only the active one is required)
- Bound in a service provider so any feature just type-hints `AiProviderInterface` and doesn't care which provider is live
- **Open question:** what's the first concrete feature this powers? (CMS writing assistant, contact-submission summarization/tagging, something else?) Worth picking one concrete use case to build the abstraction against, rather than building the abstraction in a vacuum.

### 3. WhatsApp (two-way customer messaging)

The biggest of the three. Customers message in, staff reply from the Admin panel.

**Provider decision needed first** — options:
- **Twilio WhatsApp API** — easiest to integrate (official PHP SDK, sandbox for dev testing, good docs), slightly higher per-message cost at scale
- **Meta WhatsApp Cloud API (direct)** — cheaper at scale, but requires Meta Business verification and more setup work up front
- Other BSPs (360dialog, Gupshup, MessageBird, Vonage) — similar tradeoff to Twilio

Recommendation: start with Twilio for speed of integration; revisit direct Meta Cloud API once volume justifies the extra setup.

**Data model:**
- `WhatsappConversation` — customer phone number, status (open/closed), assigned staff user (nullable)
- `WhatsappMessage` — belongs to conversation, direction (in/out), body, media URL (nullable), provider message ID, status (sent/delivered/read/failed), timestamps

**Flow:**
- Inbound: provider webhook → `POST /webhooks/whatsapp` → verify signature → find-or-create conversation → store message → (later) broadcast to Admin UI in real time
- Outbound: staff replies in Admin panel → job dispatched to queue → calls provider API → stores outbound message

This depends on the queue (Redis, per [tech-stack.md](./tech-stack.md)) being live in production — webhook handling and outbound sends should be queued, not synchronous.

## Suggested build order

Roughly in dependency order — RBAC has to exist before the panels can be gated, and the queue has to exist before WhatsApp:

1. **RBAC foundation** — spatie/laravel-permission, migrate the existing `role` column
2. **Admin panel shell** — Users CRUD, Roles/Permissions CRUD, Activity log
3. **CMS panel shell** — convert hardcoded homepage content (Testimonials, Client logos, Divisions) into DB-backed models + CRUD; then the rest of the content-plan.md model (Blog, Team, Case Studies, Academy)
4. **Resend** — low effort, unblocks real email deliverability (password resets currently only work via logged emails)
5. **AI abstraction** — build against one concrete first feature, once that's chosen
6. **WhatsApp** — biggest lift; provider account setup, webhook, conversation UI

## Open questions to resolve before building

1. Page-builder vs. structured-content CRUD (see CMS scope above)
2. Where Contact submissions and WhatsApp inbox live — Admin (proposed) or CMS
3. Which AI provider to build the abstraction against first, and what the first concrete AI feature is
4. WhatsApp provider choice — Twilio (proposed) vs. direct Meta Cloud API vs. other BSP
