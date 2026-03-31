# Developer API Documentation & Integration Guide

Welcome to the Developer API. This document provides a comprehensive guide to integrating with our platform services, including authentication flows, code examples, and SDK details.

## 1. Authentication Flow

Our API utilizes a **wallet-based authentication system** via Bearer tokens (OAuth 2.0 standard). To access any API endpoint, developers must maintain a minimum balance in their platform wallet.

### 1.1 Generating an API Token
You can generate a token via the dashboard or using the `/auth/token` endpoint.

```bash
curl -X POST https://api.fuwa.ng/api/v1/auth/token \
  -H "Content-Type: application/json" \
  -d '{
    "email": "developer@example.com",
    "password": "your_password",
    "device_name": "server_1"
  }'
```

### 1.2 Using the Token
Pass the token in the `Authorization` header for all subsequent requests:
`Authorization: Bearer <your_token>`

### 1.3 Wallet Balance Enforcement
If your wallet balance falls below the minimum required threshold, your API key will be automatically suspended. The API will respond with HTTP `402 Payment Required` and the following message:

```json
{
  "status": false,
  "message": "fund not sufficient",
  "error": "fund not sufficient"
}
```

## 2. API Endpoints Reference

Our API follows strict RESTful design principles and enforces HTTPS.

### 2.1 Identity Verification
- **POST /api/v1/verifications/nin**: Verify National Identity Number.
- **POST /api/v1/verifications/bvn**: Verify Bank Verification Number.

### 2.2 VTU & Bill Payments
- **POST /api/v1/vtu/airtime**: Top up mobile airtime.
- **POST /api/v1/vtu/data**: Purchase data bundles.
- **POST /api/v1/vtu/cable**: Subscribe to Cable TV (DSTV, GOTV, etc.).
- **POST /api/v1/vtu/electricity**: Pay prepaid or postpaid electricity bills.

For detailed request/response schemas, please refer to the OpenAPI 3.0 specification available at `/api-docs/openapi.yaml`.

## 3. Code Examples

### Python (using `requests`)
```python
import requests

url = "https://api.fuwa.ng/api/v1/vtu/airtime"
headers = {
    "Authorization": "Bearer YOUR_API_TOKEN",
    "Content-Type": "application/json"
}
payload = {
    "network": "MTN",
    "amount": 1000,
    "phone": "08012345678"
}

response = requests.post(url, json=payload, headers=headers)
print(response.json())
```

### Node.js (using `axios`)
```javascript
const axios = require('axios');

async function buyAirtime() {
  try {
    const response = await axios.post('https://api.fuwa.ng/api/v1/vtu/airtime', {
      network: 'MTN',
      amount: 1000,
      phone: '08012345678'
    }, {
      headers: {
        'Authorization': 'Bearer YOUR_API_TOKEN'
      }
    });
    console.log(response.data);
  } catch (error) {
    if (error.response && error.response.status === 402) {
      console.error("Error: fund not sufficient");
    }
  }
}
buyAirtime();
```

### PHP (using `cURL`)
```php
<?php
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => "https://api.fuwa.ng/api/v1/vtu/airtime",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        "network" => "MTN",
        "amount" => 1000,
        "phone" => "08012345678"
    ]),
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer YOUR_API_TOKEN",
        "Content-Type: application/json"
    ],
]);

$response = curl_exec($ch);
curl_close($ch);
echo $response;
```

## 4. SDK Availability

We provide official SDKs to accelerate your integration:
- **PHP SDK**: Available via Composer (`composer require fuwang/sdk-php`)
- **Node.js SDK**: Available via NPM (`npm install @fuwang/sdk-node`)
- **Python SDK**: Available via Pip (`pip install fuwang-sdk`)

Each SDK automatically handles token management, retries, and rate limit tracking.

## 5. Best Practices & Error Handling

1. **Rate Limiting**: Our API allows 60 requests per minute by default. If exceeded, a `429 Too Many Requests` status is returned.
2. **HTTPS**: All traffic must be sent over HTTPS. Non-HTTPS requests are rejected.
3. **HTTP Status Codes**:
   - `200 OK`: Success
   - `400 Bad Request`: Validation error
   - `401 Unauthorized`: Invalid or missing token
   - `402 Payment Required`: "fund not sufficient"
   - `429 Too Many Requests`: Rate limit exceeded
   - `500 Internal Server Error`: Platform error
