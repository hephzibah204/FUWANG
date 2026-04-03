# 05. Database Schema

## 5.1 Overview
**Fuwa.NG** uses a relational database (MySQL) to ensure data integrity, especially for financial transactions and identity records. The schema is managed via Laravel migrations.

## 5.2 Entity Relationships (ERD)

### 5.2.1 Core Tables
- **`users`**: Central table for all platform users (admins, agents, regular users).
    - **Fields**: `id`, `fullname`, `username`, `email`, `phone`, `password`, `kyc_tier`, `status`, `role`.
- **`admins`**: Detailed administrative accounts.
    - **Relationships**: One-to-one with `users` (via email or id), Many-to-Many with roles/permissions via `spatie/laravel-permission`.
- **`account_balances`**: Real-time wallet balances.
    - **Relationships**: One-to-one with `users`.
- **`transactions`**: Immutable ledger of all wallet activities.
    - **Fields**: `id`, `user_email`, `order_type`, `amount`, `balance_before`, `balance_after`, `status`, `transaction_id`.

### 5.2.2 Service Tables
- **`custom_apis`**: Registry of third-party providers for verification and VTU.
    - **Fields**: `id`, `name`, `service_type`, `endpoint`, `api_key`, `priority`, `status`.
- **`verification_results`**: Persistent storage of verification data (NIN, BVN, CAC).
    - **Fields**: `id`, `user_id`, `type`, `status`, `payload`, `response_data`, `admin_note`.
- **`vtu_transactions`**: Specific records for airtime, data, and bill payments.
- **`legal_documents`**: Generated legal documents and their metadata.
- **`notary_requests`**: Requests for document certification.

### 5.2.3 Support Tables
- **`tickets`** & **`ticket_replies`**: Helpdesk system for user support.
- **`admin_audit_logs`**: Detailed logging of sensitive administrative actions.
- **`system_settings`**: Global configuration (e.g., funding limits, feature toggles).

## 5.3 Indexing Strategy
To ensure high performance for frequent queries, indexes are applied to:
- **`users.email`**, **`users.username`** (Unique).
- **`transactions.transaction_id`**, **`transactions.user_email`** (Search).
- **`verification_results.user_id`**, **`verification_results.type`** (Filter).
- **`account_balances.user_id`**, **`account_balances.email`** (Balance lookup).
- **`custom_apis.service_type`**, **`custom_apis.status`** (Service routing).

## 5.4 Migration History
The database has evolved through multiple phases:
1.  **Phase 1 (2026-03-06)**: Core identity (Users, Balances, Transactions).
2.  **Phase 2 (2026-03-07 to 2026-03-09)**: Service integration (Custom APIs, Legal, Notary).
3.  **Phase 3 (2026-03-11)**: Performance & Security (Indexes, Audit Logs, API Tokens).
4.  **Phase 4 (2026-03-17 to 2026-03-20)**: Ecosystem expansion (Auctions, Payments, VTU).
5.  **Phase 5 (2026-03-25 to 2026-03-31)**: Advanced features (Referrals, Chatbot, RBAC).

## 5.5 Key Models
- **`App\Models\User`**: Core user model with `kyc_tier` logic.
- **`App\Models\AccountBalance`**: Wallet balance management.
- **`App\Models\Transaction`**: Immutable ledger entry.
- **`App\Models\CustomApi`**: Provider configuration.
- **`App\Models\VerificationResult`**: Dynamic response storage.
- **`App\Models\SystemSetting`**: Centralized key-value settings store.
