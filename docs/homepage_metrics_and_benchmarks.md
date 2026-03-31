# Homepage Conversion Metrics & Performance Benchmarks

## Event tracking

Events are collected server-side in `ab_events` via:

- `POST /ab/event`

Schema:

- `event_name`: string
- `page`: path
- `experiment`: experiment name (e.g., `home_hero`)
- `variant`: variant (e.g., `A` / `B`)
- `session_id`: stable anonymous ID (localStorage)
- `meta`: JSON payload
- `user_id`: nullable (when logged in)

## Event taxonomy (homepage)

- `page_view`
  - meta: `{ ref }`
- `cta_click`
  - meta: `{ cta }`
  - expected values: `primary`, `secondary`, `pricing`, `devportal`, `openapi`, `final_primary`, `final_dev`
- `demo_tab_change`
  - meta: `{ tab }`
- `copy_snippet`
  - meta: `{ id }`
- `web_vitals`
  - meta: `{ lcp_ms }` and `{ cls }`

## Core funnels

### Funnel A — Signup conversion

1. `page_view` on `/`
2. `cta_click` where `cta=primary` or `cta=final_primary`
3. Registration completed (existing auth flow)

### Funnel B — Developer intent

1. `page_view` on `/`
2. `cta_click` where `cta=openapi` or `cta=devportal`
3. Token generation in developer portal

## A/B testing

Experiment: `home_hero`

- Variant A: “Verify identities. Automate compliance. Grow revenue.”
- Variant B: “Ship compliant onboarding in days, not weeks.”

Success metrics:

- Primary CTA CTR: `cta_click(primary)` / `page_view`
- Final CTA CTR: `cta_click(final_primary)` / `page_view`
- Pricing click rate: `cta_click(pricing)` / `page_view`

## Example queries

### CTR by variant (primary CTA)

```sql
SELECT
  variant,
  SUM(CASE WHEN event_name='cta_click' AND JSON_EXTRACT(meta, '$.cta')='primary' THEN 1 ELSE 0 END) AS primary_clicks,
  SUM(CASE WHEN event_name='page_view' THEN 1 ELSE 0 END) AS page_views
FROM ab_events
WHERE experiment='home_hero' AND page='/'
GROUP BY variant;
```

SQLite note: JSON extraction depends on SQLite JSON extension availability.

## Performance benchmarks (targets)

Targets for the marketing homepage (mobile, 4G, mid-tier device):

- LCP: <= 2500ms
- CLS: <= 0.10
- TTFB: <= 800ms
- Total JS: keep minimal on homepage

Measured metrics can be aggregated from `web_vitals` events:

- LCP: `meta.lcp_ms`
- CLS: `meta.cls`

## Accessibility benchmarks (targets)

- Keyboard navigation: all interactive elements reachable and usable
- Visible focus indicator: all buttons/links
- Reduced motion support: `prefers-reduced-motion`

