# Homepage Mockup Spec (Design Tokens + Components)

## Visual direction

- Dark, modern, high-contrast theme aligned with existing Fuwa.NG styling
- Large typography, short copy, CTA-first layout
- Clear information hierarchy: promise → proof → demo → trust → CTA

## Design tokens

- Background: `#080b12`
- Card: `rgba(255,255,255,0.03)`
- Border: `rgba(255,255,255,0.10)`
- Text primary: `#ffffff`
- Text secondary: `rgba(255,255,255,0.62)`
- Accent primary: `var(--clr-primary)` (theme-driven)
- Radius: 14–22px
- Focus ring: `outline: 3px solid rgba(59,130,246,0.7)`

## Components

### Hero

- Badge/pill with trust message
- H1 (variant A/B via experiment)
- CTA group:
  - Primary: “Get started”
  - Secondary: “Sign in”
  - Link CTA: “View pricing”
- Proof grid: 3 KPI cards

### Interactive demo

- Tabs: NIN / BVN / CAC
- Each tab:
  - Request snippet with Copy
  - Response snippet with Copy
- Footer links: Developer portal / OpenAPI

### Logos strip

- Non-claiming categories to avoid false client attribution unless real logos are available
- 6 logo chips on desktop, 3 on mobile

### Capability cards

- Verification suites
- AI Legal Hub
- Developer-ready API

### Security strip

- “Security that builds trust”
- Badges: Webhook signatures / RBAC / Audit logs

### Testimonials

- 3 cards
- Star rating decoration
- Role + industry (generic, non-identifying)

### Final CTA block

- Headline + supporting copy
- Primary CTA: Create account
- Secondary CTA: Developer portal

## Accessibility checklist

- Heading order: H1 → H2 → H3
- Contrast: ensure secondary text stays readable on dark background
- Focus states: visible for keyboard users
- Reduced motion: disable transitions/animations when `prefers-reduced-motion`
- ARIA: tablist/tabpanel attributes on interactive demo
