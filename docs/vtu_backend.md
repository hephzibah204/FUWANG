# VTU Backend: Service Types, Providers, and Transactions

## Service Types (Catalog)
VTU service types are centralized in [vtu_services.php](file:///c:/Users/User/Documents/gsoft_verify_V2/config/vtu_services.php).

Each service type defines:
- `direction`: `debit` (normal bill payment) or `credit` (Airtime-to-Cash)
- `order_type` + `tx_prefix`: used for wallet transactions and references
- `limits`: default min/max
- `rules`: validation rules enforced inside `VtuHubService`

## Provider Configuration (Admin)
Providers are configured via Custom APIs:
- Admin UI: [admin/custom_apis/index.blade.php](file:///c:/Users/User/Documents/gsoft_verify_V2/resources/views/admin/custom_apis/index.blade.php)
- Controller: [CustomApiController.php](file:///c:/Users/User/Documents/gsoft_verify_V2/app/Http/Controllers/Admin/CustomApiController.php)

For VTU providers, the following config keys are supported (stored inside `custom_apis.config`):
- `fee_type`: `flat` or `percent`
- `fee_value`: numeric
- `min_amount`, `max_amount`: optional provider-specific overrides
- `service_code`: provider’s internal product code (optional, forwarded in payload if you include it)
- `credit_amount_path`: for Airtime-to-Cash, dot-path to the credited amount in response (default `data.amount`)
- `payload_map`, `static_payload`, `path_suffix`, `query`, `method`: optional request-shaping for payment calls
- `validate_endpoint`, `validate_method`, `validate_path_suffix`, `validate_query`: optional pre-validation call configuration
- `validate_payload_map`, `validate_static_payload`: optional request-shaping for validation calls
- `validate_customer_name_path`, `validate_customer_address_path`: dot-paths for extracting customer info from validation response

Legacy compatibility:
- `vtu_cable_tv` also matches providers configured as `cable_tv`
- `vtu_electricity` also matches providers configured as `electricity_bills`

## Transaction Processing

### Data bands and selling prices

- Configure each data API in Custom APIs with service type `vtu_data`. Customers can select only active bands with credentials; generic providers also require an endpoint. Configured Datasub247 is the default.
- In Admin → VTU & Utility Pricing, assign every bundle to its API band, enter the provider's actual plan ID, and set that band's selling price. Use the band filter to review prices independently.
- Unassigned bundles and bundles without a provider plan ID are unavailable for configured bands. Do not populate these fields with guessed IDs.
- The purchase page, catalogue endpoint, quote and checkout all use `VtuCatalogService` to resolve the same band-specific catalogue. Switching bands clears the previous selection and price.
- The browser submits the local catalogue row ID. Only the billing service maps it to the provider plan ID, and the server determines the price regardless of the submitted amount.

All VTU transactions run through [VtuHubService.php](file:///c:/Users/User/Documents/gsoft_verify_V2/app/Services/VtuHubService.php):
- Validates payload using the catalog rules
- Selects a provider (or falls back to legacy `ApiCenter` endpoints)
- Applies commission/fee and enforces min/max limits
- Executes the upstream call with consistent error handling
- Uses wallet debit/credit and refund on failures

## Electricity DISCO Validation (Lookup → Pay)
Electricity payments are split by DISCO (`serviceID`) and enforce a “validate meter first” step:
- Validation endpoint: `POST /services/vtu/electricity/validate`
- Payment endpoint: `POST /services/vtu/electricity/buy` (requires `validation_token`)
- UI: [electricity.blade.php](file:///c:/Users/User/Documents/gsoft_verify_V2/resources/views/services/vtu/electricity.blade.php)

## VTU Transaction History (Detailed)
Detailed request/response storage is recorded in `vtu_transactions` via:
- Migration: [create_vtu_transactions_table.php](file:///c:/Users/User/Documents/gsoft_verify_V2/Database/migrations/2026_03_20_000001_create_vtu_transactions_table.php)
- Model: [VtuTransaction.php](file:///c:/Users/User/Documents/gsoft_verify_V2/app/Models/VtuTransaction.php)

Users can view details per reference:
- `/history/{transactionId}` rendered by [TransactionController.php](file:///c:/Users/User/Documents/gsoft_verify_V2/app/Http/Controllers/TransactionController.php)
