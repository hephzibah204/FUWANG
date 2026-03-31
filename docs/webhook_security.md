# Webhook Security (Admin Guide)

## Summary

This application enforces **mandatory cryptographic signature validation** for all webhook endpoints. Requests without a valid signature are rejected.

Webhook security configuration is managed entirely from the **Admin UI**:

- Admin → Settings → Security

No direct database edits or manual SQL are required.

## Webhook Endpoints

### VerifyMe Address Webhook

- **URL**: `POST /webhooks/verifyme/address`
- **Auth**: Required signature using the configured secret
- **Header**: `X-VerifyMe-Signature`
- **Body**: JSON payload (raw body is used for signature verification)

**Signature algorithm**

- `signature = HMAC(payload_raw, secret)`
- Supported digests:
  - `sha256`
  - `sha512`
- Format:
  - hex string (recommended)
  - `sha256=<hex>` and `sha512=<hex>` prefixes are also accepted

**Expected behavior**

- Missing secret configuration → `503`
- Missing signature → `403`
- Invalid signature → `403`
- Valid signature → `200` with `{"status":"received"}`

### Payvessel Webhook

- **URL**: `POST /webhooks/payvessel`
- **Auth**: Required signature using Payvessel secret key configured in API Keys
- **Header**: `PAYVESSEL_HTTP_SIGNATURE`
- **Algorithm**: `sha512` HMAC over raw payload

### Palmpay Webhook

- **URL**: `POST /webhooks/palmpay`
- **Auth**: Required signature using Paypoint secret key configured in API Keys
- **Header**: `PAYMENTPOINT_SIGNATURE`
- **Algorithm**: `sha256` HMAC over raw payload

## Admin UI: Settings → Security

### VerifyMe IP Allowlist

You can store a list of known VerifyMe webhook source IPs:

- Key: `verifyme_webhook_ips`
- Accepted formats:
  - IPv4 (e.g. `3.255.23.38`)
  - IPv6 (e.g. `2001:db8::1`)
- Validation:
  - Invalid IPs are rejected with clear error feedback

This allowlist is intended for:

- Operational visibility
- Incident response workflows
- Coordinating firewall/WAF rules

### VerifyMe Secret Rotation

You can rotate the VerifyMe webhook signing secret from the UI:

- Key: `verifyme_webhook_secret`
- Rotation requires updating the upstream sender immediately after change

**Recommended rotation procedure**

1. Generate a new secret in Admin UI (Generate button)
2. Update the upstream VerifyMe webhook configuration with the new secret
3. Rotate/apply the secret in Admin UI (Rotate Secret button)
4. Validate webhook delivery using a test event

## Role-Based Access Control (RBAC)

Only authorized admin accounts can modify webhook security settings:

- Users with `admins.role = superadmin` can modify Security settings.
- For a single-admin deployment, the only admin can modify Security settings.

## Audit Logging

All Security configuration changes are audit logged with:

- Timestamp
- Admin identity
- Action name
- Change metadata (counts and diffs)
- Request IP and User-Agent

Audit events are visible in the Security tab.

## API Specifications (Admin Settings)

All endpoints below require:

- Admin authentication (`auth:admin`)
- Security permission (`admin.security`)

### Update VerifyMe IP Allowlist

- **URL**: `POST /admin/settings/security/verifyme/ips`
- **Body**:
  - `verifyme_webhook_ips` (string, comma/space/newline separated)
- **Responses**:
  - `200`: `{ "status": true, "message": "VerifyMe IP allowlist updated." }`
  - `422`: `{ "status": false, "message": "Invalid IP address: ..." }`

### Generate VerifyMe Secret (Not Persisted)

- **URL**: `POST /admin/settings/security/verifyme/secret/generate`
- **Response**:
  - `200`: `{ "status": true, "secret": "<generated>" }`

### Rotate VerifyMe Secret (Persisted)

- **URL**: `POST /admin/settings/security/verifyme/secret`
- **Body**:
  - `verifyme_webhook_secret` (string, min 16)
- **Responses**:
  - `200`: `{ "status": true, "message": "VerifyMe webhook secret updated." }`
  - `422`: validation errors

## Security Best Practices

- Always treat webhook secrets as production credentials.
- Rotate secrets on a schedule (e.g., quarterly) and after any suspected exposure.
- Avoid sharing secrets over chat tools; use a secure password manager.
- Ensure all webhook URLs are HTTPS in production.
- Monitor webhook failures and repeated signature failures (possible probing).

## Troubleshooting

### VerifyMe webhook returns 503

- Cause: `verifyme_webhook_secret` is not configured.
- Fix: Admin → Settings → Security → Rotate a secret.

### VerifyMe webhook returns 403

- Signature is missing or invalid.
- Ensure the upstream sender:
  - Uses the exact raw payload bytes
  - Signs with the same secret stored in Admin UI
  - Sends signature in `X-VerifyMe-Signature`

### Admin cannot save Security settings

- Cause: insufficient permission.
- Fix:
  - Ensure admin user has role `superadmin`, or
  - If this is a single-admin installation, verify there is only one admin record.

