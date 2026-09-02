# Answers — Phase 2 ERP Planning Questions

Captured 2026-09-01. These are the user's answers to the open questions raised
during Phase 2 (ERP) scoping — finance/invoicing, asset tracking, and users.
Feeds into [roadmap.md](./roadmap.md) Phase 2 and a future `erp-architecture.md`.

---

## 1. Currencies

The system must be **multi-currency** from the start. Currencies in use:

- **GMD** (Gambian Dalasi)
- **USD** (US Dollar)
- **EUR** (Euro)

**Implications to plan for:**
- Every money value stored with an explicit currency code (no implicit "base
  currency" assumption in the schema).
- Documents (proforma, invoice, expense) each carry their own currency.
- Exchange-rate handling: decide whether rates are entered manually per document
  or pulled from a rate source; store the rate used on the document so historical
  totals don't shift when rates change.
- Reporting needs a reporting/base currency for consolidated figures — likely GMD.

## 2. Proforma → Invoice

- The user creates **proforma invoices** and needs to **convert a proforma into a
  (real/tax) invoice**.
- Proformas and invoices must be **tracked separately** — separate numbering
  sequences, separate lists/statuses, separate reporting. A proforma is not just
  an invoice in a "draft" state.
- Conversion should carry line items, client, currency, and amounts across, and
  link the invoice back to its originating proforma (traceability), while each
  keeps its own identity and lifecycle.

**Model sketch:**
- `Proforma` — own number series (e.g. `PF-2026-0001`), statuses: draft / sent /
  accepted / declined / converted / expired.
- `Invoice` — own number series (e.g. `INV-2026-0001`), statuses: draft / sent /
  partially paid / paid / overdue / void. Optional `proforma_id` (nullable) for
  the ones that came from a proforma.
- Shared line-item structure so conversion is a copy, not a re-entry.

## 3. Asset & Subscription Tracking

Track **both physical and digital** assets.

### Physical assets (owned equipment)
- Laptops
- Desktops
- Printers
- (extensible — general equipment register)

Track per item: name/model, serial number, purchase date, purchase cost +
currency, assigned to (person, later a staff member), status
(in use / spare / repair / retired), location.

### Digital assets (owned)
- Software **licences** (perpetual or seat-based)
- **Subscriptions** (recurring — hosting, SaaS tools, domains, etc.)

Track per item: vendor, plan, seats/quantity, cost + currency, billing cycle
(monthly / yearly), renewal date, payment method, status (active / cancelled).
Renewal reminders are valuable here.

### Client subscription accounts
- The user **runs accounts/subscriptions on behalf of clients** (e.g. paying for
  or managing a service for a client).
- These must be **tracked under the client** they belong to — i.e. a subscription
  record can be linked to a `Client`, not just to Africs internally.
- Needs to support: what the subscription is, who it's for, cost to Africs, what
  the client is billed (may differ / include a margin), renewal date, and
  ideally a link to the invoice line that recharges it to the client.

**Model sketch:**
- `Asset` with a `type` (physical / licence / subscription) or separate tables —
  decide during ERP architecture pass.
- `Subscription` — `owner_type` (internal / client), nullable `client_id`,
  vendor, cost, currency, cycle, renewal_date, billed_amount (nullable).

## 4. Users / Staff

- **Right now: single user** — only the owner logs in.
- The system is being built **for the company**, not just personal use.
- **Staff will be added later** — the user/roles design must extend cleanly from
  "one owner" to "owner + multiple staff with different permissions" without a
  rebuild.
- This reinforces the existing plan to use proper RBAC
  (spatie/laravel-permission, per [backend-architecture.md](./backend-architecture.md))
  rather than the simple `role` string column.

## 5. Client Login (Portal)

- Clients **will log in eventually**, but **not now** — it's a later phase.
- Plan the data model so clients are first-class entities that *can* later be
  given portal accounts (e.g. a `Client` has many contacts; a contact can later
  be promoted to a login), without retrofitting.
- Client-facing scope when it comes: view their proformas/invoices, pay online,
  see their subscription accounts, download documents.

---

## 6. NEW REQUIREMENT — Public API for Integration

Add to the Phase 2 plan:

- The app **must expose an API** so **other applications can integrate with it**.
- This is a first-class requirement, not an afterthought — design the ERP domain
  models and service layer so an API can sit cleanly on top.
- Points to note:
  - Use **Laravel Sanctum** (or Passport if full OAuth2 client flows are needed)
    for API token / auth — already anticipated as an option in
    [tech-stack.md](./tech-stack.md).
  - Versioned API (`/api/v1/...`).
  - Likely first consumers: invoicing/proforma data, client records, asset &
    subscription data, payment status.
  - Consider webhooks (outbound events) alongside the REST API so integrating
    apps can react to events (invoice paid, subscription renewed, etc.).
  - API keys/tokens management belongs in the Admin panel.
  - Keep the Inertia/Vue web app and the API sharing the same domain/service
    layer, not duplicating business logic.

---

## Follow-up questions raised by these answers

1. **Exchange rates:** manual entry per document, or auto-fetched from a rate
   provider? What is the consolidated reporting currency (assume GMD)?
2. **Tax:** do invoices need VAT/GST lines? Gambian tax rules to apply?
3. **Proforma numbering:** restart per year, or continuous?
4. **Client subscription billing:** is the client always re-invoiced for these,
   and is there a standard markup, or is it per-arrangement?
5. **Assets:** is a simple register enough, or is depreciation / accounting
   treatment needed?
6. **Payments:** which payment methods/gateways for invoices (Wave, card,
   bank transfer)? Ties into the `gmb-pay` work.
7. **API:** any known first integration partner/app, so the API can be designed
   against a concrete use case rather than in the abstract?
