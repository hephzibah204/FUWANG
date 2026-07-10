## Logistics Production Readiness Audit

### Scope

- Pickup and drop-off centres across Nigeria (36 states + FCT)
- Delivery modes (home pickup, home delivery, centre drop-off, centre pickup)
- Address validation and geolocation verification
- Pricing engine (weight, distance, urgency, pickup type)
- AI-powered pricing algorithm design
- Google Maps integration (geocode, distance, traffic)
- Performance targets and test coverage

---

## 1) Pickup/Drop-off Centres Coverage

### Current status

- Implemented a first-class centres data model and API:
  - `logistics_centers` table + model
  - `/logistics/centers?state=<state>&type=<pickup|dropoff>` endpoint
- Implemented seeded coverage for all states found in the Nigeria states list (expected 36 + FCT):
  - A default pickup center and a default drop-off center are created per state.
- Centres are not hardcoded in the UI. Logistics staff can create, update, and delete centres from the Ops module.
- Booking UI now requires sender/recipient state and can list centres dynamically per state.

### Gaps before “production”

- Centre dataset is currently a baseline/placeholder; real addresses, GPS coordinates, and operating hours must be populated.
- “Real-time availability” is currently driven by `availability_status` + optional `capacity_per_day/current_load`. There is no automated signal ingest.
- No public “centre details page” (hours, directions, contact).

---

## 2) Delivery Options (Home pickup/home delivery)

### Current status

- Booking flow supports:
  - Pickup: `center_dropoff` or `home_pickup`
  - Delivery: `home_delivery` or `center_pickup`
- Conditional validation enforced server-side:
  - Home pickup requires sender address
  - Home delivery requires recipient address
  - Centre modes require selecting centres

### Gaps before “production”

- Address validation is structural only (string + state). Mandatory geocoding verification is enforced when Google Maps is configured; otherwise booking/quotes should be blocked for production.
- No polygon/geofence checks for “serviceable areas” within states/cities.

---

## 3) Pricing Engine

### Current status

- Added a pricing quote endpoint used by the booking UI:
  - `POST /logistics/pricing/quote`
- Pricing now includes:
  - Base + weight surcharge + distance surcharge
  - Optional home pickup + home delivery fees
  - Urgency multiplier (standard/express/overnight/same_day)
- Quote returns a breakdown and response-time measurement.

### Gaps before “production”

- Rate configuration should be moved into an admin-editable settings UI and stored in `system_settings`.
- Dimensional weight is not applied yet (dimensions are stored and can be used in the next iteration).
- No surge-pricing policy rules (caps, floors, minimum fare, maximum per km).

---

## 4) Google Maps Integration

### Current status

- Integrated Google Geocoding + Distance Matrix (with traffic model) behind a service layer.
- When API key is configured, distance uses Distance Matrix; otherwise it falls back to default intra/inter-state km values.

### Gaps before “production”

- API quota controls, retries/backoff, and error budgets are not fully defined.
- Route optimization (multi-stop) is not implemented.
- Reverse-geocoding for automatic state selection is not implemented.

---

## 5) AI-Powered Pricing Algorithm

### Current status

- Implemented an “AI adjustment” layer as a pluggable step after deterministic pricing:
  - Default model currently has zero impact (multiplier is 0) and acts as safe scaffolding.
- Feature hooks exist for:
  - Fuel/traffic/weather/seasonal factors

### Gaps before “production”

- Historical dataset collection and labeling (actual cost vs predicted) is not implemented.
- Training and deployment pipeline (model versioning, rollback, evaluation) is not implemented.
- Accuracy target (≤5% variance) requires defined ground truth and monitoring.

---

## 6) Performance Targets

### Targets requested

- Pricing accuracy: within 5% variance
- Delivery time predictions: 90% reliability
- Price calculation latency: under 2 seconds

### Current status

- Quote endpoint returns `response_time_ms` for basic latency monitoring.
- Caching is used for geocode and distance calls.

### Gaps before “production”

- No persistent metrics store/dashboards for latency, accuracy variance, or delivery ETA performance.
- No delivery-time prediction model exists yet.

---

## 7) Testing Coverage

### Current status

- Feature tests:
  - State coverage for centres and centres API response
  - Google Maps integration test using HTTP fakes for geocode + distance matrix

### Gaps before “production”

- End-to-end booking workflow tests across all states with each delivery mode and centre selection.
- Unit tests for pricing components (weight surcharge, distance surcharge, home pickup/delivery fees, urgency multipliers, min/max policies).
- Load testing / SLA tests for the quote endpoint.
