# Content Plan — Phase 1 Website

Source: `africs_starter` — confirmed as the authoritative content/business model (Business, Technology & Design consulting firm), superseding the alternate "Gambian holding group" framing found in `africs-html`. See caveats at the bottom — this is a starting draft to react to and correct, not confirmed final copy.

## Positioning clarification (from the user, 2026-07-18)

Africs builds digital products for other companies — i.e. a digital product studio/agency, not purely an internal-facing consultancy. This sharpens (doesn't replace) the "Business, Technology & Design" model below: the homepage copy should foreground "we build the product your business case depends on" rather than generic consulting language.

## Primary source used

`africs_starter`'s content model matches "Business, Technology, Design":

- Tagline: **"Africs | Business, Technology & Design Solutions"**
- Hero: **"Adding Value to Every Engagement"** — *"We deliver integrated business, technology, and design solutions to organizations and individuals across public and private sectors."*
- Mission: *"At Africs, our mission is simple: adding value to every engagement, every project, and every partnership. We specialize in delivering integrated solutions that bridge the gap between business objectives, technological capabilities, and innovative design thinking."*

## Site Structure (Phase 1 pages)

| Page | Purpose | Content source |
|------|---------|-----------------|
| Home | Hero, expertise/offerings summary, divisions overview, impact stats, CTA | `africs_starter` homepage |
| About | Mission statement, company story, team | `africs_starter` about page |
| Divisions | Detail on each division (see below) | `africs_starter` `/divisions` |
| Services (optional merge with Divisions) | "Our Expertise" / "Our Offerings" cards | `africs_starter` homepage sections |
| Blog / Insights | Articles, categorized | `africs_starter` blog + `BlogPost`/`BlogCategory`/`BlogTag` model |
| Case Studies | Client work / results | `africs_starter` `CaseStudy` model |
| Team | Team member profiles | `africs_starter` `TeamMember` model |
| Contact | Contact form → stored submissions | `africs_starter` `ContactSubmission` model |
| News/Events (optional) | Announcements | `africs_starter` `NewsEvent` model |
| Academy | Courses listing, course detail (modules/lessons), enrollment | `africs_starter` `(lms)` routes + `Course`/`Module`/`Lesson` model |

**Not carried over:** Pay, Store, Labs — these belong to the *other* "Africs" concept (`africs-html`'s Gambian holding group framing), which is no longer in use. `limitless-africs` is unrelated and ignored for now.

## Divisions (core pillar structure)

| Division | Description |
|----------|-------------|
| Technology | Digital transformation, software development, cloud infrastructure, IT strategy |
| Consulting (Business) | Strategic business advisory, planning, growth strategies |
| Innovation | R&D, forward-looking product/solution research |
| Operations | Operational excellence, process optimization |

Homepage also lists "Specialized Services": Healthcare Services, Infrastructure, Retail & Commerce — unclear if these are real service lines or template filler; confirm before including.

## CMS Content Model (Phase 1 draft)

Based on `africs_starter`'s Prisma schema, translated to Eloquent models/migrations:

- `TeamMember` — name, role, bio, image, social links, order
- `BlogCategory`, `BlogTag`, `BlogPost` (title, slug, excerpt, content, featured image, published/publishedAt, author → TeamMember, tags M-M)
- `CaseStudy` — client, industry, challenge, solution, results, order
- `NewsEvent` — type (news/event/announcement), event date
- `ContactSubmission` — name, email, phone, message, services requested, status (new/in progress/resolved/archived)
- `SystemSettings` — site name, logo, primary color, currency (useful once CMS lets admins theme/rebrand without a deploy)
- `Course` → `Module` → `Lesson` — hierarchical Academy content (title, description, duration, level, price, featured flag, video URL)

Admin CRUD screens needed for each of the above in Phase 1's CMS.

## Branding Reference

- Domain (confirmed by user, 2026-07-18): **africsinc.com**
- Contact email: `info@africsinc.com` (updated to match confirmed domain; supersedes the old `info@africs.net` found in prototypes)
- Phone: `+220 3718206` (primary — multiple slightly different numbers appeared across sources, confirm correct one)
- Address: The Gambia (West Coast Region) — exact street name was inconsistent between sources, confirm
- Colors: deep green + amber/mustard accent recurred across both `africs-html` and `africs_starter` designs — reasonable starting palette even though exact tokens differed

## Positioning: Adding Value (confirmed by user, 2026-07-18)

The brand centers on the tagline **"Adding Value"** across the three pillars — Business, Technology, Design. The "digital product studio for other companies" framing used in an earlier homepage draft was only meant to inform *visual/design* direction, not the actual copy positioning — the company is not primarily a product-development shop. All homepage copy was revised to lead with adding value rather than "building products."

## Open Questions for You

1. **What does "Design" concretely offer?** Not resolved in `africs_starter` — its actual division breakdown (Technology/Consulting/Innovation/Operations) doesn't contain a dedicated Design division despite it being in the tagline.
2. **Real contact phone/address** — source material had inconsistencies even within `africs_starter`'s own code vs. its reference screenshots.
3. Team member names found in the old seed data look like placeholders (e.g. "Naday Keyson" vs "Nadav Keyson" across files) — treat as fake, need real team info.

## Decided

- **Academy is in scope for Phase 1** (training/courses, `Course`/`Module`/`Lesson` model + LMS-style pages).
- **`limitless-africs` is ignored** — unrelated to this business.
