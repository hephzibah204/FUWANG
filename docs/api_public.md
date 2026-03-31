# Public API (v1) – Developer Documentation

## Base URL

- Local: `http://127.0.0.1:8000/api/v1`
- Production: `<your-domain>/api/v1`

## Authentication

The API uses **Bearer tokens** (no sessions).

### Create token

`POST /auth/token`

Request body:

```json
{
  "email": "user@example.com",
  "password": "Password@123",
  "name": "my-app"
}
```

Response:

```json
{
  "status": true,
  "token": "nx_<token>",
  "token_type": "Bearer"
}
```

Store this token securely. It is shown only once.

### Use token

Send one of:

- `Authorization: Bearer nx_<token>`
- `X-Api-Token: nx_<token>`

## Rate limiting

Rate limiting is enforced per API token.

- Default: **60 requests/min**
- On limit: HTTP `429` with `Retry-After: 60`

## Response format

Successful responses generally return:

```json
{ "status": true, "message": "...", "data": {} }
```

Error responses return:

```json
{ "status": false, "message": "..." }
```

Validation errors return HTTP `422` with Laravel validation `errors`.

## Endpoints

### Get current user

`GET /me`

### Revoke current token

`DELETE /auth/token`

### Verify NIN

`POST /verifications/nin`

Body:

```json
{
  "number": "12345678901",
  "firstname": "John",
  "lastname": "Doe",
  "dob": "1990-01-01",
  "mode": "nin"
}
```

Response (success):

```json
{
  "status": true,
  "message": "NIN verified",
  "result_id": 10,
  "reference_id": "NIN_VERIFICATION-AB12CD34",
  "data": {}
}
```

### Verify BVN

`POST /verifications/bvn`

Body:

```json
{
  "number": "12345678901",
  "firstname": "John",
  "lastname": "Doe",
  "dob": "1990-01-01",
  "type": "basic"
}
```

### Get verification result

`GET /verifications/{id}`

Returns the stored `verification_results` record for the authenticated user.

### VUVAA proxy (upstream)

These endpoints proxy calls to the VUVAA API using the configured upstream provider in `custom_apis` (provider_identifier contains `vuvaa`). All requests require this API’s bearer token (same as other `/api/v1/*` routes).

`POST /vuvaa/create_user`

Creates a VUVAA user (encrypted payload upstream). Body matches the VUVAA `create_user` decrypted payload.

`POST /vuvaa/login`

Logs into VUVAA (encrypted payload upstream). Returns the decrypted login response.

`POST /vuvaa/verify_nin`

Verifies a NIN upstream via VUVAA.

Body:

```json
{ "nin": "74756011111", "reference_id": "REF1" }
```

`POST /vuvaa/in_person_verification`

In-person verification (NIN + selfie).

Body:

```json
{ "nin": "74756011111", "image": "<base64 or data-uri>", "reference_id": "REF1" }
```

`POST /vuvaa/share_code`

Share code verification.

Body:

```json
{ "share_code": "ABC123", "reference_id": "REF1" }
```

`POST /vuvaa/requery`

Requery by reference id.

Body:

```json
{ "reference_id": "REF10749036" }
```

`POST /vuvaa/wallet`

Fetch wallet details. Optional `filters` are forwarded to the upstream decrypted payload.

`POST /vuvaa/transaction_history`

Fetch transaction history. Optional `filters` are forwarded to the upstream decrypted payload.

`POST /vuvaa/reasons`

Fetch NIMC reasons list. Optional `filters` are forwarded to the upstream decrypted payload.

## Pricing & billing notes

Verification endpoints may debit the user wallet before calling upstream providers.

If the upstream call fails, a refund is attempted automatically.

## Troubleshooting

- `401 Missing API token`: send `Authorization: Bearer ...`
- `401 Invalid API token`: token revoked/expired or mistyped
- `402 Insufficient balance`: user wallet does not have enough funds for this call
- `429 Rate limit exceeded`: slow down and retry after the returned time
