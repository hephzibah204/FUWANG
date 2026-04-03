# 03. API Specification

## 3.1 Overview
The **Fuwa.NG API (v1)** provides programmatic access to identity verification, digital services, and wallet management. All endpoints are versioned under `/api/v1/`.

## 3.2 Authentication
All protected API requests must include a Bearer token in the `Authorization` header:
```http
Authorization: Bearer YOUR_API_TOKEN
```
Tokens can be generated via the [Admin Dashboard] or by calling the `/auth/token` endpoint (if configured).

## 3.3 Core Endpoints

### 3.3.1 Authentication
- **POST `/auth/token`**: Create a new API token.
    - **Payload**: `email`, `password`.
    - **Response**: `token`, `expires_at`.
- **GET `/me`**: Get the current user profile and balance.

### 3.3.2 Identity Verification
- **POST `/verifications/nin`**: Verify a National Identity Number (NIN).
    - **Payload**: `number` (NIN), `type` (e.g., 'number', 'phone', 'demography').
- **POST `/verifications/bvn`**: Verify a Bank Verification Number (BVN).
    - **Payload**: `number` (BVN), `type` (e.g., 'number', 'match').
- **GET `/verifications/{id}`**: Get the result of a specific verification request.

### 3.3.3 VTU (Airtime & Data)
- **POST `/vtu/airtime`**: Purchase airtime.
    - **Payload**: `network`, `amount`, `phone`.
- **POST `/vtu/data`**: Purchase data bundle.
    - **Payload**: `network`, `plan_id`, `phone`.
- **POST `/vtu/cable`**: Renew cable TV subscription.
- **POST `/vtu/electricity`**: Pay electricity bills.

### 3.3.4 Legal Catalog
- **GET `/legal/catalog`**: List all available legal document types.
- **GET `/legal/catalog/{documentType}`**: Get details for a specific document type.
- **GET `/legal/pricing/{documentType}`**: Get pricing for a document type.

## 3.4 Rate Limiting
The API implements strict rate limiting via the `ApiRateLimit` middleware:
- **Default**: 60 requests per minute per token.
- **Auth/Token**: 5 requests per minute.
- **WhatsApp Widget**: 10 clicks per minute per IP.

## 3.5 Error Codes
The API uses standard HTTP status codes:
- **200 OK**: Request successful.
- **201 Created**: Resource created successfully.
- **400 Bad Request**: Validation error or missing parameters.
- **401 Unauthorized**: Missing or invalid API token.
- **402 Payment Required**: Insufficient wallet balance.
- **403 Forbidden**: Token does not have permission for the requested action.
- **429 Too Many Requests**: Rate limit exceeded.
- **500 Internal Server Error**: An unexpected server error occurred.

## 3.6 Example Call (NIN Verification)
```bash
curl -X POST https://fuwa.ng/api/v1/verifications/nin \
     -H "Authorization: Bearer {token}" \
     -H "Content-Type: application/json" \
     -d '{"number": "12345678901", "type": "number"}'
```
