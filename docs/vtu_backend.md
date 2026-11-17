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

### Datasub247 catalogue and pricing

Datasub247 is the sole enabled purchase provider. Other API records remain saved for future use. Provider selection is hidden and requests cannot use another provider.

Run migrations through 2026_11_17_130000 during deployment. Every page of the signed-in documentation was verified: 41 data plans across MTN, Airtel, GLO and 9mobile, and 30 cable plans across DSTV, GOtv and StarTimes. The completion migration also updates installations that already loaded only the first pages. Existing selling prices are preserved; inaccurate labels and validity periods are corrected. Legacy rows with IDs absent from the complete provider catalogue are retained unassigned for admin review. New entries start with the documented amounts; set selling prices in Admin VTU Pricing. No API credentials are embedded in the catalogue.

- Data provider IDs: 1 (500MB / 7 days / NGN230), 2 (1GB / 30 days / NGN410), 3 (2GB / 7 days / NGN820), 4 (2GB / 30 days / NGN840), 6 (3GB / 30 days / NGN1140).
- Network IDs: MTN=1, GLO=3, AIRTEL=2, 9MOBILE=4. Legacy Fuwa codes 02=GLO, 04=AIRTEL and 03=9MOBILE retain their original meanings.
- All eight confirmed electricity IDs: Ikeja=1, Eko=2, Kano=3, Port Harcourt=4, Jos=5, Ibadan=6, Kaduna=7, Abuja=8.
- Browser data plan IDs identify local catalogue rows. VtuCatalogService translates them to provider IDs exactly once. Unmapped rows are unavailable; local IDs must never be guessed as provider IDs.
- Bulk bundle entry assigns Datasub automatically and validates every row before saving any row. A valid API token alone does not import plans. Empty printing plan tables are not seeded with guessed products.
- Global prices and product settings save in one database transaction. Cache invalidation follows commit; failed saves retain entered figures and return an error.

### Callbacks

Set DATASUB247_WEBHOOK_SECRET to an independently generated secret. Configure the Datasub dashboard callback URL as https://fuwa.ng/webhooks/vtu/datasub247?token=YOUR_WEBHOOK_SECRET (substitute the configured secret). The endpoint also accepts X-Webhook-Secret or a bearer token. It rejects unauthenticated calls and stores only the JSON body, never the URL credential. The documented parent keys request-id, status and response are supported; fail and failed both settle as failure. Replayed callbacks cannot refund twice. Prepaid electricity and PIN purchases remain pending until their delivery token or PIN is available.

All VTU transactions run through [VtuHubService.php](file:///c:/Users/User/Documents/gsoft_verify_V2/app/Services/VtuHubService.php):
- Validates payload using the catalog rules
- Routes purchases through configured Datasub247 credentials
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
