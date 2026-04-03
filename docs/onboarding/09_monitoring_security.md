# 09. Monitoring, Alerting & Security

## 9.1 Overview
**Fuwa.NG** is designed with a "Security-First" and "Monitor-All" mindset. We use industry-standard tools and protocols to ensure high visibility and data integrity.

## 9.2 Monitoring & Dashboards
- **Laravel Telescope**: Accessible at `/telescope` for real-time monitoring of requests, jobs, exceptions, and database queries.
- **Admin Dashboard**:
    - **System Metrics**: Real-time stats for daily verifications, success rates, and provider health.
    - **Queue Monitor**: Accessible via **Admin → Queue** to monitor background jobs (emails, campaigns).
- **Log Aggregation**:
    - **Local Logs**: Located in `storage/logs/laravel.log`.
    - **Admin Audit Logs**: Detailed tracking of sensitive actions in the `admin_audit_logs` table.
- **System Health Checks**:
    - Automated health checks for database connectivity, Redis, and external API providers.

## 9.3 Alerting Configuration
- **Slack/Discord Integration**: Critical exceptions are automatically sent to the configured Slack/Discord webhook (if enabled in `config/logging.php`).
- **Email Notifications**: System-wide alerts for failed jobs and high-priority incidents are sent to the system administrators.

## 9.4 Security Documentation

### 9.4.1 Authentication & Authorization
- **Role-Based Access Control (RBAC)**: Managed via `spatie/laravel-permission` with clear boundaries between Super Admins, Admins, and Agents.
- **Two-Factor Authentication (2FA)**: Required for all Super Admin accounts and optional for regular users (managed via `pragmarx/google2fa-laravel`).
- **API Token Security**: All external API requests must include a secure, uniquely generated token with configurable permissions.

### 9.4.2 Data Encryption
- **At-Rest Encryption**: Sensitive data like API keys and passwords are encrypted using Laravel's built-in `Encrypter` and `Hash` services.
- **In-Transit Encryption**: All communication is enforced over **HTTPS** via the `EnforceHttps` middleware.

### 9.4.3 Webhook & API Security
- **IP Allowlisting**: Critical webhooks (e.g., from Monnify/Payvessel) are only accepted from verified provider IP ranges.
- **Signature Verification**: Every incoming webhook must include a valid HMAC signature for verification.
- **Rate Limiting**: Applied to all public and API endpoints to prevent DDoS and brute-force attacks.

### 9.4.4 Vulnerability Mitigation
- **SQL Injection**: Prevented via Laravel's Eloquent ORM and Query Builder.
- **XSS/CSRF**: Mitigated via built-in Blade escaping and CSRF token protection.
- **Security Headers**: Enforced via `SecurityHeaders` middleware (HSTS, CSP, X-Frame-Options).

## 9.5 Incident Response Procedures
1.  **Detection**: Alert received via Slack or found in Telescope.
2.  **Triage**: Admin assesses the severity (P1 - Critical, P2 - Major, P3 - Minor).
3.  **Containment**: For P1/P2 issues, the affected service or provider is temporarily disabled via **Admin → Feature Toggles**.
4.  **Resolution**: Developer identifies and fixes the root cause.
5.  **Recovery**: Changes are deployed, and the service is re-enabled.
6.  **Post-Mortem**: Document the incident, root cause, and preventative measures for future reference.
