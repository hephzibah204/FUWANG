# Consolidation Inventory (Duplicates & Canonical Implementations)

This document tracks identified duplication clusters, the chosen canonical implementation, and how existing call-sites were migrated while preserving behavior.

## Backend (PHP)

### DB table detection (`isBaseTable`) and account-balance table selection
- Duplicates previously existed in `WalletService`, `WebhookController`, and `FundingController`.
- Canonical implementation: `App\Support\DbTable`
  - `DbTable::isBaseTable(string $table): bool`
  - `DbTable::resolveAccountBalanceTable(): ?string`
- Backward compatibility
  - Keeps existing “fail-open” behavior (returns `true` on exceptions) to avoid breaking environments where `information_schema` is restricted.

### Verification result persistence (`storeResult`)
- Duplicates previously existed across API and web controllers, with inconsistent `reference_id` generation.
- Canonical implementation: `App\Services\VerificationResultService`
  - `create(User|int $user, string $serviceType, string $identifier, string $providerName, mixed $responseData, string $status='success', ?string $referencePrefix=null)`
  - `generateReferenceId(string $prefix): string`
- Backward compatibility
  - Call-sites may pass a `referencePrefix` to preserve existing reference formats (e.g., `NIN-*`, `BVN-*`).

### Paid workflow (wallet debit → provider call → mark success / refund)
- Repeated pattern existed in multiple verification entrypoints.
- Canonical implementation: `App\Services\PaidActionService`
  - `run(User $user, float $amount, string $orderType, string $txIdPrefix, callable $action): array`
- Backward compatibility
  - Controllers preserve their existing response envelopes and HTTP status code conventions while delegating wallet lifecycle to the shared service.

## Frontend (JS)

### CSRF JSON `fetch` wrappers
- Multiple scripts implemented the same CSRF + JSON request plumbing.
- Canonical implementation: `public/assets/nexus/js/csrf-fetch.js`
  - Exposes `window.csrfFetch(url, { method, data, headers, timeoutMs })`
- Backward compatibility
  - Existing scripts keep a fallback implementation if `window.csrfFetch` is not present.
  - Layouts load `csrf-fetch.js` before other core scripts.

## Tests

### Test bootstrapping
- Canonical test bootstrap: `Tests\CreatesApplication` and `Tests\TestCase` updated to ensure the Laravel application is bootstrapped for unit/feature tests.

### Coverage added for consolidations
- Unit tests validate:
  - `VerificationResultService` persists results and generates expected reference prefixes.
  - `PaidActionService` marks success or refunds correctly and records transactions consistently.
- Feature tests validate:
  - API NIN verification debits/persists on success and refunds on provider failure.
