@extends('layouts.nexus')

@section('title', 'Agent Details: ' . $agent->full_name . ' | Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-id-card text-primary me-2"></i>Agent Application Details</h3>
            <p class="text-white-50 mb-0">Review identity proof, uploaded KYC documents, NIMC compliance, and grant agency access.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.agents.edit', $agent->id) }}" class="btn btn-primary rounded-pill btn-sm fw-bold">
                <i class="fa-solid fa-pen-to-square me-1"></i>Edit Profile & Hardware
            </a>
            <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-light rounded-pill btn-sm">Back to Agent List</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-3 mb-4 p-3" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0;">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="row g-4">
        <!-- Main Details Card -->
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between border-bottom border-secondary pb-3 mb-3">
                    <div class="d-flex align-items-center gap-3">
                        @if($agent->picture_path)
                            <img src="{{ asset('storage/' . $agent->picture_path) }}" alt="Agent Photo" class="rounded-circle border border-primary" style="width: 60px; height: 60px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold fs-4" style="width: 60px; height: 60px;">
                                {{ substr($agent->full_name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <h4 class="text-white fw-bold mb-0">{{ $agent->full_name }}</h4>
                            <span class="badge bg-info text-dark font-monospace text-uppercase">{{ $agent->agent_type }} Agent</span>
                            @if($agent->company_agent_code)
                                <span class="badge bg-warning text-dark font-monospace">Code: {{ $agent->company_agent_code }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($agent->isLicensePaid())
                            <span class="badge bg-success px-3 py-2 rounded-pill fs-6"><i class="fa-solid fa-certificate me-1"></i>License Paid</span>
                        @elseif($agent->isLicensePendingReview())
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fs-6"><i class="fa-solid fa-clock me-1"></i>License Proof Review</span>
                        @else
                            <span class="badge bg-secondary px-3 py-2 rounded-pill fs-6"><i class="fa-solid fa-xmark me-1"></i>License Unpaid</span>
                        @endif

                        @if($agent->isApproved())
                            <span class="badge bg-success px-3 py-2 rounded-pill fs-6">Approved</span>
                        @elseif($agent->isPending())
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fs-6">Pending Review</span>
                        @elseif($agent->isRejected())
                            <span class="badge bg-danger px-3 py-2 rounded-pill fs-6">Rejected</span>
                        @elseif($agent->isSuspended())
                            <span class="badge bg-secondary px-3 py-2 rounded-pill fs-6">Suspended</span>
                        @endif
                    </div>
                </div>

                <div class="row g-3 text-white mb-4">
                    <div class="col-md-6">
                        <strong class="text-white-50 small d-block">Phone Number</strong>
                        <span>{{ $agent->phone_number }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-white-50 small d-block">State of Station</strong>
                        <span>{{ $agent->state ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-white-50 small d-block">Machine IMEI Number</strong>
                        <code class="text-warning fs-6">{{ $agent->machine_imei }}</code>
                        @if($agent->has_machine)
                            <span class="badge bg-success ms-1">Has Machine</span>
                        @else
                            <span class="badge bg-secondary ms-1">Needs Machine</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong class="text-white-50 small d-block">NIN Number (11 Digits)</strong>
                        <span class="text-info font-monospace fs-6">{{ $agent->nin }}</span>
                        @if($agent->nin_server_status === 'verified')
                            <span class="badge bg-success ms-2"><i class="fa-solid fa-check me-1"></i>NIMC Verified</span>
                        @else
                            <span class="badge bg-warning text-dark ms-2"><i class="fa-solid fa-clock me-1"></i>Pending Gateway Re-Check</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong class="text-white-50 small d-block">BVN Number (11 Digits)</strong>
                        <span class="font-monospace fs-6">{{ $agent->bvn }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-white-50 small d-block">Business CAC Number</strong>
                        <span>{{ $agent->business_registration_number ?? 'Individual Agent' }}</span>
                    </div>
                    <div class="col-md-12">
                        <strong class="text-white-50 small d-block">Residential Address</strong>
                        <p class="mb-0 text-white-50">{{ $agent->residential_address }}</p>
                    </div>
                    <div class="col-md-12">
                        <strong class="text-white-50 small d-block">Office / Station Address</strong>
                        <p class="mb-0 text-white-50">{{ $agent->office_address }}</p>
                    </div>
                </div>

                <!-- NIMC Compliance Agreement Audit -->
                <div class="border-top border-secondary pt-3 mt-3">
                    <h6 class="text-white fw-bold mb-2"><i class="fa-solid fa-gavel text-success me-2"></i>NIMC Code of Conduct Audit</h6>
                    @if($agent->accepted_terms)
                        <div class="alert alert-success border-0 p-3 rounded-3 mb-0" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0;">
                            <i class="fa-solid fa-check-double me-2"></i>Signed all NIMC & Company Compliance Terms (No non-appearance, anti-fraud, NDPR privacy, terminal lock) on <strong>{{ $agent->terms_accepted_at?->format('d M Y, h:i A') }}</strong>.
                        </div>
                    @else
                        <div class="alert alert-warning border-0 p-3 rounded-3 mb-0" style="background: rgba(234, 179, 8, 0.15); color: #fef08a;">
                            <i class="fa-solid fa-clock me-2"></i>Code of conduct agreement pending agent completion.
                        </div>
                    @endif
                </div>
            </div>

            <!-- KYC Uploaded Documents Review Cards -->
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-file-shield text-warning me-2"></i>Uploaded Verification Documents</h5>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card border-secondary bg-dark p-3 rounded-3 text-center h-100">
                            <strong class="text-white d-block mb-2">Utility Bill</strong>
                            @if($agent->utility_bill_path)
                                <a href="{{ asset('storage/' . $agent->utility_bill_path) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill">
                                    <i class="fa-solid fa-eye me-1"></i>View Utility Bill
                                </a>
                            @else
                                <span class="text-danger small">Not Uploaded</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-secondary bg-dark p-3 rounded-3 text-center h-100">
                            <strong class="text-white d-block mb-2">Passport Picture</strong>
                            @if($agent->picture_path)
                                <a href="{{ asset('storage/' . $agent->picture_path) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill">
                                    <i class="fa-solid fa-eye me-1"></i>View Passport Photo
                                </a>
                            @else
                                <span class="text-danger small">Not Uploaded</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-secondary bg-dark p-3 rounded-3 text-center h-100">
                            <strong class="text-white d-block mb-2">CAC Business Doc</strong>
                            @if($agent->business_registration_doc_path)
                                <a href="{{ asset('storage/' . $agent->business_registration_doc_path) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill">
                                    <i class="fa-solid fa-eye me-1"></i>View CAC Certificate
                                </a>
                            @else
                                <span class="text-white-50 small">N/A</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="col-lg-4">
            <!-- Station License Accreditation Card -->
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="text-white fw-bold mb-0"><i class="fa-solid fa-certificate text-warning me-2"></i>Station License</h5>
                    @if($agent->isLicensePaid())
                        <span class="badge bg-success px-2 py-1 rounded-pill"><i class="fa-solid fa-check me-1"></i>Paid</span>
                    @elseif($agent->isLicensePendingReview())
                        <span class="badge bg-warning text-dark px-2 py-1 rounded-pill"><i class="fa-solid fa-clock me-1"></i>Proof Review</span>
                    @else
                        <span class="badge bg-secondary px-2 py-1 rounded-pill"><i class="fa-solid fa-xmark me-1"></i>Unpaid</span>
                    @endif
                </div>

                <div class="space-y-2 text-white small mb-3">
                    <div>
                        <span class="text-white-50">Accreditation Fee:</span>
                        <strong class="text-white ms-1">₦{{ number_format((float)($agent->license_fee_paid ?? \App\Models\EnrollmentAgent::getEffectiveLicenseFee()), 2) }}</strong>
                    </div>
                    <div>
                        <span class="text-white-50">Payment Method:</span>
                        <span class="text-warning fw-bold ms-1">
                            @if($agent->license_payment_method === 'legacy_pre_platform')
                                Paid Before Website Launch (Legacy)
                            @elseif($agent->license_payment_method === 'paystack')
                                Paystack Automated
                            @elseif($agent->license_payment_method === 'wallet')
                                Wallet Balance
                            @elseif($agent->license_payment_method === 'offline_proof')
                                Offline Bank Transfer
                            @elseif($agent->license_payment_method === 'admin_manual')
                                Admin Manual Verification
                            @elseif($agent->license_payment_method === 'waived')
                                Waived Exemption
                            @else
                                Not Paid Yet
                            @endif
                        </span>
                    </div>
                    @if($agent->license_payment_reference)
                        <div>
                            <span class="text-white-50">Reference:</span>
                            <code class="text-info font-monospace ms-1">{{ $agent->license_payment_reference }}</code>
                        </div>
                    @endif
                    @if($agent->license_paid_at)
                        <div>
                            <span class="text-white-50">Paid On:</span>
                            <span class="text-white ms-1">{{ $agent->license_paid_at->format('d M Y, h:i A') }}</span>
                        </div>
                    @endif
                    @if($agent->licenseVerifiedBy)
                        <div>
                            <span class="text-white-50">Verified By:</span>
                            <span class="text-white ms-1">{{ $agent->licenseVerifiedBy->name }}</span>
                        </div>
                    @endif
                    @if($agent->license_rejection_reason)
                        <div class="alert alert-danger p-2 rounded-2 mt-2 mb-0">
                            <small class="d-block fw-bold"><i class="fa-solid fa-triangle-exclamation me-1"></i>Proof Rejected Reason:</small>
                            <small>{{ $agent->license_rejection_reason }}</small>
                        </div>
                    @endif
                    @if($agent->license_admin_notes)
                        <div class="mt-2 text-white-50 small bg-dark p-2 rounded-2 border border-secondary">
                            <span class="d-block text-white fw-bold">Admin / Claim Notes:</span>
                            {{ $agent->license_admin_notes }}
                        </div>
                    @endif
                </div>

                <!-- Offline Proof Preview if uploaded -->
                @if($agent->license_proof_path)
                    <div class="p-3 bg-dark rounded-3 border border-secondary mb-3">
                        <strong class="text-white small d-block mb-2"><i class="fa-solid fa-file-invoice-dollar text-warning me-1"></i>Submitted Payment Proof:</strong>
                        <div class="d-flex align-items-center justify-content-between">
                            <a href="{{ asset('storage/' . $agent->license_proof_path) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3">
                                <i class="fa-solid fa-eye me-1"></i>View Proof Document
                            </a>
                            @if($agent->isLicensePendingReview())
                                <span class="badge bg-warning text-dark">Needs Review</span>
                            @endif
                        </div>
                        @if($agent->license_proof_meta)
                            @php $meta = (array)$agent->license_proof_meta; @endphp
                            <div class="mt-2 text-white-50 text-2xs">
                                <div>Bank: <strong>{{ $meta['bank_name'] ?? 'N/A' }}</strong></div>
                                <div>Amount: <strong>₦{{ number_format((float)($meta['amount_paid'] ?? 0), 2) }}</strong></div>
                                <div>Paid Date: <strong>{{ $meta['payment_date'] ?? 'N/A' }}</strong></div>
                                <div>Ref: <strong>{{ $meta['transaction_reference'] ?? 'N/A' }}</strong></div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Review Actions if Pending Proof -->
                @if($agent->isLicensePendingReview())
                    <div class="d-flex gap-2 mb-3">
                        <form action="{{ route('admin.agents.licenses.approve_proof', $agent->id) }}" method="POST" class="flex-grow-1" onsubmit="return confirm('Approve offline payment proof and mark license as PAID?');">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm rounded-pill w-100 fw-bold">
                                <i class="fa-solid fa-check me-1"></i>Approve Proof
                            </button>
                        </form>
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" data-toggle="collapse" data-bs-toggle="collapse" data-target="#rejectProofForm" data-bs-target="#rejectProofForm">
                            <i class="fa-solid fa-times me-1"></i>Reject
                        </button>
                    </div>

                    <div class="collapse mb-3" id="rejectProofForm">
                        <form action="{{ route('admin.agents.licenses.reject_proof', $agent->id) }}" method="POST" class="bg-dark p-3 rounded-3 border border-danger">
                            @csrf
                            <label class="form-label text-white small fw-bold">Proof Rejection Reason</label>
                            <textarea name="rejection_reason" rows="2" class="form-control form-control-sm text-white bg-dark border-secondary rounded-2 mb-2" required placeholder="Why is this proof rejected?"></textarea>
                            <button type="submit" class="btn btn-danger btn-sm rounded-pill w-100">Confirm Reject</button>
                        </form>
                    </div>
                @endif

                <!-- Modal Trigger: Mark License as Paid -->
                <button type="button" class="btn btn-outline-warning rounded-pill w-100 py-2 fw-bold mb-2" data-toggle="modal" data-bs-toggle="modal" data-target="#markLicensePaidModal" data-bs-target="#markLicensePaidModal">
                    <i class="fa-solid fa-pen-nib me-2"></i>{{ $agent->isLicensePaid() ? 'Edit License / Accreditation' : 'Mark License as Paid' }}
                </button>

                @if($agent->isLicensePaid())
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill w-100 py-1 text-2xs" data-toggle="modal" data-bs-toggle="modal" data-target="#revokeLicenseModal" data-bs-target="#revokeLicenseModal">
                        <i class="fa-solid fa-ban me-1"></i>Revoke License
                    </button>
                @endif
            </div>

            <!-- Approval Actions Card -->
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-sliders text-primary me-2"></i>Approval Actions</h5>

                @if($agent->isPending() || $agent->isRejected() || $agent->isSuspended())
                    <form action="{{ route('admin.agents.approve', $agent->id) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-success rounded-pill w-100 py-2 fw-bold">
                            <i class="fa-solid fa-check-circle me-2"></i>Approve Agent Application
                        </button>
                    </form>
                @endif

                @if($agent->isApproved())
                    <form action="{{ route('admin.agents.suspend', $agent->id) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-warning text-dark rounded-pill w-100 py-2 fw-bold">
                            <i class="fa-solid fa-ban me-2"></i>Suspend Agent
                        </button>
                    </form>
                @endif

                @if(!$agent->isRejected())
                    <button class="btn btn-outline-danger rounded-pill w-100 py-2 fw-bold mb-3" data-toggle="collapse" data-bs-toggle="collapse" data-target="#rejectForm" data-bs-target="#rejectForm">
                        <i class="fa-solid fa-times-circle me-2"></i>Reject Application
                    </button>

                    <div class="collapse mb-3" id="rejectForm">
                        <form action="{{ route('admin.agents.reject', $agent->id) }}" method="POST" class="card card-body bg-dark border-danger rounded-3">
                            @csrf
                            <label class="form-label text-white small fw-bold">Rejection Reason</label>
                            <textarea name="rejection_reason" rows="3" class="form-control form-control-sm mb-3" required placeholder="Specify why the agent application was rejected..."></textarea>
                            <button type="submit" class="btn btn-danger btn-sm rounded-pill">Submit Rejection</button>
                        </form>
                    </div>
                @endif

                <form action="{{ route('admin.agents.destroy', $agent->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this Enrollment Agent profile? This will remove their agent credentials and hardware record, but their main user account, wallet balance, and other profiles will remain completely intact.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-secondary text-white-50 rounded-pill w-100 py-1.5 small fw-bold">
                        <i class="fa-solid fa-trash me-2"></i>Delete Agent Profile
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Mark License Paid Modal -->
    <div class="modal fade text-start" id="markLicensePaidModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white rounded-4 border border-success">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold text-success"><i class="fa-solid fa-certificate me-2"></i>Accredit Station License</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" style="background:transparent; border:0; font-size:1.5rem;">&times;</button>
                </div>
                <form action="{{ route('admin.agents.licenses.mark_paid', $agent->id) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="text-white-50 small mb-3">Accredit or update station license for <strong>{{ $agent->full_name }}</strong>.</p>

                        <div class="mb-3">
                            <label class="form-label text-white small fw-bold">Payment Method / Origin</label>
                            <select name="payment_method" class="form-select bg-dark text-white border-secondary rounded-3" required>
                                <option value="legacy_pre_platform" {{ $agent->license_payment_method === 'legacy_pre_platform' ? 'selected' : '' }}>Paid Before Website Launch (Pre-Website Legacy)</option>
                                <option value="admin_manual" {{ $agent->license_payment_method === 'admin_manual' ? 'selected' : '' }}>Verified Offline Bank Transfer / Cash</option>
                                <option value="paystack" {{ $agent->license_payment_method === 'paystack' ? 'selected' : '' }}>Direct Paystack Verified</option>
                                <option value="wallet" {{ $agent->license_payment_method === 'wallet' ? 'selected' : '' }}>Wallet Balance</option>
                                <option value="waived" {{ $agent->license_payment_method === 'waived' ? 'selected' : '' }}>Special Exemption / Waived Fee</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white small fw-bold">Amount Paid (₦)</label>
                            <input type="number" step="0.01" name="amount" value="{{ $agent->license_fee_paid ?? \App\Models\EnrollmentAgent::getEffectiveLicenseFee() }}" class="form-control text-white bg-dark border-secondary rounded-3">
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
                            <label class="form-label text-white small fw-bold">Admin Audit Notes</label>
                            <textarea name="admin_notes" rows="2" placeholder="e.g. Verified by management before platform launch" class="form-control text-white bg-dark border-secondary rounded-3">{{ $agent->license_admin_notes }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4"><i class="fa-solid fa-check-circle me-1"></i>Save Accreditation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Revoke License Modal -->
    @if($agent->isLicensePaid())
        <div class="modal fade text-start" id="revokeLicenseModal" tabindex="-1" aria-hidden="true">
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
</div>
@endsection
