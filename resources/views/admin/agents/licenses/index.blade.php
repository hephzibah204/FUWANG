@extends('layouts.nexus')

@section('title', 'NIN Enrollment Agent Station Licenses | Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Top Header -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.agents.overview') }}" class="text-white-50 text-xs text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1"></i> Command Center
                </a>
                <span class="text-white-50 text-xs">/</span>
                <a href="{{ route('admin.agents.index') }}" class="text-white-50 text-xs text-decoration-none">Agents Directory</a>
                <span class="text-white-50 text-xs">/</span>
                <span class="text-warning text-xs fw-semibold">Station Licenses</span>
            </div>
            <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-certificate text-warning me-2"></i>NIN Enrollment Station Licenses Desk</h3>
            <p class="text-white-50 mb-0">Manage agent license accreditation, review offline payment proofs, and manually accredit legacy pre-website stations.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-light rounded-pill fw-bold">
                <i class="fa-solid fa-users me-1"></i>All Agents
            </a>
            <a href="{{ route('admin.settings.index') }}#tab-pricing" class="btn btn-outline-warning rounded-pill fw-bold">
                <i class="fa-solid fa-gear me-1"></i>Pricing Config
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-3 mb-4 p-3" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0;">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 rounded-3 mb-4 p-3" style="background: rgba(239, 68, 68, 0.15); color: #fca5a5;">
            <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
        </div>
    @endif

    <!-- Promo Pricing Alert Banner -->
    <div class="card border-0 rounded-4 p-3 mb-4" style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.15), rgba(180, 83, 9, 0.25)); border: 1px solid rgba(234, 179, 8, 0.4) !important;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-2 text-center" style="background: rgba(234, 179, 8, 0.25); width: 48px; height: 48px;">
                    <i class="fa-solid fa-tag text-warning fa-xl"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning text-dark font-monospace fw-bold">ACTIVE PROMO PRICE</span>
                        <span class="text-white fw-bold fs-5">₦{{ number_format($effectiveFee, 2) }}</span>
                    </div>
                    <small class="text-white-50">Current promo price is active until <strong>October 10th, 2026</strong>. Agents can pay online via Paystack, wallet balance, or offline bank transfer.</small>
                </div>
            </div>
            <div>
                <span class="badge rounded-pill px-3 py-2" style="background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.4);">
                    <i class="fa-solid fa-info-circle me-1"></i>Admin Discretion: Approval does not require prior payment
                </span>
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 rounded-4 p-3 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <span class="text-white-50 text-xs text-uppercase font-monospace mb-1 d-block">Accredited (Paid)</span>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="text-success fw-bold mb-0">{{ number_format($counts['paid']) }}</h3>
                    <i class="fa-solid fa-circle-check text-success fa-lg"></i>
                </div>
                <small class="text-white-50 mt-2 d-block">Revenue: <strong>₦{{ number_format($counts['total_revenue'], 2) }}</strong></small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 rounded-4 p-3 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <span class="text-white-50 text-xs text-uppercase font-monospace mb-1 d-block">Pending Proof Review</span>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="text-warning fw-bold mb-0">{{ number_format($counts['pending_review']) }}</h3>
                    <i class="fa-solid fa-clock text-warning fa-lg"></i>
                </div>
                <small class="text-white-50 mt-2 d-block">Offline receipts awaiting check</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 rounded-4 p-3 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <span class="text-white-50 text-xs text-uppercase font-monospace mb-1 d-block">Unpaid Licenses</span>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="text-danger fw-bold mb-0">{{ number_format($counts['unpaid']) }}</h3>
                    <i class="fa-solid fa-circle-xmark text-danger fa-lg"></i>
                </div>
                <small class="text-white-50 mt-2 d-block">Agents yet to pay license fee</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 rounded-4 p-3 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <span class="text-white-50 text-xs text-uppercase font-monospace mb-1 d-block">Total Agents</span>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="text-info fw-bold mb-0">{{ number_format($counts['total']) }}</h3>
                    <i class="fa-solid fa-users text-info fa-lg"></i>
                </div>
                <small class="text-white-50 mt-2 d-block">Total registered stations</small>
            </div>
        </div>
    </div>

    <!-- PENDING REVIEWS QUEUE (If any) -->
    @if($pendingReviews->isNotEmpty())
        <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(234, 179, 8, 0.08); border: 1px solid rgba(234, 179, 8, 0.3) !important;">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="text-warning fw-bold mb-0"><i class="fa-solid fa-bell me-2"></i>Offline Payment Proofs Awaiting Verification ({{ $pendingReviews->count() }})</h5>
                    <p class="text-white-50 small mb-0">Agents who transferred offline or submitted pre-launch claims. Review receipt proof and approve/reject.</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                    <thead>
                        <tr class="text-white-50 border-bottom border-secondary">
                            <th>Agent Details</th>
                            <th>Claim Type</th>
                            <th>Amount Paid</th>
                            <th>Bank & Ref</th>
                            <th>Date / Submitted</th>
                            <th>Proof Document</th>
                            <th class="text-end">Verification Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingReviews as $pAgent)
                            @php
                                $meta = (array) ($pAgent->license_proof_meta ?? []);
                            @endphp
                            <tr>
                                <td>
                                    <strong class="text-white d-block">{{ $pAgent->full_name }}</strong>
                                    <small class="text-white-50">{{ $pAgent->phone_number }}</small>
                                    @if($pAgent->company_agent_code)
                                        <span class="badge bg-secondary font-monospace">{{ $pAgent->company_agent_code }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($pAgent->license_payment_method === 'legacy_pre_platform')
                                        <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock-rotate-left me-1"></i>Pre-Website Legacy Claim</span>
                                    @else
                                        <span class="badge bg-info text-dark"><i class="fa-solid fa-receipt me-1"></i>Bank Transfer Proof</span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="text-white">₦{{ number_format((float)($meta['amount_paid'] ?? $effectiveFee), 2) }}</strong>
                                </td>
                                <td>
                                    <span class="text-white">{{ $meta['bank_name'] ?? 'N/A' }}</span>
                                    <br><small class="text-info font-monospace">{{ $meta['transaction_reference'] ?? $pAgent->license_payment_reference ?? 'No Ref' }}</small>
                                </td>
                                <td>
                                    <small class="text-white-50 d-block">Paid: {{ $meta['payment_date'] ?? 'N/A' }}</small>
                                    <small class="text-white-50 font-monospace">Sub: {{ $pAgent->updated_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    @if($pAgent->license_proof_path)
                                        <a href="{{ asset('storage/' . $pAgent->license_proof_path) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3">
                                            <i class="fa-solid fa-eye me-1"></i>View Proof
                                        </a>
                                    @else
                                        <span class="text-white-50 small">No File</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <!-- Approve Proof Form -->
                                        <form action="{{ route('admin.agents.licenses.approve_proof', $pAgent->id) }}" method="POST" onsubmit="return confirm('Approve offline payment proof and mark license as PAID for {{ $pAgent->full_name }}?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
                                                <i class="fa-solid fa-check me-1"></i>Approve
                                            </button>
                                        </form>

                                        <!-- Reject Proof Button Modal -->
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" data-toggle="modal" data-bs-toggle="modal" data-target="#rejectModal{{ $pAgent->id }}" data-bs-target="#rejectModal{{ $pAgent->id }}">
                                            <i class="fa-solid fa-times me-1"></i>Reject
                                        </button>
                                    </div>

                                    <!-- Reject Modal -->
                                    <div class="modal fade text-start" id="rejectModal{{ $pAgent->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content bg-dark text-white rounded-4 border border-danger">
                                                <div class="modal-header border-secondary">
                                                    <h5 class="modal-title fw-bold text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>Reject Payment Proof</h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" style="background:transparent; border:0; font-size:1.5rem;">&times;</button>
                                                </div>
                                                <form action="{{ route('admin.agents.licenses.reject_proof', $pAgent->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body p-4">
                                                        <p class="text-white-50 small mb-3">Specify the reason for rejecting this payment proof. The agent will see this note and can re-upload.</p>
                                                        <div class="mb-3">
                                                            <label class="form-label text-white small fw-bold">Rejection Reason</label>
                                                            <textarea name="rejection_reason" rows="3" class="form-control text-white bg-dark border-secondary rounded-3" required placeholder="e.g. Transaction could not be found in company bank statement / Invalid amount"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-secondary">
                                                        <button type="button" class="btn btn-outline-light rounded-pill" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger rounded-pill px-4">Confirm Rejection</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Filters & Search Bar -->
    <div class="card border-0 rounded-4 p-3 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.agents.licenses.index') }}" class="btn btn-sm rounded-pill {{ !$status ? 'btn-primary' : 'btn-outline-light' }}">
                    All Licenses ({{ $counts['total'] }})
                </a>
                <a href="{{ route('admin.agents.licenses.index', ['status' => 'paid']) }}" class="btn btn-sm rounded-pill {{ $status === 'paid' ? 'btn-success fw-bold' : 'btn-outline-success' }}">
                    Paid ({{ $counts['paid'] }})
                </a>
                <a href="{{ route('admin.agents.licenses.index', ['status' => 'pending_review']) }}" class="btn btn-sm rounded-pill {{ $status === 'pending_review' ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning' }}">
                    Pending Review ({{ $counts['pending_review'] }})
                </a>
                <a href="{{ route('admin.agents.licenses.index', ['status' => 'unpaid']) }}" class="btn btn-sm rounded-pill {{ $status === 'unpaid' ? 'btn-danger fw-bold' : 'btn-outline-danger' }}">
                    Unpaid ({{ $counts['unpaid'] }})
                </a>
            </div>

            <form action="{{ route('admin.agents.licenses.index') }}" method="GET" class="d-flex gap-2">
                @if($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm rounded-pill" placeholder="Search agent name, code, phone, ref...">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Search</button>
            </form>
        </div>
    </div>

    <!-- Master License Directory Table -->
    <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                <thead>
                    <tr class="text-white-50 border-bottom border-secondary">
                        <th>#</th>
                        <th>Agent Name & Code</th>
                        <th>Phone / User</th>
                        <th>Agency Status</th>
                        <th>License Status</th>
                        <th>Payment Method</th>
                        <th>Fee Paid & Ref</th>
                        <th>Paid Date</th>
                        <th class="text-end">License Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agents as $agent)
                        <tr>
                            <td>{{ $agent->id }}</td>
                            <td>
                                <strong class="text-white">{{ $agent->full_name }}</strong>
                                @if($agent->is_fast_tracked)
                                    <span class="badge bg-warning text-dark ms-1"><i class="fa-solid fa-bolt me-1"></i>Existing</span>
                                @else
                                    <span class="badge bg-info text-dark ms-1">New</span>
                                @endif
                                <br>
                                @if($agent->company_agent_code)
                                    <code class="text-warning font-monospace">{{ $agent->company_agent_code }}</code>
                                @else
                                    <small class="text-white-50">AG-{{ str_pad($agent->id, 5, '0', STR_PAD_LEFT) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="text-white">{{ $agent->phone_number }}</span>
                                <br><small class="text-white-50">{{ $agent->user->email ?? 'No Email' }}</small>
                            </td>
                            <td>
                                @if($agent->isApproved())
                                    <span class="badge bg-success rounded-pill px-2 py-1">Approved</span>
                                @elseif($agent->isPending())
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1">Pending</span>
                                @elseif($agent->isRejected())
                                    <span class="badge bg-danger rounded-pill px-2 py-1">Rejected</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-2 py-1">{{ ucfirst($agent->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($agent->isLicensePaid())
                                    <span class="badge bg-success rounded-pill px-3 py-1"><i class="fa-solid fa-check-circle me-1"></i>Paid</span>
                                @elseif($agent->isLicensePendingReview())
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-1"><i class="fa-solid fa-clock me-1"></i>Review</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3 py-1"><i class="fa-solid fa-xmark me-1"></i>Unpaid</span>
                                @endif
                            </td>
                            <td>
                                @if($agent->license_payment_method === 'paystack')
                                    <span class="badge bg-primary"><i class="fa-solid fa-credit-card me-1"></i>Paystack</span>
                                @elseif($agent->license_payment_method === 'wallet')
                                    <span class="badge bg-info text-dark"><i class="fa-solid fa-wallet me-1"></i>Wallet</span>
                                @elseif($agent->license_payment_method === 'legacy_pre_platform')
                                    <span class="badge bg-warning text-dark" title="Paid before website launch"><i class="fa-solid fa-clock-rotate-left me-1"></i>Pre-Website</span>
                                @elseif($agent->license_payment_method === 'offline_proof')
                                    <span class="badge bg-secondary"><i class="fa-solid fa-receipt me-1"></i>Offline Transfer</span>
                                @elseif($agent->license_payment_method === 'admin_manual')
                                    <span class="badge bg-secondary"><i class="fa-solid fa-user-shield me-1"></i>Admin Manual</span>
                                @elseif($agent->license_payment_method === 'waived')
                                    <span class="badge bg-light text-dark"><i class="fa-solid fa-hand-holding-heart me-1"></i>Waived</span>
                                @else
                                    <span class="text-white-50 small">-</span>
                                @endif
                            </td>
                            <td>
                                @if($agent->license_fee_paid !== null)
                                    <strong class="text-white">₦{{ number_format((float)$agent->license_fee_paid, 2) }}</strong>
                                    <br><small class="text-white-50 font-monospace">{{ $agent->license_payment_reference ?: '-' }}</small>
                                @else
                                    <span class="text-white-50 small">-</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-white-50">{{ $agent->license_paid_at?->format('d M Y') ?: '-' }}</small>
                            </td>
                            <td class="text-end">
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-outline-light btn-sm rounded-pill dropdown-toggle px-3" type="button" data-toggle="dropdown" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.agents.show', $agent->id) }}">
                                                <i class="fa-solid fa-eye me-2 text-primary"></i>View Agent Dossier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider border-secondary"></li>
                                        <li>
                                            <button class="dropdown-item text-success" type="button" data-toggle="modal" data-bs-toggle="modal" data-target="#markPaidModal{{ $agent->id }}" data-bs-target="#markPaidModal{{ $agent->id }}">
                                                <i class="fa-solid fa-check-double me-2"></i>Mark License as Paid...
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item text-warning" type="button" data-toggle="modal" data-bs-toggle="modal" data-target="#editLicenseModal{{ $agent->id }}" data-bs-target="#editLicenseModal{{ $agent->id }}">
                                                <i class="fa-solid fa-pen-to-square me-2"></i>Edit License / Corrections...
                                            </button>
                                        </li>
                                        @if($agent->isLicensePaid())
                                            <li>
                                                <button class="dropdown-item text-danger" type="button" data-toggle="modal" data-bs-toggle="modal" data-target="#revokeModal{{ $agent->id }}" data-bs-target="#revokeModal{{ $agent->id }}">
                                                    <i class="fa-solid fa-ban me-2"></i>Revoke License...
                                                </button>
                                            </li>
                                        @endif
                                    </ul>
                                </div>

                                <!-- Mark Paid Modal -->
                                <div class="modal fade text-start" id="markPaidModal{{ $agent->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content bg-dark text-white rounded-4 border border-success">
                                            <div class="modal-header border-secondary">
                                                <h5 class="modal-title fw-bold text-success"><i class="fa-solid fa-certificate me-2"></i>Accredit Station License</h5>
                                                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" style="background:transparent; border:0; font-size:1.5rem;">&times;</button>
                                            </div>
                                            <form action="{{ route('admin.agents.licenses.mark_paid', $agent->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body p-4">
                                                    <p class="text-white-50 small mb-3">Accredit station license for <strong>{{ $agent->full_name }}</strong> ({{ $agent->company_agent_code ?: 'AG-' . $agent->id }}).</p>

                                                    <div class="mb-3">
                                                        <label class="form-label text-white small fw-bold">Payment Method / Origin</label>
                                                        <select name="payment_method" class="form-select bg-dark text-white border-secondary rounded-3" required>
                                                            <option value="legacy_pre_platform">Paid Before Website Launch (Pre-Website Legacy)</option>
                                                            <option value="admin_manual">Verified Offline Bank Transfer / Cash</option>
                                                            <option value="paystack">Direct Paystack Verified</option>
                                                            <option value="wallet">Wallet Deduction</option>
                                                            <option value="waived">Special Exemption / Waived Fee</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label text-white small fw-bold">Amount Paid (₦)</label>
                                                        <input type="number" step="0.01" name="amount" value="{{ $effectiveFee }}" class="form-control text-white bg-dark border-secondary rounded-3">
                                                        <small class="text-white-50 text-2xs">Leave default for active promo fee.</small>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label text-white small fw-bold">Payment Reference / Receipt ID (Optional)</label>
                                                        <input type="text" name="payment_reference" placeholder="e.g. REC-12345 or Bank Transfer Ref" class="form-control text-white bg-dark border-secondary rounded-3">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label text-white small fw-bold">Payment Date (Optional)</label>
                                                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="form-control text-white bg-dark border-secondary rounded-3">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label text-white small fw-bold">Admin Audit Notes</label>
                                                        <textarea name="admin_notes" rows="2" placeholder="e.g. Confirmed on pre-launch banking ledger" class="form-control text-white bg-dark border-secondary rounded-3"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-secondary">
                                                    <button type="button" class="btn btn-outline-light rounded-pill" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success rounded-pill px-4"><i class="fa-solid fa-check-circle me-1"></i>Accredit License</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Edit / Correction Modal -->
                                <div class="modal fade text-start" id="editLicenseModal{{ $agent->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content bg-dark text-white rounded-4 border border-warning">
                                            <div class="modal-header border-secondary">
                                                <h5 class="modal-title fw-bold text-warning"><i class="fa-solid fa-pen-to-square me-2"></i>Edit License / Corrections</h5>
                                                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" style="background:transparent; border:0; font-size:1.5rem;">&times;</button>
                                            </div>
                                            <form action="{{ route('admin.agents.licenses.update', $agent->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body p-4">
                                                    <p class="text-white-50 small mb-3">Modify or correct license details for <strong>{{ $agent->full_name }}</strong> ({{ $agent->company_agent_code ?: 'AG-' . $agent->id }}).</p>

                                                    <div class="mb-3">
                                                        <label class="form-label text-white small fw-bold">License Status</label>
                                                        <select name="license_status" class="form-select bg-dark text-white border-secondary rounded-3" required>
                                                            <option value="paid" {{ ($agent->license_status === 'paid' || $agent->isLicensePaid()) ? 'selected' : '' }}>Paid / Accredited</option>
                                                            <option value="unpaid" {{ ($agent->license_status === 'unpaid' || $agent->isLicenseUnpaid()) ? 'selected' : '' }}>Unpaid</option>
                                                            <option value="pending_review" {{ $agent->license_status === 'pending_review' ? 'selected' : '' }}>Pending Review (Offline Proof / Claim)</option>
                                                            <option value="waived" {{ $agent->license_status === 'waived' ? 'selected' : '' }}>Special Exemption / Waived Fee</option>
                                                        </select>
                                                        <small class="text-white-50 text-2xs">Change status directly if an administrative error occurred.</small>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label text-white small fw-bold">Payment Method / Origin</label>
                                                        <select name="payment_method" class="form-select bg-dark text-white border-secondary rounded-3">
                                                            <option value="legacy_pre_platform" {{ $agent->license_payment_method === 'legacy_pre_platform' ? 'selected' : '' }}>Paid Before Website Launch (Pre-Website Legacy)</option>
                                                            <option value="admin_manual" {{ $agent->license_payment_method === 'admin_manual' ? 'selected' : '' }}>Verified Offline Bank Transfer / Cash</option>
                                                            <option value="paystack" {{ $agent->license_payment_method === 'paystack' ? 'selected' : '' }}>Direct Paystack Verified</option>
                                                            <option value="wallet" {{ $agent->license_payment_method === 'wallet' ? 'selected' : '' }}>Wallet Deduction</option>
                                                            <option value="offline_proof" {{ $agent->license_payment_method === 'offline_proof' ? 'selected' : '' }}>Offline Bank Transfer Proof</option>
                                                            <option value="waived" {{ $agent->license_payment_method === 'waived' ? 'selected' : '' }}>Special Exemption / Waived Fee</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label text-white small fw-bold">Amount Paid (₦)</label>
                                                        <input type="number" step="0.01" name="amount" value="{{ $agent->license_fee_paid ?? $effectiveFee }}" class="form-control text-white bg-dark border-secondary rounded-3">
                                                        <small class="text-white-50 text-2xs">Correct the recorded payment amount if wrong.</small>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label text-white small fw-bold">Payment Reference / Receipt ID (Optional)</label>
                                                        <input type="text" name="payment_reference" value="{{ $agent->license_payment_reference }}" placeholder="e.g. REC-12345 or Bank Transfer Ref" class="form-control text-white bg-dark border-secondary rounded-3">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label text-white small fw-bold">Payment Date (Optional)</label>
                                                        <input type="date" name="payment_date" value="{{ $agent->license_paid_at ? $agent->license_paid_at->format('Y-m-d') : date('Y-m-d') }}" class="form-control text-white bg-dark border-secondary rounded-3">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label text-white small fw-bold">Admin Audit Notes / Correction Reason</label>
                                                        <textarea name="admin_notes" rows="2" placeholder="e.g. Price adjusted due to bank ledger reconciliation error" class="form-control text-white bg-dark border-secondary rounded-3">{{ $agent->license_admin_notes }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-secondary">
                                                    <button type="button" class="btn btn-outline-light rounded-pill" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold"><i class="fa-solid fa-save me-1"></i>Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Revoke Modal -->
                                @if($agent->isLicensePaid())
                                    <div class="modal fade text-start" id="revokeModal{{ $agent->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content bg-dark text-white rounded-4 border border-danger">
                                                <div class="modal-header border-secondary">
                                                    <h5 class="modal-title fw-bold text-danger"><i class="fa-solid fa-ban me-2"></i>Revoke Station License</h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" style="background:transparent; border:0; font-size:1.5rem;">&times;</button>
                                                </div>
                                                <form action="{{ route('admin.agents.licenses.revoke', $agent->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body p-4">
                                                        <p class="text-white-50 small mb-3">Are you sure you want to revoke the license for <strong>{{ $agent->full_name }}</strong>? The status will revert to UNPAID.</p>
                                                        <div class="mb-3">
                                                            <label class="form-label text-white small fw-bold">Revocation Reason</label>
                                                            <textarea name="reason" rows="2" class="form-control text-white bg-dark border-secondary rounded-3" required placeholder="Specify why this license is being revoked..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-secondary">
                                                        <button type="button" class="btn btn-outline-light rounded-pill" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger rounded-pill px-4">Confirm Revocation</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-white-50">No enrollment agent licenses found matching criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $agents->links() }}
        </div>
    </div>
</div>
@endsection
