# Fuwa.ng Service Isolation Architecture

## Executive Summary

This document describes the architectural approach for implementing service isolation at Fuwa.ng, starting with the logistics service module. The architecture enables standalone service modules while maintaining unified user authentication through Single Sign-On (SSO).

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        Fuwa.ng Platform                          │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐ │
│  │   Main Auth │  │   Users DB  │  │   Service Sessions DB   │ │
│  │   System    │  │   (Unified) │  │   (Cross-service SSO)   │ │
│  └─────────────┘  └─────────────┘  └─────────────────────────┘ │
│         │                │                      │                │
└─────────┼────────────────┼──────────────────────┼────────────────┘
          │                │                      │
          ▼                ▼                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Service Isolation Layer                        │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │              SSO Bridge Service (Shared)                     ││
│  │  - Token Generation & Validation                             ││
│  │  - Cross-service Session Management                          ││
│  │  - User Profile Synchronization                              ││
│  └─────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
          │
          ▼
┌──────────────────┐     ┌──────────────────┐     ┌──────────────────┐
│  Logistics Svc   │     │   Auctions Svc   │     │    Other Svcs    │
│  ┌────────────┐  │     │  ┌────────────┐  │     │  ┌────────────┐  │
│  │  API Layer │  │     │  │  API Layer │  │     │  │  API Layer │  │
│  │  (Isolated)│  │     │  │  (Isolated)│  │     │  │  (Isolated)│  │
│  └────────────┘  │     │  └────────────┘  │     │  └────────────┘  │
│  ┌────────────┐  │     │  ┌────────────┐  │     │  ┌────────────┐  │
│  │  Profiles  │  │     │  │  Profiles  │  │     │  │  Profiles  │  │
│  │    DB      │  │     │  │    DB      │  │     │  │    DB      │  │
│  └────────────┘  │     │  └────────────┘  │     │  └────────────┘  │
│  ┌────────────┐  │     │  ┌────────────┐  │     │  ┌────────────┐  │
│  │  Business  │  │     │  │  Business  │  │     │  │  Business  │  │
│  │    Logic   │  │     │  │    Logic   │  │     │  │    Logic   │  │
│  └────────────┘  │     │  └────────────┘  │     │  └────────────┘  │
└──────────────────┘     └──────────────────┘     └──────────────────┘
```

## Core Components

### 1. SSO Bridge Service

**Location:** `app/Services/Auth/SSOBridgeService.php`

**Responsibilities:**
- Generate and validate service-specific tokens
- Manage cross-service session lifecycle
- Authenticate existing platform users to specific services
- Token revocation and cleanup

**Token Structure:**
- 64-character random token
- SHA-256 hash stored in database
- 1-hour expiry time
- Service-specific scope definitions

### 2. Service Sessions Model

**Location:** `app/Models/ServiceSession.php`

**Database:** `service_sessions` table

**Fields:**
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to users |
| service | varchar(50) | Service identifier (e.g., 'logistics') |
| token | varchar(64) | SHA-256 hashed token |
| scopes | json | Permission scopes array |
| ip_address | varchar(45) | Client IP |
| user_agent | text | Browser client info |
| expires_at | timestamp | Token expiration time |

**Indexes:**
- `(token, service)` - Token lookup
- `(user_id, service)` - User sessions per service
- `(service, expires_at)` - Service cleanup queries

### 3. Service-Specific Profile Models

**Location:** `app/Models/LogisticsProfile.php`

Each service has its own profile model that extends the unified user identity:

```php
LogisticsProfile {
    user_id: bigint (FK to users)
    company_name: string (optional)
    contact_person: string
    phone: string
    business_type: enum(individual, company, enterprise)
    preferred_delivery: enum(standard, express, overnight)
    notification_preferences: json
    is_active: boolean
}
```

## User Journey Flows

### Flow 1: New User → Logistics Registration

```
1. User visits /logistics
2. Clicks "Create Account"
3. Fills registration form (fullname, email, password)
4. System creates:
   a. User record in main users table
   b. LogisticsProfile record in logistics_profiles table
   c. ServiceSession token for 'logistics' service
5. User is redirected to /logistics/dashboard
```

### Flow 2: Existing Fuwa.ng User → SSO Login

```
1. User is already logged into Fuwa.ng
2. Visits /logistics and clicks "Login with Fuwa.ng"
3. System validates main platform session (Auth::check())
4. System generates ServiceSession token for 'logistics'
5. User is redirected to /logistics/dashboard with pre-filled profile
```

### Flow 3: External User → Direct Login

```
1. User has Fuwa.ng account but visits /logistics directly
2. Enters email/password on logistics login form
3. System authenticates against main users table
4. System generates ServiceSession token for 'logistics'
5. User is redirected to /logistics/dashboard
```

## Database Schema

### Main Platform Tables (Shared)

```sql
-- Users table (existing)
users {
    id BIGINT PRIMARY KEY,
    email VARCHAR(255) UNIQUE,
    fullname VARCHAR(255),
    username VARCHAR(20) UNIQUE,
    password VARCHAR(255),
    user_status ENUM('active', 'suspended', 'inactive'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
}

-- Service sessions (new)
service_sessions {
    id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),
    service VARCHAR(50),
    token VARCHAR(64),
    scopes JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
}
```

### Service-Specific Tables (Isolated)

```sql
-- Logistics profiles
logistics_profiles {
    id BIGINT PRIMARY KEY,
    user_id BIGINT UNIQUE REFERENCES users(id),
    company_name VARCHAR(255),
    contact_person VARCHAR(255),
    phone VARCHAR(20),
    alternate_phone VARCHAR(20),
    email VARCHAR(255),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    business_type ENUM('individual', 'company', 'enterprise'),
    preferred_delivery ENUM('standard', 'express', 'overnight'),
    notification_preferences JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
}

-- Logistics requests (existing)
logistics_requests {
    id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),
    sender_name VARCHAR(100),
    sender_address VARCHAR(255),
    recipient_name VARCHAR(100),
    recipient_address VARCHAR(255),
    weight DECIMAL(10,2),
    description VARCHAR(255),
    delivery_type ENUM('standard', 'express', 'overnight'),
    amount DECIMAL(10,2),
    tracking_id VARCHAR(20) UNIQUE,
    status ENUM('processing', 'in_transit', 'out_for_delivery', 'delivered'),
    waybill_path VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
}
```

## Routing Structure

```
# Main Platform Routes (web.php)
- /login → Main login page
- /register → Main registration page
- /dashboard → Main dashboard

# Logistics Standalone Routes (logistics.php)
- /logistics/landing → Public logistics landing
- /logistics/login → Logistics-specific login
- /logistics/register → Logistics-specific registration
- /logistics/sso → SSO authentication endpoint
- /logistics/dashboard → Logistics dashboard (requires auth)
- /logistics/book → New booking form (requires auth)

# API Routes (logistics.php - API prefix)
- /api/v1/logistics/auth/login → API login
- /api/v1/logistics/auth/sso → API SSO
- /api/v1/logistics/auth/register → API registration
- /api/v1/logistics/auth/validate → Token validation
- /api/v1/logistics/auth/revoke → Token revocation
```

## Security Implementation

### Rate Limiting

| Endpoint | Limit | Window | Implementation |
|----------|-------|--------|----------------|
| Login | 5 attempts | 1 minute | Laravel throttle middleware |
| Registration | 5 attempts | 1 minute | Laravel throttle middleware |
| SSO | 10 attempts | 1 minute | Laravel throttle middleware |
| API General | 60 requests | 1 minute | Laravel RateLimiter |

### Token Security

1. **Hashing:** Tokens stored as SHA-256 hashes
2. **Expiry:** 1-hour token lifetime
3. **Revocation:** Immediate invalidation on logout
4. **IP Binding:** Optional IP address validation
5. **Scope Limitation:** Minimal required scopes per operation

### Input Validation

All authentication endpoints validate:
- Email format and uniqueness
- Password strength (8+ chars, mixed case, numbers, symbols)
- Required field presence
- CSRF protection for web routes

## Performance Considerations

### Target Benchmarks

| Metric | Target | Implementation |
|--------|--------|----------------|
| Auth response time | < 200ms | Token cache, indexed lookups |
| Dashboard load | < 1 second | Eager loading, pagination |
| Concurrent users | 10,000 | Horizontal scaling ready |

### Optimization Strategies

1. **Database Indexes:** Composite indexes on service_sessions for fast lookups
2. **Token Caching:** Short-term cache for validation results
3. **Connection Pooling:** Efficient database connection management
4. **Lazy Loading:** Service profiles loaded on demand

## Monitoring & Alerting

### Key Metrics to Track

1. **Authentication Failures**
   - Failed login attempts per minute
   - Failed token validations per minute
   - Error rate thresholds

2. **Token Operations**
   - Token generation rate
   - Token revocation rate
   - Expired token cleanup

3. **Service Health**
   - API response times
   - Database query performance
   - Session table size growth

### Alert Triggers

```yaml
alerts:
  - name: high_auth_failure_rate
    condition: failure_rate > 10% in 5 minutes
    severity: warning

  - name: service_token_expiry_anomaly
    condition: expired_tokens > 1000 in 1 minute
    severity: critical

  - name: database_connection_exhaustion
    condition: connections > 80% max
    severity: critical
```

## Deployment Checklist

### Pre-Deployment
- [ ] Run database migrations
- [ ] Clear application cache
- [ ] Verify rate limiting configuration
- [ ] Test SSO flow in staging

### Post-Deployment
- [ ] Verify service sessions table indexes
- [ ] Monitor authentication error rates
- [ ] Check token generation performance
- [ ] Validate redirect flows

### Rollback Procedure
1. Revert application deployment
2. Run `php artisan migrate:rollback --step=1` for service_sessions
3. Optionally rollback logistics_profiles migration
4. Clear all caches

## Future Extensibility

### Adding New Services

1. Create service profile model (e.g., `AuctionProfile`)
2. Create service-specific auth controller
3. Add service routes to `routes/{service}.php`
4. Register routes by including the service route file from `routes/web.php` (the application bootstraps routing via `bootstrap/app.php`)
5. Update SSO Bridge scopes as needed

### OAuth2/SAML Integration

The SSO Bridge is designed to integrate with external identity providers:

```php
interface ServiceProviderInterface {
    public function authenticate(array $credentials): ?User;
    public function createServiceToken(User $user, string $service): string;
    public function validateToken(string $token): ?array;
}
```

## Appendix: File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   └── Logistics/
│   │   │       └── LogisticsAuthController.php
│   │   └── Api/
│   │       └── Logistics/
│   │           └── LogisticsApiController.php
│   └── Middleware/
│       ├── ValidateServiceToken.php
│       └── RateLimitByService.php
├── Models/
│   ├── ServiceSession.php
│   ├── LogisticsProfile.php
│   └── User.php (updated with logisticsProfile relation)
├── Services/
│   └── Auth/
│       └── SSOBridgeService.php
└── Providers/
    └── RouteServiceProvider.php (rate limiting)

database/migrations/
├── 2026_04_16_000001_create_service_sessions_table.php
└── 2026_04_16_000002_create_logistics_profiles_table.php

routes/
└── logistics.php

resources/views/
└── logistics/
    ├── auth/
    │   ├── login.blade.php
    │   └── register.blade.php
    └── landing.blade.php

tests/
├── Feature/
│   └── Auth/
│       └── Logistics/
│           └── LogisticsAuthTest.php
└── Unit/
    └── Services/
        └── Auth/
            └── SSOBridgeServiceTest.php
```
