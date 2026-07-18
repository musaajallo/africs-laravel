# Africs — Project Overview

## What Africs Is

Africs is a business operating across three pillars:

- **Business**
- **Technology**
- **Design**

(Full detail on what each pillar offers is being pulled together in [content-plan.md](./content-plan.md) from prior prototype projects.)

## Project Phases

### Phase 1 — Website + CMS

A public-facing marketing/informational website for Africs, with a CMS backend so non-technical staff can manage content (pages, services, blog/updates, etc.) without developer involvement.

### Phase 2 — Full ERP

The same Laravel codebase evolves into a full ERP for running the business itself — e.g. operations, finance, HR, projects/clients — building on the foundation, auth, and data laid down in Phase 1 rather than starting a separate system.

**Implication for Phase 1 architecture:** favor patterns (module boundaries, permissions, data modeling) that won't have to be torn up when ERP modules are added later. Avoid one-off/throwaway structures where a slightly more general one costs little extra now.

## Related Prior Projects

These sibling directories under `africs-projects/` contain earlier prototypes/explorations and are being mined for content and structure to inform Phase 1, but are not the codebase going forward:

- `africs-html` — static HTML prototype
- `africs_starter` — Next.js/Prisma starter
- `limitless-africs` — Next.js project, possibly a related program/initiative

See [content-plan.md](./content-plan.md) for what was extracted from them.

## Documents in this folder

- [tech-stack.md](./tech-stack.md) — stack decisions and infra plan
- [content-plan.md](./content-plan.md) — Phase 1 website content/site structure
- [roadmap.md](./roadmap.md) — phased delivery plan
