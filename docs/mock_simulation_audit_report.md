# Mock / Stub / Simulation Audit Report

This report catalogs first-party mocks, stubs, fakes, demo/sandbox logic, and simulation paths found across the codebase. It also evaluates isolation from production behavior, activation conditions, and risks.

## Executive Summary

### High-Risk Items (Action Recommended)
- Committed secrets in configuration files (remove from VCS and rotate credentials).
- Production-path simulation logic for virtual cards when provider key is missing.
- Installer logic capable of writing `.env` over HTTP when enabled.

### Test-Only Items (Generally OK)
- Laravel tests using `Http::fake()` and local fake classes.
- Playwright E2E stubs that intercept network requests and replace third-party SDK globals.

## Catalog

### 1) Production Code: Simulation / Demo / Sandbox

| Type | Location | Purpose / Replaces | Activation / Scope | Dependencies | Risk | Notes / Recommendation |
|---|---|---|---|---|---:|---|
| Simulation fallback | [VirtualCardService.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/app/Services/VirtualCardService.php#L14-L177) | Simulates virtual card creation/funding when Flutterwave secret is missing | **Always active** when `config('services.flutterwave.secret')` is empty (not env-gated) | Flutterwave API, Laravel `Http` | High | Replace with hard failure outside `local/testing`; never simulate financial artifacts in production. |
| Installer flow | [web.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/routes/web.php#L11-L27), [InstallController.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/app/Http/Controllers/InstallController.php) | Provisioning over HTTP; writes DB creds and runs setup | Enabled when `INSTALLER_ENABLED=true` | DB, filesystem writes, Artisan | High | Ensure installer is disabled in production; block web-server writes to `.env`. |
| Runtime env mutation | [CheckInstallation.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/app/Http/Middleware/CheckInstallation.php#L17-L46) | Copies `.env.example` to `.env` + `key:generate` if missing | Runs in request lifecycle | Filesystem, Artisan | High | Remove runtime `.env` mutation; do setup only via CLI install command. |
| Admin Sandbox runner | [AdminSandboxController.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/app/Http/Controllers/Admin/AdminSandboxController.php) | Sandbox verification test UI | Admin-only routes | External providers via HTTP | Medium | Confirm permissions; consider environment gate or feature flag. |
| Admin Sandbox services | [AdminSandboxServicesController.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/app/Http/Controllers/Admin/AdminSandboxServicesController.php) | Generates `*-SANDBOX-*` refs and runs service calls | Admin-only routes | External providers via HTTP | Medium | Keep behind strict permissions and auditing; avoid unsafe defaults. |
| Demo data reset | [ResetAuctionDemoData.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/app/Console/Commands/ResetAuctionDemoData.php) + [AuctionDemoSeeder.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/database/seeders/AuctionDemoSeeder.php) | Truncates and re-seeds auction data | CLI command; may run anywhere | DB write/truncate | Medium/High | Add explicit `APP_ENV=local` guard; never truncate production tables. |
| Payment modal “test mode” flag | [payment-modal.js](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/public/assets/nexus/js/payment-modal.js) | Uses provider SDKs | Browser runtime | Paystack/Flutterwave/Monnify SDKs | Medium | Ensure Monnify `isTestMode` is environment-controlled, not hardcoded. |

### 2) Test Code: Mocks / Stubs / Fakes

| Type | Location | Purpose / Replaces | Isolation | Dependencies | Risk | Notes |
|---|---|---|---|---|---:|---|
| HTTP fake | [VtuHubServiceTest.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/tests/Unit/VtuHubServiceTest.php) | Replaces outbound provider HTTP calls | Test-only (`tests/`) | Laravel `Http::fake()` | Low | Good isolation; consider preferring migrations over inline schema creation to avoid drift. |
| HTTP fake + key injection | [VirtualCardServiceTest.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/tests/Feature/VirtualCardServiceTest.php) | Replaces Flutterwave card calls | Test-only | `Http::fake()` | Low/Med | Remove any `dump()` output in tests to avoid leaking payloads in CI logs. |
| Fake class | [GeminiServiceAdapterTest.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/tests/Unit/GeminiServiceAdapterTest.php) | Replaces drafting service with fake implementation | Test-only | PHP anonymous class | Low | Conforms to expected interface contract at compile-time. |
| E2E SDK stubs | [payment-gateway-modals.spec.ts](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/tests/e2e/payment-gateway-modals.spec.ts) | Stubs Paystack/Flutterwave/Monnify JS SDK globals to assert invocation | Test-only (`tests/e2e`) | Playwright | Low | Properly isolated from production; verifies invocation + parameters + timing. |
| E2E route gate | [web.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/routes/web.php#L11-L30), [payment-harness.blade.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/resources/views/e2e/payment-harness.blade.php) | Provides stable UI harness for E2E | `local/testing` only | Blade, JS | Low | Confirm production `APP_ENV` cannot be `local/testing`. |

### 3) Legacy Archive: Unsafe Test Scripts / Bypasses

| Type | Location | Purpose | Risk | Recommendation |
|---|---|---:|---:|---|
| Hardcoded SMTP creds | [test_email.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/legacy_archive/root_php/test_email.php) | Email delivery test | Critical | Delete; rotate credentials immediately. |
| Infrastructure probe | [test_conn.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/legacy_archive/root_php/test_conn.php) | Port/connectivity test | Medium | Remove from deployable tree. |
| Authorization bypass | [db_conn.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/legacy_archive/root_php/db_conn.php) | Legacy DB/env loader | High | Remove or quarantine outside web root; do not deploy. |
| Known-password seeder | [DemoUsersSeeder.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/legacy_archive/seeders/DemoUsersSeeder.php) | Seeds demo accounts | High | Remove or hard-gate to `local` only; never ship known credentials. |

## Isolation Assessment

### Properly Isolated (OK)
- Anything under `tests/` (unit/feature/e2e) is isolated from production by directory and test runner.
- E2E harness route is gated to `local/testing` environments.

### Not Properly Isolated (Needs Change)
- `VirtualCardService` simulation fallback is reachable in production when the secret key is unset.
- Installer/runtime `.env` write logic is reachable if `INSTALLER_ENABLED` is enabled.
- Demo reset/seed routines can be invoked without an explicit environment guard.

## Recommendations (Prioritized)
1. Remove committed `.env` files and rotate any exposed secrets.
2. Remove or hard-gate all production-path simulations (e.g., virtual card “simulate” paths) to `local/testing`.
3. Disable installer routes and remove runtime `.env` mutation from middleware.
4. Gate demo seed/truncate commands to `local/testing` only.
5. Add CI checks that fail builds if `isTestMode:true` or `_TEST` keys are detected in production configuration.

