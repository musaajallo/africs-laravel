# Build Brief: National Work Mapping — Limitless Africs Website

> **Status (2026-09-11):** Not built as specified below. A small,
> representative section demonstrating the idea (philosophy line +
> filterable sample project index + collapsed COFOG legend, no systems map,
> no CMS admin) was added inline to
> `resources/js/Pages/Site/LimitlessAfrics.vue` for review. The data model,
> CMS-backed admin CRUD, real project content, and the phase-2 systems map
> described below are still pending. This file preserves the original brief
> so the full build can be picked up later without re-deriving it.

## Purpose

Limitless Africs does not operate within a fixed set of sectors. The
organization goes wherever it can find people, resources, or a solution to
close a gap — across labour, health and safety, traffic safety, water access,
digital platforms for regional youth councils, and more. This page must
demonstrate that breadth as **evidence** (a body of tagged, real projects),
not as a claim (a static "our services" list).

Build a page that:
1. States this philosophy briefly up front.
2. Shows a filterable index of real projects.
3. Optionally shows a systems map of national government functions with
   markers indicating where the organization has been active.
4. Makes the underlying classification system (see below) visible but
   secondary — a legend, not the headline.

## Content model

Each **project** is a single record with multiple tags. Do not force a
project into one category — a well-digging project can be tagged Water +
Agriculture + Rural Communities simultaneously.

```
Project {
  title: string
  summary: string (1-3 sentences)
  description: string (longer, optional)
  region: string | string[]           // e.g. "URR", "National", "KMC"
  partner_type: enum[]                // government | community | private | ngo
  clusters: string[]                  // plain-language clusters, see below — one project can carry 2+
  cofog_codes: string[]               // e.g. ["04.2", "06.3"] — see crosswalk below
  outcome_tags: string[]              // free-text plain labels, e.g. "water access", "youth governance", "worker safety"
  status: enum                        // active | completed | ongoing
  date_range: string
  media: image[] | none
}
```

## Plain-language clusters (reader-facing)

These are the primary filter/navigation categories. They are intentionally
in plain language for a general site visitor — not a government reporting
taxonomy. Each maps to one or more COFOG codes underneath (see crosswalk),
but that mapping is shown only in a legend/footnote, never as the primary
label.

1. **Governance** — civic participation, local/regional administration, youth
   governance structures
2. **Economy & Livelihoods** — trade, finance, labour, entrepreneurship
3. **Foreign & Security** — external relations, defence, justice, public
   order
4. **Human Capital** — health, education, women's affairs
5. **Production & Natural Resources** — agriculture, fisheries, water,
   environment, energy
6. **Infrastructure & Territory** — transport, works, communication, land
7. **Social & Culture** — tourism, culture, sport

## Crosswalk legend (COFOG — for transparency, shown collapsed/footnoted)

This table exists so the classification is auditable (useful to funders,
government partners, or anyone checking rigor) without cluttering the
public-facing copy. Render as a collapsible "How we classify this work"
section or footnote, not inline with project cards.

| Plain-language cluster | COFOG codes covered |
|---|---|
| Governance | 01.1 (executive organs, local administration) |
| Economy & Livelihoods | 01.1 (fiscal affairs), 04.1, 04.2, 04.6 |
| Foreign & Security | 01.1 (external affairs), 02, 03.1, 03.3 |
| Human Capital | 07, 09.1–09.4, 09.8, 10 |
| Production & Natural Resources | 04.2, 04.3, 05, 06.3 |
| Infrastructure & Territory | 04.4, 04.5, 06.2 |
| Social & Culture | 04.7, 08.1, 08.2 |

Note: several COFOG codes appear under more than one cluster (e.g. 01.1
appears three times) because government functions genuinely overlap —
this is expected and should not be "fixed" by forcing exclusivity.

## Page structure

1. **Intro block** (short, above the fold)
   - One-line philosophy statement, e.g.: "We don't confine our work to a
     fixed set of sectors — we go where the gap is, and where we can find
     the people, resources, or solution to close it."
   - No sector list here. This block is philosophy only.

2. **Systems map (optional, phase 2)**
   - Static diagram of the 7 clusters as nodes
   - Small markers/dots on nodes where Limitless Africs has active or past
     projects
   - Clicking a node filters the project index below to that cluster

3. **Filterable project index (core of the page)**
   - Filters: Cluster (multi-select), Region, Partner type, Outcome tag
   - Filter logic: OR within a filter group, AND across groups
   - Project cards show: title, summary, region, active tags (as small
     pills/badges), status
   - No project should visually appear "owned" by only one cluster — show
     all its cluster tags on the card

4. **Classification legend (footer or collapsible section)**
   - The crosswalk table above, framed as "How we classify this work" —
     optional reading for anyone who wants the rigor behind the tags

## Technical notes

- Data-driven: projects should live in a structured data source (JSON,
  database table, or CMS collection) — not hardcoded per-project HTML —
  since tags will be added/edited over time and cross-referenced.
- Filtering should happen client-side if the project count stays modest
  (sub-hundreds), to keep it fast and simple.
- Mobile-first: filters should collapse into a dropdown/sheet on small
  screens rather than a persistent sidebar.
- Keep the crosswalk/legend visually de-emphasized (smaller type, collapsed
  by default) — it's there for credibility and audit, not for navigation.

## Open decisions for the full build (not yet made)

- **Data source**: full CMS-backed collection under `/cms` (migration,
  model, policy, controller, admin CRUD, RBAC permission) vs. a
  structured JSON/data file maintained by hand. The representative version
  uses a hardcoded array in the Vue component — placeholder only, not
  wired to any data source.
- **Page location**: currently inline on the Limitless Africs page. Revisit
  whether it deserves its own route once real content volume makes the
  page too long.
- **Real project data**: the representative version uses invented sample
  projects for layout/interaction purposes only. These must not be
  mistaken for real claims about government partnerships — replace with
  vetted project records before this ships to production content.
- **Systems map**: not attempted in the representative version at all.
- **Location taxonomy**: a real `region`/facility field will eventually need a
  proper directory of Gambian schools and community centers (not just the 7
  admin regions) — GBOS (Gambia Bureau of Statistics) is a likely source to
  check before building one from scratch. Also needed by TechTrek's school
  computer-refurbishment project. Not started.
