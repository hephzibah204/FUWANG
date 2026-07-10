# Logistics Service Authentication API Documentation

## Overview

The Logistics Service Authentication API provides secure authentication mechanisms for the Fuwa.ng logistics service module. It supports direct authentication and service-token validation for cross-service calls.

## Base URL

```
Production: https://fuwa.ng/api/v1/logistics
Staging: https://staging.fuwa.ng/api/v1/logistics
Local: http://localhost/api/v1/logistics
```

## Authentication Methods

### 1. Direct Login

**Endpoint:** `POST /api/v1/logistics/auth/login`

Authenticate using email and password credentials.

**Request:**
```json
{
  "email": "user@example.com",
  "password": "YourPassword123!"
}
```

**Response (200 OK):**
```json
{
  "status": "success",
  "token": "64-character-service-token",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "fullname": "John Doe",
    "username": "johndoe",
    "logistics_profile": {
      "id": 1,
      "company_name": null,
      "contact_person": "John Doe",
      "phone": null,
      "is_active": true
    }
  }
}
```

**Error Response (401 Unauthorized):**
```json
{
  "status": "error",
  "message": "Invalid credentials."
}
```

### 2. New User Registration

**Endpoint:** `POST /api/v1/logistics/auth/register`

Register a new logistics service account.

**Request:**
```json
{
  "fullname": "John Doe",
  "username": "johndoe",
  "email": "user@example.com",
  "password": "YourPassword123!",
  "transaction_pin": "1234"
}
```

**Response (201 Created):**
```json
{
  "status": "success",
  "token": "64-character-service-token",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "fullname": "John Doe",
    "username": "johndoe"
  }
}
```

**Validation Rules:**
- `fullname`: Required, string, max 255 characters
- `username`: Required, string, max 20 characters, unique in users table
- `email`: Required, valid email, unique in users table
- `password`: Required, min 8 characters, mixed case, numbers, symbols
- `transaction_pin`: Required, 4 characters

### 3. Token Validation

**Endpoint:** `POST /api/v1/logistics/auth/validate`

Validate an existing service token.

**Headers:**
```
Authorization: Bearer <service_token>
```

**Response (200 OK):**
```json
{
  "valid": true,
  "user_id": 1,
  "scopes": ["read", "profile"]
}
```

**Response (401 Unauthorized):**
```json
{
  "valid": false
}
```

### 4. Token Revocation

**Endpoint:** `POST /api/v1/logistics/auth/revoke`

Revoke the current service token.

**Headers:**
```
Authorization: Bearer <service_token>
```

**Response (200 OK):**
```json
{
  "status": "success",
  "message": "Token revoked."
}
```

## Rate Limiting

| Endpoint | Limit | Window |
|----------|-------|--------|
| `/auth/login` | 5 attempts | 1 minute |
| `/auth/register` | 5 attempts | 1 minute |

## Error Codes

| HTTP Status | Description |
|-------------|-------------|
| 400 | Bad Request - Invalid input data |
| 401 | Unauthorized - Invalid credentials or token |
| 403 | Forbidden - Account not active |
| 422 | Validation Error - Input validation failed |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error |

## Security Headers

All responses include the following headers:

```
X-RateLimit-Limit: 5
X-RateLimit-Remaining: 4
Content-Type: application/json
```

## Token Expiry

Service tokens expire after **1 hour** (3600 seconds). Clients should handle token refresh before expiry.

## Service Dashboard Redirect

After successful authentication, clients should redirect users to:

- **Logistics Dashboard:** `/logistics/dashboard`
- **Logistics Booking:** `/logistics/book`
