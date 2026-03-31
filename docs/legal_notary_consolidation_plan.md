# Legal + Notary Consolidation Plan (Unified Legal Platform)

## 1) Current State Summary (as-is)

### User-facing modules
- **AI Legal Hub**
  - Routes: `/services/legal-hub/*` (feature-gated)
  - Storage: `legal_documents` (draft → completed) with PDF output
- **Notary Services**
  - Routes: `/services/notary/*` (feature-gated)
  - Storage: `notary_requests` (draft → pending_stamp/completed) with draft/final PDFs
  - Catalog/pricing: `notary_settings` (+ SystemSetting overrides)

### Key issues to resolve before consolidation
- **Duplicate legal drafting stacks** (GeminiService vs AiController) and duplicate entrypoints (LegalHubController vs NexusServiceController).
- **Notary payment route mismatch**: UI references a pay endpoint that is not currently routed.
- **Stamp/signature asset mismatch**: DB columns vs PDF generator vs admin “branding” system settings do not align.
- **Admin permissions**: Notary ops are not currently protected by an explicit permission middleware.

## 2) Target Architecture (to-be)

### Design goals
- Single “Legal Platform” surface area for users and admins.
- Preserve all existing capabilities: AI drafting, preview, wallet payment, PDF generation, stamping workflow, admin status updates, catalog management.
- Maintain data integrity, auditability, and privacy/security compliance.
- Enable phased rollout and rollback with feature flags (no downtime cutover).

### Proposed logical components
- **Legal Platform Gateway (HTTP)**
  - New unified routes (kept behind a new feature flag):
    - `GET /services/legal` (unified UI)
    - `POST /services/legal/draft`
    - `POST /services/legal/finalize`
    - `POST /services/legal/notary/pay`
    - `GET /services/legal/records` (history)
  - Compatibility routes remain temporarily:
    - `/services/legal-hub/*` and `/services/notary/*` redirect or proxy to unified handlers.

- **Unified Domain Layer**
  - `LegalDraftingService` (single interface; multiple providers)
  - `LegalPricingService` (catalog-based pricing + overrides)
  - `LegalPdfService` (single renderer entrypoint; template selection)
  - `LegalStampingService` (applies stamp/signature, watermark rules)
  - `LegalWorkflowService` (state machine for lifecycle transitions)
  - `LegalRecordRepository` (abstracts underlying storage during migration)

- **Data Layer**
  - Phase 1: keep existing tables, unify via repository abstraction.
  - Phase 2: optionally merge into a single canonical table once stable.

### Canonical lifecycle model
All legal items become one of:
- `draft` → `queued_payment` (optional) → `paid`
- `paid` → `pending_stamp` (if court stamp required)
- `paid` → `completed` (if no stamp required)
- `pending_stamp` → `completed` (admin action)
- Terminal failure states: `failed_payment`, `failed_render`, `cancelled`

### Security & compliance controls
- **Access control**
  - Users may only access their own records.
  - Admin access requires explicit permissions per area:
    - `manage_legal_platform` (catalog + workflows)
    - `manage_notary_queue` (stamp queue + status changes)
    - `view_legal_records` (read-only audit/reporting)
- **Audit logging**
  - All admin actions already auto-logged; extend with consistent action names:
    - `legal_platform.catalog.*`, `legal_platform.request.*`, `legal_platform.stamp.*`
  - Add user activity logs for legal record access and downloads (PII-safe, no document body).
- **Data protection**
  - Encrypt sensitive fields at rest if required by policy (document body, form_data, generated_content).
  - Limit data exposure in exports and logs; redact PII in audit meta.
  - Explicit retention policy for generated PDFs and AI content.
- **Regulatory posture**
  - Maintain immutable references (reference_id/reference) and timestamps for legal evidence.
  - Maintain chain-of-custody: status transitions + actor (user/admin) + time.

## 3) Unified UI (WordPress-like, preserves functionality)

### User UI layout (single entry)
`/services/legal` provides:
- **Document Catalog**
  - Filter by category: “AI Legal Drafts”, “Notary”, “Court-Stamped”
  - Each item shows: price, expected workflow (instant vs stamp queue), requirements
- **Drafting workspace**
  - Form inputs (document-specific) + AI draft preview
  - “Regenerate draft” (rate-limited)
  - “Save draft” (no payment)
- **Payment & finalize**
  - Shows wallet balance, required amount, confirmation dialog
  - On finalize: creates/updates record, queues render, shows status + download button when ready
- **My Legal Records**
  - List of all records (both legacy and new) with status filters and download links

### Admin UI (unified)
- **Catalog management**
  - Single catalog UI (replaces split hardcoded legal hub types)
  - Category, template config, price, requires stamp, status
- **Notary queue**
  - Unified queue view for `pending_stamp` items (from legacy notary + unified records)
  - Actions: approve stamp, reject, request changes, mark completed

## 4) Data Model Consolidation Strategy

### Phase 1 (no table merge; safest)
Introduce an internal canonical representation:
- `LegalRecordDTO` with fields superset of:
  - `user_id`, `document_type`, `category`, `status`, `price`, `reference`, `content/html`, `form_data`, `pdf_paths`, `requires_stamp`, `stamped_at`, `created_at`, `updated_at`

Mapping:
- AI Legal Hub records map from `legal_documents`
- Notary records map from `notary_requests`
- Catalog for both comes from `notary_settings` (extended to cover AI Legal Hub docs)

### Phase 2 (optional hard merge)
Create `legal_requests` canonical table:
- Columns: superset of legal_documents + notary_requests, plus:
  - `source` (`legal_hub|notary|unified`)
  - `workflow_type` (`ai_draft|notary`)
  - `requires_court_stamp`, `draft_pdf_path`, `final_pdf_path`, `content`, `form_data`, `amount_paid`
Migration:
- Backfill `legal_requests` from both tables in batches.
- Dual-read via repository; dual-write for new records.
- Cutover reads after validation; freeze legacy writes; deprecate old tables later.

## 5) Migration Strategy (99.9% uptime)

### Principles
- No breaking schema changes on the critical path.
- Additive DB migrations only during transition window.
- Feature flag driven cutover; rapid rollback via toggles.

### Steps
1. **Prepare**
   - Add new feature flag: `legal_platform_enabled`
   - Add missing routes for notary pay and unify all notary endpoints behind a single handler.
   - Normalize stamp/signature asset source of truth.
2. **Build unified UI + domain services**
   - Implement repository abstraction to read/write both legacy stores.
   - Ensure all writes are transactional (wallet + status + pdf generation).
3. **Shadow mode**
   - For new requests, optionally write to both canonical and legacy tables (if Phase 2 is enabled).
   - Log discrepancies in a dedicated metric channel.
4. **Gradual rollout**
   - Enable unified UI for a small cohort (feature gate by admin/flag).
   - Monitor error rate, wallet failures, pdf generation time, queue health.
5. **Cutover**
   - Make unified UI default; keep legacy routes redirecting.
6. **Backfill historical data**
   - Backfill in batches (nightly jobs), verifying counts/hashes.
7. **Decommission**
   - After stable period, remove legacy controllers/routes and cleanup unused schema.

## 6) AuthN/AuthZ Updates

### User authentication
- No change in auth mechanism; continue to require `auth` middleware.
- Add explicit record-level authorization policies:
  - `LegalRecordPolicy@view`, `@download`, `@update`

### Admin authorization
- Define permissions and enforce middleware on:
  - catalog operations
  - queue approvals/status changes
  - record downloads/reports
- Ensure every admin action writes to audit logs with consistent action naming.

## 7) Testing Protocols

### Unit tests
- Domain services:
  - pricing calculation (catalog vs override precedence)
  - workflow transitions and guards
  - PDF generation selection and storage paths
  - stamping rules and watermarking

### Integration tests
- End-to-end user flow:
  - draft → finalize → pdf available
  - notary: draft → pay → pending_stamp → admin completes → final pdf
- Wallet integrity tests:
  - debit success → record transitions
  - debit failure → no record completion
  - pdf failure → refund behavior (if applicable)
- Security tests:
  - user cannot access others’ records
  - admin permissions enforced on notary queue and catalog

### UAT (user acceptance testing)
- Scenario-based scripts:
  - Legal Hub doc creation for each document type
  - Notary court-stamp and non-stamp flows
  - PDF appearance validation (watermarks, stamp/signature placement)
  - Mobile + desktop UI checks

### Performance tests
- Draft generation latency and timeouts
- PDF generation throughput under concurrency
- Queue job SLA compliance under load

## 8) Documentation Deliverables
- **User guide**: unified workflows, record history, payment/stamping meanings.
- **Admin guide**: catalog management, queue operations, status meanings, audit log review.
- **API reference**: consolidated endpoints and response schemas.
- **Ops runbook**: migrations, rollout toggles, troubleshooting, queue worker scaling.

## 9) Rollback Plan

### Fast rollback (feature flag)
- Disable `legal_platform_enabled` to revert users to legacy pages.
- Keep data written in forward-compatible format; legacy flows remain functional.

### Data rollback considerations
- All migrations should be additive during the rollout period.
- If Phase 2 canonical table is enabled:
  - Keep legacy tables as the system of record until stable.
  - Do not drop/alter legacy columns during rollout window.

### Operational rollback
- Stop new unified jobs/workers; drain queues.
- Revert route mapping to legacy controllers.
- Validate wallet ledger consistency and reconcile any partial states.

## 10) Post-Consolidation Monitoring (stability + performance + satisfaction)

### System stability (SLO 99.9% uptime)
- Availability of:
  - `/services/legal` and downstream endpoints
  - PDF download endpoints
  - wallet debit operations used in legal flows
- Error budgets tracked weekly.

### Performance benchmarks
- P95/P99 latency:
  - draft generation
  - finalize request handling (queue enqueue only)
  - pdf generation job duration
- Queue health:
  - pending jobs by queue
  - oldest job age
  - failed job rate

### Business/user satisfaction
- Funnel metrics:
  - drafts created → paid finalizations → completed documents
  - notary pending_stamp aging distribution
- UX telemetry:
  - drop-off points, time-to-completion, support tickets tagged “legal”

### Compliance metrics
- Audit coverage:
  - percent of admin actions logged with correct actor + timestamps
  - record access logs present for downloads
- Data retention:
  - scheduled cleanup success rate (if policy requires expiration)

