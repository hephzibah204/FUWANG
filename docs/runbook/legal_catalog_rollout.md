# Legal Catalog Rollout Runbook (Zero Downtime)

## Goal
Unify AI Legal Hub and Notary document catalogs under a single source of truth: `notary_settings`.

## Preconditions
- Database migrations are applied.
- Feature flags for `legal_services` and `notary_services` remain unchanged.

## Rollout steps
1. Deploy code + migrations.
2. Run catalog sync (optional, safe to repeat):
   - `php artisan legal:sync-catalog`
3. Verify parity:
   - Open Notary and confirm categories load.
   - Open AI Legal Hub and confirm the same document types appear.
   - Confirm pricing matches (SystemSetting override + catalog fallback).
4. Monitor:
   - Error logs for drafting failures.
   - PDF generation success rate.
   - Wallet debit/refund rates for Legal Hub finalization.

## API endpoints
The catalog/pricing APIs are available under `routes/api.php` (token-protected):
- `GET /api/v1/legal/catalog`
- `GET /api/v1/legal/catalog/{documentType}`
- `GET /api/v1/legal/pricing/{documentType}`

## gRPC definition
Proto contract:
- `docs/grpc/legal_catalog.proto`

## Rollback
- Revert application deployment to previous release.
- No destructive migrations are included; catalog changes are additive upserts.
- If needed, remove newly inserted catalog items from `notary_settings` by document_type.
