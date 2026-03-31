# Public Service Discovery

This project exposes public, read-only landing pages for platform services and a public auction browsing experience.

## Routes

- Services directory: `GET /explore`
- Service landing: `GET /explore/{slug}`
- Public auctions list: `GET /explore/auctions`
- Public auction detail: `GET /explore/auctions/{lotCode}`
- Authenticated realtime stream (SSE): `GET /realtime/auctions/stream`

All pages are designed to be safe for guests:

- No form submissions
- No state-modifying calls
- Disabled interaction elements with authentication CTAs

## Service catalog configuration

Services are defined in:

- [public_services.php](file:///c:/Users/User/Documents/gsoft_verify_V2/config/public_services.php)

Each service entry supports:

- `slug`, `title`, `category`
- `icon`, `tagline`, `summary`
- `highlights` (bullets)
- `cta` (labels)
- `links` (primary/secondary destinations)
- `image.gradient` (hero styling)

To add a new service landing page:

1. Add a service entry to `config/public_services.php`
2. It will automatically appear in:
   - `/explore`
   - Homepage “Explore all services” section

## Auctions data model (public browsing)

Public auction pages use these tables:

- `auction_sellers`
- `auction_lots`
- `auction_lot_images`

Bid history display uses:

- `auction_bids` (existing)

Notes:

- `auction_bids.lot_id` is treated as the lot code (`auction_lots.lot_code`).
- Bidder names are anonymized (e.g., `J***`) on public pages.

## Realtime auctions (authenticated-only)

Public auction pages can look “fully live” for authenticated users by enabling an SSE stream.

- Endpoint: `GET /realtime/auctions/stream`
- Auth: protected by `auth` middleware (guests cannot connect)
- Query params:
  - `ttl` (seconds, default 60, max 300)
  - `interval` (seconds, default 5, min 2, max 15)
  - `lot` (optional; when set, returns detail snapshot for one lot)

Client behavior:

- Guests: no background requests; only server-rendered refresh + countdown timer
- Logged in users: the UI subscribes to the SSE stream and updates prices, statuses, and bid history without a page reload

## Guest lockouts

Public pages intentionally disable interactive actions for non-authenticated visitors:

- Bid placement
- Watchlist actions
- Any state-changing requests

Instead, CTAs route to:

- `/register`
- `/login`

## Analytics tracking

Public pages send engagement events to:

- `POST /ab/event`

Tracked events include:

- `page_view`
- `cta_click`

Data is stored in:

- `ab_events`
