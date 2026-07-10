## Cross-Service Authentication Monitoring

### What Is Monitored

- Invalid or expired service-token validations
- Missed token revocations (revoke requested but no matching token found)

### Where Events Are Emitted

- `App\Services\Auth\SSOBridgeService::validateServiceToken()`
- `App\Services\Auth\SSOBridgeService::revokeServiceToken()`

### Log Channel

- Channel: `security`
- File: `storage/logs/security.log`
- Retention: 30 days (configurable)

### Correlation IDs

All requests include `X-Correlation-ID` (generated if missing) to support tracing across services.

### Recommended Alerts

- Spike in `Invalid or expired service token` messages over a short window
- Sustained increase in 401 responses on `/api/v1/logistics/auth/validate`
- Repeated “revocation missed” events for the same `token_hash_prefix`
