# 07. Testing Documentation

## 7.1 Overview
**Fuwa.NG** maintains high quality and reliability through a comprehensive testing suite. We emphasize **Feature Tests** for business-critical logic (Wallet, Verification, Auth) and **Unit Tests** for isolated utility functions.

## 7.2 Testing Frameworks
- **PHPUnit (v11+)**: Core backend testing for controllers, services, and models.
- **Mockery**: Used to simulate third-party API responses (Robosttech, Dataverify, Gemini).
- **Playwright** (Optional/Legacy): Used for end-to-end browser testing for critical UI flows.

## 7.3 Core Backend Testing
Backend tests are located in the `tests/` directory:
- **`tests/Feature`**: High-level integration tests for routing, controllers, and services.
    - **`tests/Feature/Admin/SelfFundingTest.php`**: Verifies super admin self-funding and auto-creation of user accounts.
    - **`tests/Feature/VerificationTest.php`**: Simulates multiple provider responses and failover logic.
    - **`tests/Feature/WalletTest.php`**: Verifies transaction integrity, debits, credits, and refunds.
- **`tests/Unit`**: Isolated tests for models and support classes.

## 7.4 Running Tests
1.  **Run All Tests**:
    ```bash
    php artisan test
    ```
2.  **Run a Specific Test File**:
    ```bash
    php artisan test tests/Feature/Admin/SelfFundingTest.php
    ```
3.  **Run with Code Coverage** (Requires Xdebug):
    ```bash
    php artisan test --coverage
    ```

## 7.5 Testing Procedures
- **Mocking External APIs**: Use `Http::fake()` or custom mock objects to prevent real API calls during testing.
- **Database Refresh**: Feature tests should use the `RefreshDatabase` trait to ensure a clean state for each test.
- **Seeding Data**: Use `AdminFactory`, `UserFactory`, and `AccountBalance` factories to quickly populate test data.
- **Manual Verification**:
    - **Sandbox Mode**: Use the "Sandbox" toggle in the Admin Dashboard to test verifications without real billing.
    - **Self-Funding**: Super Admins can add test credits to their accounts via `/admin/self-funding`.

## 7.6 Performance Benchmarks
- **Verification Response Time**: Should be under 5s for mocked responses and under 10s for real provider integrations.
- **Wallet Debit/Credit**: Should be under 500ms under 100 concurrent requests (tested via stress-testing tools).

## 7.7 Manual Testing Procedures
1.  **Admin RBAC**: Verify that regular admins cannot access super admin routes (e.g., self-funding).
2.  **Wallet Lock**: Verify that a user cannot spend more than their balance during high-concurrency requests.
3.  **Audit Trail**: Verify that all administrative changes are correctly logged in `admin_audit_logs`.
4.  **UI Responsiveness**: Verify the "Nexus" dashboard works across mobile, tablet, and desktop views.
