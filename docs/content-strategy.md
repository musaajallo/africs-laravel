# Content Strategy — Page Roles & Thinking

Working doc for deciding *what belongs on each page* before we write real copy or build out UI further. Captures the reasoning behind page boundaries so future content work (and future UI work driven by that content) stays consistent, instead of re-litigating "does this belong here or there" per page.

This supersedes [content-plan.md](./content-plan.md) for anything about page structure/purpose — that doc was the original Phase 1 draft (site map, CMS data model); this one tracks the site as it's actually being built and the reasoning behind changes made along the way.

## Current site map (as built, 2026-08-11)

| Page | Route | Status |
|------|-------|--------|
| Home | `/` | Built — hero, mission quote, client logos, services overview (4 disciplines), process summary, testimonials, Academy teaser, contact CTA |
| Services — Business | `/services/business` | Built — scaffolding + copy reused from Home's divisions grid (Consulting + Operations) |
| Services — Technology | `/services/technology` | Built — scaffolding + copy reused from Home's divisions grid (Technology + Innovation) |
| Services — Design | `/services/design` | Built — scaffolding + newly-authored copy (Home has no dedicated Design division content) |
| How we work / Process | `/#process` (homepage section only) | Not yet a dedicated page — see below |
| Academy | `/#academy` (homepage section only) | Teaser only, "Coming soon" |
| Portfolio | `/portfolio` | Built — empty state, no real case studies yet |
| Contact | `/contact` | Built |
| Cookie Policy | `/cookie-policy` | Built |

Nav: Home / **Services** (dropdown: Business, Technology, Design) / How we work / Academy / Portfolio / Contact.

## The core distinction: Services vs. Process

Two different questions a visitor asks, and they shouldn't blur together:

- **Services pages answer "what do you offer, and what's the outcome?"** — the three pillars (Business, Technology, Design), what's included, who it's for.
- **Process page answers "how do you actually do it, and how do I know it'll work?"** — methodology, how a solution gets arrived at, what an engagement looks like week to week. This is a trust/credibility artifact as much as an informational one — the kind of page you link a prospect to before a call.

Decision (2026-08-11): keep Process as **one dedicated page**, not folded into each Services page, structured with a section per pillar (Business process / Technology process / Design process) rather than three separate process pages. Reasoning:

- A visitor might want to sanity-check *how* Africs works before they've decided *which* service they need — one page answers that without forcing a pillar choice first.
- Splitting process detail across the three Services pages risks diluting them — Services pages should stay focused on outcomes/scope, not turn into methodology essays.
- The homepage's existing 4-step summary (Discover → Design → Build → Deliver, under `#process`) is generic across all pillars today. A dedicated page is where that gets replaced with something specific enough to be useful — not "we discover, then design, then build" for the third time, but the actual differences in how a business-strategy engagement unfolds vs. a software build vs. a design engagement.

Open discipline to hold onto once this is written: if a sentence describes *what's delivered*, it belongs on a Services page. If it describes *how a decision gets made or validated*, it belongs on the Process page. Watch for drift once both exist.

## Per-page notes

### Home
Stays a condensed overview of everything else — it should tease Services, Process, Academy, and Portfolio, not duplicate their depth. Current homepage sections already roughly do this; revisit once the dedicated Process page exists, since the homepage's process summary should probably shrink further and link out rather than stand alone.

### Services (Business / Technology / Design)
Each should answer: what's included in this pillar, who it's for, what a typical deliverable looks like. Explicitly *not* the place for step-by-step methodology — that's the Process page's job. Current content on all three pages is scaffolding-quality (reused/condensed from Home's divisions grid, plus one freshly-written page for Design) — good enough to ship the structure, not yet final copy.

### Process (planned)
One page, sections per pillar as decided above. Needs real content decisions:
- What does a Business-strategy engagement actually look like start to finish (not the generic 4-step version)?
- What does a Technology build look like — discovery, architecture decisions, delivery cadence?
- What does a Design engagement look like — research, prototyping, validation, handoff?

This is the page this doc was created to prepare for. Nothing else blocks it structurally (routing pattern, page-hero/section CSS, nav — all already exist from the Services pages build-out); what's missing is the actual content decisions above.

### Academy
Still just a "coming soon" teaser. No content strategy decided yet — revisit once there's real course content to plan around.

### Portfolio
Structurally ready (grid + empty state), waiting on real case studies. Not a content-strategy question so much as "we need real client work to publish."

## Open questions

1. Should "How we work" in the nav get renamed to "Process" once the dedicated page exists? (Leaning yes — matches the section's existing `id="process"` in code, reads cleaner than "How we work.") Holding off on the rename until the page itself exists, so the nav label matches something real.
2. Do Academy and Portfolio deserve the same "dedicated page with sub-structure" treatment Services just got, once they have real content? Not yet decided — no urgency until there's actual course/case-study content to organize.
