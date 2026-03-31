# Education ↔ ePIN Consolidation

This consolidation merges the Education PIN purchase flows (WAEC/NECO/NABTEB/JAMB) into the unified `vtu_epin` infrastructure while keeping legacy routes and providers compatible.

## Status / Idempotency

The system treats consolidation as “applied” when:
- `config/epin_products.php` exists and defines products, and
- the integration flag `integration.education_epin_v1` is set to `true` (when `system_settings` exists).

Runtime ensures the flag is set once (no repeated writes) via:
- [EducationEpinConsolidationService.php](file:///c:/Users/User/Documents/gsoft_verify_V2/app/Services/EducationEpinConsolidationService.php)

You can also check/apply via command:
- `php artisan epin:consolidate-education --dry-run`
- `php artisan epin:consolidate-education`

Command: [ConsolidateEducationEpin.php](file:///c:/Users/User/Documents/gsoft_verify_V2/app/Console/Commands/ConsolidateEducationEpin.php)

## What Was Unified

### Product catalog
Education products are declared in a single catalog:
- [epin_products.php](file:///c:/Users/User/Documents/gsoft_verify_V2/config/epin_products.php)

Each product defines:
- `service_id`, `variation_code`, fixed `amount`, default `quantity`
- canonical `order_type` + `tx_prefix`
- `provider_service_types` (lets `vtu_epin` reuse legacy `education_*` providers)

### Backend processing
All Education PIN purchases now use:
- `service_type = vtu_epin`
- product-fixed amount/quantity
- provider selection across `provider_service_types`

Call-sites:
- [EducationController.php](file:///c:/Users/User/Documents/gsoft_verify_V2/app/http/controllers/service/EducationController.php)
- [VTUController.php](file:///c:/Users/User/Documents/gsoft_verify_V2/app/http/controllers/service/VTUController.php)

### Provider selection (backward compatible)
When buying an education product from the ePIN page or education routes, the request passes `provider_service_types` so the engine can pick from:
- `vtu_epin` providers (new)
- `education_*` providers (legacy)

Engine support: [VtuHubService.php](file:///c:/Users/User/Documents/gsoft_verify_V2/app/Services/VtuHubService.php)

### UI consolidation
The VTU ePIN page now includes an Education product selector to auto-fill product codes and prices:
- [epin.blade.php](file:///c:/Users/User/Documents/gsoft_verify_V2/resources/views/services/vtu/epin.blade.php)

## Backward Compatibility
- Existing Education routes remain functional (same endpoints, same response shape) but execute through `vtu_epin`.
- Existing `CustomApi` providers configured under `education_*` continue to work without DB migration.

