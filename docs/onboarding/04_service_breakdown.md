# 04. Service Breakdown

## 4.1 Overview
The **Fuwa.NG** core business logic is encapsulated in the `App\Services` namespace. Each service handles a specific domain and is designed for high reliability and testability.

## 4.2 Wallet Service (`App\Services\WalletService`)
- **Purpose**: Manages all financial operations (debiting, crediting, refunds) across the platform.
- **Key Methods**:
    - `debit(User $user, float $amount, string $orderType, ...)`: Securely subtracts funds and creates a `pending` transaction.
    - `credit(User $user, float $amount, string $orderType, string $transactionId)`: Securely adds funds and creates a `success` transaction.
    - `failAndRefund(User $user, float $amount, string $orderType, string $transactionId)`: Reverses a failed transaction and restores funds to the user's wallet.
- **Dependencies**: `AccountBalance`, `Transaction`, `KycService`.
- **Logic**: All balance updates are wrapped in `DB::transaction` with `lockForUpdate` to prevent race conditions during concurrent requests.

## 4.3 Service Router (`App\Services\ServiceRouter`)
- **Purpose**: Provides high-availability access to third-party verification and utility APIs.
- **Key Methods**:
    - `for(string $serviceType)`: Initializes a router for a specific domain (e.g., 'nin', 'bvn', 'vtu').
    - `verify(array $payload, ?int $providerId = null)`: Attempts to fulfill a request by iterating through configured providers in order of priority.
- **Failover Logic**: If the primary provider fails (e.g., Robosttech), the router automatically attempts the request with the secondary provider (e.g., Dataverify) until a successful response is received or all providers are exhausted.
- **Configuration**: Managed via the `custom_apis` table in the database.

## 4.4 KYC & Verification Services
- **`App\Services\KycService`**: Enforces transaction limits based on a user's verification tier (Tier 1-3).
- **`App\Services\VerificationResultService`**: Handles the storage and retrieval of complex verification data (NIN details, BVN match, CAC status).
- **`App\Services\VerificationResult`**: Processes raw API responses from multiple providers into a unified system-wide schema.

## 4.5 AI Legal Hub (`App\Services\GeminiService`)
- **Purpose**: Provides intelligent text generation for drafting legal documents and answering user queries.
- **Key Methods**:
    - `generateLegalDocument(string $docType, array $inputs)`: Uses Google Gemini Pro to generate a customized legal document based on user-provided data.
    - `chat(string $message, ?string $sessionId = null)`: Provides conversational support for legal inquiries and platform usage.
- **Business Logic**: Each AI-generated document is logged and priced according to its category and complexity.

## 4.6 VTU Hub Service (`App\Services\VtuHubService`)
- **Purpose**: Centralized integration point for all Value-Added Services (Airtime, Data, Bills).
- **Key Methods**:
    - `purchaseAirtime(...)`, `purchaseData(...)`, `payBill(...)`.
- **Logic**: Integrates with providers like VTU Hub and Clubkonnect, handling response mapping and automated status tracking.

## 4.7 Other Services
- **`SmsService`**: Handles SMS delivery for 2FA, notifications, and marketing campaigns.
- **`EmailService`**: Manages transactional and marketing emails via SMTP or Amazon SES.
- **`ImageOptimizer`**: Automatically compresses and optimizes user-uploaded documents and profile pictures for storage efficiency.
- **`VirtualCardService`**: Integrates with fintech providers to issue and manage virtual dollar cards for international payments.
