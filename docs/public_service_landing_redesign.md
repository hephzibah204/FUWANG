# Public Service Landing Pages: Audit & Redesign

This audit covers the public “Explore” service landing pages served at `/explore/{slug}` and the `/explore` directory page.

## Inventory (Public Landing Pages)

**Directory**
- `/explore` → [index.blade.php](file:///c:/Users/User/Documents/gsoft_verify_V2/resources/views/public/services/index.blade.php)

**Service landers (by slug)**
- `/explore/identity-verification`
- `/explore/bvn-verification`
- `/explore/validation`
- `/explore/clearance`
- `/explore/personalization`
- `/explore/nin-verification`
- `/explore/drivers-license-check`
- `/explore/cac-verification`
- `/explore/address-verification`
- `/explore/passport-verification`
- `/explore/tin-verification`
- `/explore/voters-card-verification`
- `/explore/biometric-verification`
- `/explore/plate-number-verification`
- `/explore/stamp-duty-verification`
- `/explore/credit-bureau-check`
- `/explore/developer-api`
- `/explore/ai-legal-hub`
- `/explore/auctions`
- `/explore/logistics`
- `/explore/finance`
- `/explore/agency-banking`
- `/explore/virtual-cards`
- `/explore/fx-exchange`
- `/explore/invoicing`
- `/explore/vtu-services` (VTU consolidated)
- `/explore/airtime` (VTU)
- `/explore/data-bundles` (VTU)
- `/explore/cable-tv` (VTU)
- `/explore/electricity-bills` (VTU)
- `/explore/notary-services`
- `/explore/ticketing`
- `/explore/post-office-logistics`
- `/explore/waec-pins`
- `/explore/waec-registration-pins`
- `/explore/motor-insurance`

Source of truth for slugs and baseline metadata: [public_services.php](file:///c:/Users/User/Documents/gsoft_verify_V2/config/public_services.php).

## Conversion Diagnosis (Why Pages Under-Convert)

**Common issues observed in the previous landing template**
- **Value proposition too generic**: headline echoed the service name; summary was descriptive, not persuasive.
- **No structured persuasion flow**: missing pain-point framing, feature→benefit mapping, and a closing argument.
- **Weak above-the-fold clarity**: CTA text was not contextual; urgency was absent; “why now” was unclear.
- **Insufficient trust reinforcement**: trust signals existed but weren’t integrated into decision-making sections.
- **No measurable experimentation loop**: views weren’t cleanly attributable to variants for `/explore/{slug}`, and conversion actions weren’t consistently tracked.

**VTU-specific issues (under-performing VTU pages)**
- VTU is time-sensitive (airtime/data “right now”), but the prior page didn’t speak to urgency, failure anxiety, or reconciliation needs.
- Visitor intent is transactional; the copy didn’t reduce fear of “failed recharge”, nor did it highlight references/history as proof-of-purchase.

## Redesign (Conversion Framework Implemented)

The service landing template now follows a conversion structure:
- **Hero**: benefit-led headline and subheadline, contextual CTAs, immediate trust pills, and an on-brand illustration.
- **Problem framing**: pain-point cards to match visitor anxiety and intent.
- **Feature → benefit breakdown**: a grid of benefits designed to reduce friction and increase confidence.
- **Preview**: kept as “safe preview”, but reframed with clearer intent and microcopy.
- **How it works**: 3-step path that makes the next action obvious.
- **FAQ**: reduces objections that block registration.
- **Closing argument**: a second CTA block to capture late deciders.

Implementation: [show.blade.php](file:///c:/Users/User/Documents/gsoft_verify_V2/resources/views/public/services/show.blade.php).

## VTU Copy + Visuals (First Pass)

VTU is treated as a single product suite (bill payments hub) with dedicated subpages.

**Consolidated VTU landing**
- VTU suite: `/explore/vtu-services`

**VTU subpages**
- Airtime: `/explore/airtime`
- Data: `/explore/data-bundles`
- Cable: `/explore/cable-tv`
- Electricity: `/explore/electricity-bills`

These include:
- Two headline variants (A/B), benefit-driven subheadline
- VTU-specific pain points (failed recharges, downtime, reconciliation)
- Benefits grid aligned to speed + proof-of-purchase
- Steps + FAQ + closing argument

Source: [public_services.php](file:///c:/Users/User/Documents/gsoft_verify_V2/config/public_services.php).

## A/B Testing + Measurement

**Experiment**
- Name: `service_landing`
- Cookie: `ab_service_landing`
- Variants: `A`, `B`
- Config: [ab.php](file:///c:/Users/User/Documents/gsoft_verify_V2/config/ab.php)
- Route wiring: `/explore/{slug}` uses `ab:service_landing` and `track.view:service_landing` ([web.php](file:///c:/Users/User/Documents/gsoft_verify_V2/routes/web.php))

**Tracking**
- `page_view` events (server-side) now include experiment/variant when available: [LogPageView.php](file:///c:/Users/User/Documents/gsoft_verify_V2/app/Http/Middleware/LogPageView.php)
- `cta_click` and `conversion` events (client-side) are posted via `/ab/event` using `window.csrfFetch` (fallback to fetch with CSRF): [show.blade.php](file:///c:/Users/User/Documents/gsoft_verify_V2/resources/views/public/services/show.blade.php)

**Primary conversion definition (for initial iteration)**
- `conversion` event triggered when a visitor clicks:
  - `service_primary` (hero primary CTA)
  - `lockout_register` (overlay CTA)
  - `closing_primary` (closing CTA)

**How to evaluate “20% lift”**
- Baseline: conversion rate per slug for variant A (conversions / page_views) over a stable window.
- Compare to variant B for the same slug and referrer mix.
- Promote the winning headline per slug by setting weights to 100/0 (or by making the winner the default copy).
