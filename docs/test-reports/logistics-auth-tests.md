## Logistics Auth Test Report

### Scope

- Logistics API registration/login/token lifecycle
- Service session token generation/validation/revocation

### Test Suites

- Feature: `Tests\Feature\Auth\Logistics\LogisticsAuthTest`
- Unit: `Tests\Unit\Services\Auth\SSOBridgeServiceTest`

### Scenarios Covered

- New user registers via logistics API and receives a service token
- Invalid email registration is rejected with 422 validation error response
- Duplicate email registration is rejected with 422 validation error response
- Existing user logs in and receives a service token
- Invalid credentials are rejected
- Inactive users cannot authenticate for logistics service token issuance
- Token validation succeeds for valid tokens
- Token validation fails for expired tokens
- Token revocation removes active tokens
- Rate limiting triggers after repeated invalid login attempts

### Execution Result

- All tests in the two suites pass in the current environment when running:
  - `php artisan test --filter='(LogisticsAuthTest|SSOBridgeServiceTest)'`
