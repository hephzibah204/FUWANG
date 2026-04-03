# 02. System Architecture

## 2.1 Overview
**Fuwa.NG** follows a robust, enterprise-grade architecture based on the **Laravel framework (v11.x)**. It is a unified platform integrating multiple third-party services through a centralized "Smart Routing" gateway.

## 2.2 Core Components
- **Laravel Framework (PHP 8.2+)**: Core backend providing routing, Eloquent ORM, and background job processing.
- **Smart Routing Service (`App\Services\ServiceRouter`)**: Automatically switches between API providers (Robosttech, Dataverify, etc.) based on status and priority to ensure 99.9% uptime.
- **Wallet & Transaction System (`App\Services\WalletService`)**: A secure, wallet-based interface for managing user balances, transactions, and commission payouts.
- **AI Legal Hub (`App\Services\GeminiService`)**: Integration with Google Gemini Pro API for generating 50+ types of legal documents.
- **Verification Engine (`App\Services\KycService`, `App\Services\VerificationResultService`)**: Connects to major national databases for identity verification (NIN, BVN, CAC).

## 2.3 Data Flow
1.  **Request**: User initiates a verification or service request (e.g., NIN verification) via the web UI or API.
2.  **Validation**: `VerificationController` or `VtuApiController` validates the request payload.
3.  **Debit**: `WalletService` attempts to debit the user's wallet based on the service's dynamic pricing.
4.  **Routing**: `ServiceRouter` selects the highest-priority active provider for the requested service.
5.  **Execution**: The selected provider's API is called.
6.  **Response**: The response is processed, results are saved to `verification_results` or `vtu_transactions`, and the user receives a confirmation.
7.  **Audit**: `AdminAuditLog` records the transaction, and any failure triggers an automated refund if applicable.

## 2.4 Infrastructure Topology
- **Vercel/Vps/Server**: The platform is designed for cloud-native deployment with Vercel support.
- **MySQL (v8.0+)**: Relational database for storing user accounts, wallet balances, and transaction history.
- **Redis (Predis)**: Used for high-speed caching and background queue management.
- **Third-Party Integrations**:
    - **Identity**: Robosttech, Dataverify, Vuvaa, Payvessel.
    - **Payments**: Monnify, Payvessel (for wallet funding).
    - **Utilities**: VTU Hub, Clubkonnect.
    - **AI**: Google Gemini Pro API.

## 2.5 Security Architecture
- **Role-Based Access Control (RBAC)**: Managed via `spatie/laravel-permission` for fine-grained admin and agent access.
- **API Token Authentication**: Custom `api.token` middleware for secure external integrations.
- **Webhook Security**: IP Allowlisting and HMAC signature verification for payment notifications.
- **Audit Trails**: Detailed logging of every administrative action and sensitive verification request.
