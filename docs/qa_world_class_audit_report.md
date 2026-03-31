# World-Class QA Audit Report (System-Wide)

Date: 2026-03-30
Scope: Full Laravel + Vite application (web + admin + API) including tests and deployment posture.

## Executive Summary

This system is feature-rich and has several strong foundations (centralized layout meta tags + schema, CSP middleware, service toggles, Playwright + PHPUnit presence). However, there are **multiple critical security and production-readiness risks** that should be treated as release blockers.

### Release Blockers (Fix Before Production)
1. **Unauthenticated admin metrics API exposure** (`/api/v1/admin/chatbot/metrics`).
2. **Installer and runtime `.env` mutation** reachable when `INSTALLER_ENABLED=true`.
3. **Stored XSS risk** via raw Page HTML rendering (`{!! $page->content !!}`) and **SVG uploads**.
4. **Proxy trust misconfiguration** (`trustProxies(at: '*')`).
5. **Virtual card simulation paths** reachable in production when provider secret is missing.

### Key Quality Themes
- **Security posture is inconsistent**: strong CSP exists, but several endpoints and admin content pipelines bypass safe defaults.
- **Workflow maturity is low**: no first-party CI workflows detected; README is mostly stock Laravel.
- **Test coverage exists but is not comprehensive**: good direction (Playwright + PHPUnit), but critical flows still need systematic coverage.

## Methodology

- Static audit of `app/`, `routes/`, `resources/`, `public/`, `database/`, and `tests/`.
- Focused scanning for:
  - authz/authn gaps, unsafe defaults, secret handling, upload/XSS vectors
  - performance bottlenecks (unbounded queries, sleeps, schema checks in hot paths)
  - placeholders/sandbox logic and environment gating
  - SEO, accessibility, and UX consistency issues based on templates and CSS patterns

## Findings (Prioritized)

Severity levels:
- **Critical**: remote compromise/data exposure likely or high-impact production failure
- **High**: exploitable weakness or major reliability risk
- **Medium**: correctness/performance/UX risk with moderate impact
- **Low**: minor issue or best-practice improvement

### 1) Codebase Quality (Architecture, Standards, Security, Performance)

#### 1.1 Critical — Unauthenticated admin metrics API
- **Location**: [api.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/routes/api.php#L22-L24), [ChatbotAdminController.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/app/Http/Controllers/Admin/ChatbotAdminController.php#L12-L37)
- **Issue**: Public endpoint returns internal metrics and full feedback records without `auth`.
- **Repro**:
  - `GET /api/v1/admin/chatbot/metrics`
  - Observe `needs_attention` objects.
- **Recommendation**:
  - Add `auth:admin` (or `api.token`) + permission middleware.
  - Return aggregated counts only; never return full records.
- **Timeline**: 0–2 days.

#### 1.2 Critical — Installer + runtime `.env` writes (production kill switch)
- **Location**:
  - Installer routing gate: [web.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/routes/web.php#L11-L20)
  - Installer controller: [InstallController.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/app/Http/Controllers/InstallController.php)
  - Runtime `.env` mutation: [CheckInstallation.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/app/Http/Middleware/CheckInstallation.php#L17-L46)
- **Issue**: If enabled, the installer can write `.env` and provision admin/database over HTTP.
- **Repro**:
  - Set `INSTALLER_ENABLED=true` and browse `/install`.
- **Recommendation**:
  - Force installer routes to `local` only; remove runtime `.env` writes.
  - Convert installer to a CLI command.
- **Timeline**: 0–7 days.

#### 1.3 High — Stored XSS via raw page content and unsafe uploads
- **Location**:
  - Raw content stored: [PageController.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/app/Http/Controllers/Admin/PageController.php#L45-L53)
  - Rendered raw: [pages/show.blade.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/resources/views/pages/show.blade.php#L15-L20)
  - SVG allowed: [MediaController.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/app/Http/Controllers/Admin/MediaController.php#L14-L28)
- **Issue**: Admin content pipeline can inject arbitrary scripts into public pages.
- **Repro**:
  - Create/edit a CMS page with `<script>alert(1)</script>` or upload a crafted SVG.
- **Recommendation**:
  - Sanitize HTML using a robust allowlist sanitizer.
  - Disallow SVG uploads or serve SVG with safe headers + sanitize.
- **Timeline**: 1–2 weeks.

#### 1.4 High — Proxy trust is too permissive
- **Location**: [app.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/bootstrap/app.php#L16-L22)
- **Issue**: `trustProxies(at: '*')` trusts forwarded headers from any source.
- **Impact**: IP spoofing, scheme spoofing, potential auth/rate-limit bypass decisions.
- **Recommendation**:
  - Trust only known proxy IP ranges (load balancer, CDN).
- **Timeline**: 0–2 days.

#### 1.5 High — Virtual cards can silently “simulate” in production
- **Location**: [VirtualCardService.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/app/Services/VirtualCardService.php#L31-L35)
- **Issue**: If Flutterwave secret is missing, service returns simulated card objects.
- **Risk**: Financial correctness and fraud risk.
- **Recommendation**:
  - Hard-fail outside `local/testing`.
- **Timeline**: 0–7 days.

#### 1.6 High — Token endpoint lacks throttle
- **Location**: [api.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/routes/api.php#L6-L8)
- **Issue**: `/api/v1/auth/token` is brute-forceable.
- **Recommendation**:
  - Add `throttle`/rate-limit middleware and account lockout policy.
- **Timeline**: 0–2 days.

#### 1.7 Medium — Webhook signature verification is optional when config missing
- **Location**: [WebhookController.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/app/Http/Controllers/WebhookController.php#L307-L317)
- **Issue**: If webhook hash not set, events are accepted and queued.
- **Recommendation**:
  - Require signature/hmac always; reject if missing.
- **Timeline**: 1 week.

#### 1.8 Medium — Performance hotspots
- **Unbounded history loads**: [VerificationController.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/app/Http/Controllers/Service/VerificationController.php#L64-L73) uses `->latest()->get()`.
- **In-request retry sleeps**: [VerificationController.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/app/Http/Controllers/Service/VerificationController.php#L229-L238) uses `usleep()`.
- **Recommendation**:
  - Paginate history; move retries to queued jobs; use circuit breaker.
- **Timeline**: 2–4 weeks.

### 2) Development Workflow (CI/CD, Git, Reviews, Docs)

#### 2.1 High — CI/CD workflows are not present
- **Observation**: No first-party `.github/workflows/*` found.
- **Risk**: Regressions ship without automated gates.
- **Recommendation**:
  - Add GitHub Actions (or equivalent) pipelines:
    - `composer test` (PHPUnit)
    - `php artisan pint --test`
    - `npm run build`
    - `npm run test:e2e` (Playwright)
- **Timeline**: 1–2 weeks.

#### 2.2 Medium — Documentation is incomplete
- **Location**: [README.md](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/README.md)
- **Issue**: Mostly stock Laravel; lacks system-specific setup, environment variables, deployment steps.
- **Recommendation**:
  - Add docs for env vars, services, admin roles/permissions, install/deploy flow.
- **Timeline**: 1–2 weeks.

#### 2.3 Medium — Dependency hygiene
- **Issue**: `pragmarx/google2fa-laravel` pinned to `*`.
- **Recommendation**: Pin versions; add `composer audit` in CI.
- **Timeline**: 0–7 days.

### 3) UX / Accessibility (WCAG 2.1)

#### 3.1 Medium — CSP blocks inline scripts without nonce (risk of broken interactions)
- **Observation**: Inline scripts must include `nonce` or they silently fail under CSP.
- **Recommendation**:
  - Consolidate inline scripts into Vite bundles where possible.
  - When inline is unavoidable, ensure `nonce` is applied.
- **Timeline**: Ongoing; 1–2 weeks for consolidation.

#### 3.2 Medium — Contrast issues in dark UI
- **Example**: `.btn-outline` links could appear too dark when Bootstrap overrides text color.
- **Recommendation**:
  - Maintain a contrast test checklist; add automated visual regression for key CTAs.
- **Timeline**: 1 week.

#### 3.3 Medium — Accessibility baseline
- **Observation**: SweetAlert modals are used widely; keyboard focus and ARIA should be validated.
- **Recommendation**:
  - Add an accessibility pass using axe-core in Playwright.
  - Ensure modal focus trapping and keyboard navigation.
- **Timeline**: 2–4 weeks.

### 4) Mobile Experience (iOS/Android)

- **Current**: Playwright mobile emulation exists (Pixel 5, iPhone 13).
- **Gaps**:
  - No real-device performance profiling.
  - Offline flows not explicitly implemented.
- **Recommendation**:
  - Add BrowserStack (or real device lab) runs for:
    - login, wallet funding, VTU purchases, admin user management
  - Add performance budgets for first load and interaction.
- **Timeline**: 2–6 weeks.

### 5) SEO Audit

#### 5.1 Good
- Meta tags, canonical, OG/Twitter, and JSON-LD schema are present in the global layout:
  - [nexus.blade.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/resources/views/layouts/nexus.blade.php#L1-L45)

#### 5.2 Medium — Sitemap is static and incomplete
- **Location**: [sitemap.xml](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/public/sitemap.xml)
- **Issue**: Only a few URLs are listed; dynamic pages/services are not included.
- **Recommendation**:
  - Generate sitemap dynamically (routes + CMS pages + public services).
- **Timeline**: 1–2 weeks.

#### 5.3 Low — Robots.txt duplication
- **Location**: [robots.txt](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/public/robots.txt)
- **Issue**: Sitemap listed twice (relative and absolute).
- **Recommendation**: Keep one canonical absolute URL.
- **Timeline**: 0–2 days.

### 6) Marketing Copy Review

- **Strengths**: Home hero copy is clear with dual CTAs (register + talk to sales):
  - [home.blade.php](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/resources/views/marketing/home.blade.php#L16-L35)
- **Recommendations**:
  - Add consistent tone guidelines + microcopy standards (errors, empty states).
  - Add localization strategy if multi-language is planned.
- **Timeline**: 2–4 weeks.

### 7) Page-by-Page Functional Verification (Sampling + Strategy)

Because “page-by-page” is large, implement a **journey-based test matrix** and convert to Playwright suites:
- Public: home → register → login
- Wallet: fund wallet → verify provider config → open gateway modal
- Services: NIN/BVN/VTU flows (happy path + insufficient balance + provider down)
- Admin: login → users directory → fund/deduct/refund/reset password/suspend

### 8) Service Layer (APIs, DB, Caching, 3rd Parties)

- Add consistent API error contract (`{ status, code, message }`) across endpoints.
- Centralize third-party HTTP clients and add:
  - timeouts, retries in jobs (not requests), circuit breakers
  - request/response logging with PII redaction

### 9) Incomplete / Placeholder / Hardcoded

Key items already cataloged in the mock/simulation audit:
- [mock_simulation_audit_report.md](file:///c:/Users/Abiodun%20Emmanuel/Documents/CODEBASE/FUWA/docs/mock_simulation_audit_report.md)

Additional placeholders:
- Default placeholder endpoints (`https://ade.com/...`) in sandbox controller.
- Demo crypto constants in Vuvaa client.

## Recommended Implementation Timeline

### 0–2 Days (Immediate)
- Protect `/api/v1/admin/chatbot/metrics`.
- Add throttling/rate limits to `/api/v1/auth/token`.
- Fix proxy trust configuration.
- Remove robots.txt duplicate sitemap entry.

### 0–7 Days (Short)
- Hard-disable installer in production and remove runtime `.env` writes.
- Remove/gate virtual card simulation mode.
- Pin dependency versions and add `composer audit`.

### 1–2 Weeks
- Implement HTML sanitization for CMS pages and tighten SVG handling.
- Build real CI workflows and enforce lint/tests.
- Generate dynamic sitemap.

### 2–6 Weeks
- Performance pass: paginate heavy queries, move retries to jobs, add caching where needed.
- Accessibility: add automated axe checks and keyboard navigation test coverage.
- Expand Playwright journeys and add visual regression on key CTAs.

