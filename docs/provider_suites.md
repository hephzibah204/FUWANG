# Provider Suites (Verification Services)

This project supports multiple upstream API providers per verification service via the `custom_apis` table, grouped by `service_type`.

Each `service_type` represents a **Service Suite** (e.g., “NIN Verification Suite”), containing one or more provider configurations.

## Data model

### Providers

- Table: `custom_apis`
- Key fields:
  - `service_type` (suite identifier)
  - `name` (provider name)
  - `endpoint` (base URL)
  - `headers` (JSON key/value headers)
  - `api_key`, `secret_key` (provider credentials)
  - `status` (enabled/disabled)
  - `price` (provider default price)
  - `timeout_seconds`, `retry_count`, `retry_delay_ms` (HTTP policy)

### Provider verification types (tiered products)

- Table: `custom_api_verification_types`
- Key fields:
  - `custom_api_id` (FK to provider)
  - `type_key` (machine key; sent by UI as `verification_type`)
  - `label` (human label shown in UI)
  - `price` (type-specific price)
  - `status` (enabled/disabled)
  - `sort_order`
  - `meta` (JSON overrides for request behavior)

Supported `meta` keys (optional):

```json
{
  "query": { "type": "basic" },
  "payload": { "plan": "premium" },
  "headers": { "X-Plan": "premium" },
  "path_suffix": "premium"
}
```

## Suites (service_type catalog)

The codebase currently uses these `service_type` values (found in controllers):

- **NIN Verification Suite**: `nin_verification`
- **BVN Verification Suite**: `bvn_verification`
- **BVN Identity Match Suite**: `bvn_matching`
- **CAC Verification Suite**: `cac_verification`
- **Driver’s License Suite**: `drivers_license`
- **Passport Verification Suite**: `passport_verification`
- **Voter’s Card Suite**: `voters_card_verification`
- **TIN Suite**: `tin_verification`
- **NIN Face Suite**: `nin_face_verification`
- **Biometric Suite**: `biometric_verification`
- **Plate Number Suite**: `plate_number_verification`
- **Stamp Duty Suite**: `stamp_duty`
- **Credit Bureau Advance Suite**: `credit_bureau_advance`
- **BVN+NIN+Phone Suite**: `bvn_nin_phone_verification`
- **Insurance Motor Suite**: `insurance_motor`
- **Education WAEC Suite**: `education_waec`
- **Education WAEC Registration Suite**: `education_waec_registration`
- **Address Verification Suite**: `address_verification`

Legacy note:

- Some older modules use `service_type = nin` (not `nin_verification`). These should be migrated to the suite key above to avoid split configuration.

## Admin configuration

### Provider configuration (Admin → Custom APIs)

1. Go to Admin → Custom APIs
2. Add or edit a provider for the correct `service_type`
3. Configure:
   - Endpoint
   - Credentials (API Key / Secret)
   - Headers (JSON)
   - Default price
   - Timeout + retry policy
4. Toggle status to enable/disable the provider

### Type configuration (Admin → Custom APIs → Verification Types & Pricing)

Each provider supports multiple verification “types” with independent pricing:

1. Open the provider modal
2. Add a Type Key + Label + Price
3. Optionally provide `meta` JSON for query/payload/header/path customization
4. Disable a type by turning status off or removing it

## User experience (dynamic provider/type selection)

For suites that support the dynamic UI, end users can:

1. Pick an enabled provider
2. The UI fetches the provider’s enabled verification types (AJAX)
3. The UI updates the type list and displayed price without a page reload
4. The chosen `api_provider_id` and `verification_type` are submitted to the backend

Dynamic types endpoint:

- `GET /services/providers/{providerId}/types?service_type=<suite>`

## Request contract

### User-facing service forms

Requests now include:

- `api_provider_id` (selected provider)
- `verification_type` (selected provider type, if configured)

### Backend behavior

- Provider selection is restricted to `status = true` providers and correct `service_type`
- If `verification_type` is provided:
  - must exist and be active under the selected provider
  - price is taken from the type record
- Else:
  - price falls back to provider price, then system setting defaults

## Testing coverage

Feature tests cover:

- Active-only type listing and service_type enforcement
- Invalid verification types rejected
- Graceful upstream error messaging for invalid credentials

Files:

- `tests/Feature/ProviderCatalogTest.php`

