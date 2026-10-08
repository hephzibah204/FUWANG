@extends('layouts.nexus')

@section('title', 'Agent Details: ' . $agent->full_name . ' | Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.agents.index') }}" class="text-white-50 text-xs text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1"></i> Agent Directory
                </a>
                <span class="text-white-50 text-xs">/</span>
                <span class="text-emerald-400 text-xs font-semibold">Dossier #{{ $agent->id }}</span>
            </div>
            <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-id-card text-primary me-2"></i>Agent Application Dossier</h3>
            <p class="text-white-50 mb-0">Review identity proof, KYC documents, manage accreditation, and adjust verified enrollment metrics.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-info rounded-pill btn-sm fw-bold px-3" data-toggle="modal" data-bs-toggle="modal" data-target="#directNotificationModal" data-bs-target="#directNotificationModal">
                <i class="fa-solid fa-envelope me-1"></i>Direct Message
            </button>
            <a href="{{ route('admin.agents.edit', $agent->id) }}" class="btn btn-primary rounded-pill btn-sm fw-bold px-3">
                <i class="fa-solid fa-pen-to-square me-1"></i>Edit Profile & Hardware
            </a>
            <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-light rounded-pill btn-sm px-3">Back to Directory</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-3 mb-4 p-3 d-flex align-items-center justify-content-between" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0; border: 1px solid rgba(34, 197, 94, 0.3) !important;">
            <div><i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}</div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 rounded-3 mb-4 p-3 d-flex align-items-center justify-content-between" style="background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3) !important;">
            <div><i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}</div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Main Details Column -->
        <div class="col-lg-8">
            <!-- Profile Identity Card -->
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 border-bottom border-secondary pb-3 mb-3">
                    <div class="d-flex align-items-center gap-3">
                        @if($agent->picture_path)
                            <img src="{{ asset('storage/' . $agent->picture_path) }}" alt="Agent Photo" class="rounded-circle border border-primary" style="width: 64px; height: 64px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold fs-3" style="width: 64px; height: 64px;">
                                {{ substr($agent->full_name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <h4 class="text-white fw-bold mb-1">{{ $agent->full_name }}</h4>
                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                <span class="badge bg-info text-dark font-monospace text-uppercase text-2xs">{{ $agent->agent_type }} Agent</span>
                                @if($agent->company_agent_code)
                                    <span class="badge bg-warning text-dark font-monospace text-2xs">Code: {{ $agent->company_agent_code }}</span>
                                @endif
                                @if($agent->is_mva_of_month)
                                    <span class="badge bg-warning text-dark font-weight-bold text-2xs"><i class="fa-solid fa-crown me-1"></i>MVA of Month</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @if($agent->isLicensePaid())
                            <span class="badge bg-success px-3 py-2 rounded-pill fs-6"><i class="fa-solid fa-certificate me-1"></i>License Paid</span>
                        @elseif($agent->isLicensePendingReview())
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fs-6"><i class="fa-solid fa-clock me-1"></i>License Review</span>
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
                        <span class="fs-6">{{ $agent->phone_number }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-white-50 small d-block">State of Station</strong>
                        <span class="fs-6">{{ $agent->state ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-white-50 small d-block">Machine IMEI Number</strong>
                        <code class="text-warning fs-6">{{ $agent->machine_imei ?? 'Not Configured' }}</code>
                        @if($agent->has_machine)
                            <span class="badge bg-success ms-1 text-2xs">Has Machine</span>
                        @else
                            <span class="badge bg-secondary ms-1 text-2xs">Needs Machine</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong class="text-white-50 small d-block">NIN Number (11 Digits)</strong>
                        <span class="text-info font-monospace fs-6">{{ $agent->nin }}</span>
                        @if($agent->nin_server_status === 'verified')
                            <span class="badge bg-success ms-2 text-2xs"><i class="fa-solid fa-check me-1"></i>NIMC Verified</span>
                        @else
                            <span class="badge bg-warning text-dark ms-2 text-2xs"><i class="fa-solid fa-clock me-1"></i>Pending Re-Check</span>
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
                        <p class="mb-0 text-white-50">{{ $agent->residential_address ?? 'Not provided' }}</p>
                    </div>
                    <div class="col-md-12">
                        <strong class="text-white-50 small d-block">Office / Station Address</strong>
                        <p class="mb-0 text-white-50">{{ $agent->office_address ?? 'Not provided' }}</p>
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

            <!-- Enrollment Performance & Production Card (Admin can update) -->
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(255,255,255,0.02) 100%); border: 1px solid rgba(16, 185, 129, 0.25) !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <h5 class="text-white fw-bold mb-1"><i class="fa-solid fa-fingerprint text-emerald-400 me-2"></i>NIN Enrollment Performance & Production</h5>
                        <p class="text-white-50 text-xs mb-0">Record and monitor verified citizen captures, monthly quota delivery, and performance standings.</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" data-toggle="modal" data-bs-toggle="modal" data-target="#updateEnrollmentsModal" data-bs-target="#updateEnrollmentsModal">
                        <i class="fa-solid fa-pen-to-square me-1"></i>Update Capture Counts
                    </button>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-dark border border-secondary border-opacity-50">
                            <span class="text-white-50 text-2xs text-uppercase font-weight-bold d-block mb-1">Total Successful Enrollments (All-Time)</span>
                            <div class="d-flex align-items-baseline gap-2">
                                <span class="fs-2 text-success fw-bold">{{ number_format($agent->total_enrollments) }}</span>
                                <span class="text-white-50 text-xs">citizens captured</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-dark border border-secondary border-opacity-50">
                            <span class="text-white-50 text-2xs text-uppercase font-weight-bold d-block mb-1">Current Month Captures</span>
                            <div class="d-flex align-items-baseline gap-2">
                                <span class="fs-2 text-info fw-bold">{{ number_format($agent->monthly_enrollments) }}</span>
                                <span class="text-white-50 text-xs">this month</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KYC Uploaded Documents Review Cards (Upgraded Glassmorphism) -->
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-file-shield text-warning me-2"></i>Uploaded Verification Documents</h5>

                <div class="row g-3">
                    <!-- Utility Bill -->
                    <div class="col-md-4">
                        <div class="card border-0 p-3 rounded-3 text-center h-100" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08) !important;">
                            <strong class="text-white d-block mb-2 text-xs text-uppercase tracking-wider">Utility Bill</strong>
                            @if($agent->utility_bill_path)
                                @php
                                    $isImg = preg_match('/\.(jpg|jpeg|png|webp)$/i', $agent->utility_bill_path);
                                    $docUrl = asset('storage/' . $agent->utility_bill_path);
                                @endphp
                                @if($isImg)
                                    <div class="mb-2 rounded overflow-hidden" style="height: 90px; background: #000;">
                                        <img src="{{ $docUrl }}" class="w-100 h-100 object-fit-cover" alt="Utility Bill" role="button" onclick="openLightbox('{{ $docUrl }}', 'Utility Bill')">
                                    </div>
                                @else
                                    <div class="mb-2 py-3 bg-dark rounded text-info">
                                        <i class="fa-solid fa-file-pdf fa-2x"></i>
                                    </div>
                                @endif
                                <div class="d-flex gap-1 justify-content-center">
                                    @if($isImg)
                                        <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-2.5 py-1 text-2xs" onclick="openLightbox('{{ $docUrl }}', 'Utility Bill')">
                                            <i class="fa-solid fa-eye me-1"></i>Preview
                                        </button>
                                    @endif
                                    <a href="{{ $docUrl }}" target="_blank" class="btn btn-xs btn-outline-light rounded-pill px-2.5 py-1 text-2xs">
                                        <i class="fa-solid fa-external-link me-1"></i>Open
                                    </a>
                                </div>
                            @else
                                <div class="py-4 text-white-50 small">
                                    <i class="fa-solid fa-circle-exclamation text-danger mb-1 d-block"></i>
                                    Not Uploaded
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Passport Picture -->
                    <div class="col-md-4">
                        <div class="card border-0 p-3 rounded-3 text-center h-100" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08) !important;">
                            <strong class="text-white d-block mb-2 text-xs text-uppercase tracking-wider">Passport Photo</strong>
                            @if($agent->picture_path)
                                @php
                                    $docUrl = asset('storage/' . $agent->picture_path);
                                @endphp
                                <div class="mb-2 rounded overflow-hidden" style="height: 90px; background: #000;">
                                    <img src="{{ $docUrl }}" class="w-100 h-100 object-fit-cover" alt="Passport Photo" role="button" onclick="openLightbox('{{ $docUrl }}', 'Passport Photo')">
                                </div>
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-2.5 py-1 text-2xs" onclick="openLightbox('{{ $docUrl }}', 'Passport Photo')">
                                        <i class="fa-solid fa-eye me-1"></i>Preview
                                    </button>
                                    <a href="{{ $docUrl }}" target="_blank" class="btn btn-xs btn-outline-light rounded-pill px-2.5 py-1 text-2xs">
                                        <i class="fa-solid fa-external-link me-1"></i>Open
                                    </a>
                                </div>
                            @else
                                <div class="py-4 text-white-50 small">
                                    <i class="fa-solid fa-circle-exclamation text-danger mb-1 d-block"></i>
                                    Not Uploaded
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- CAC Business Doc -->
                    <div class="col-md-4">
                        <div class="card border-0 p-3 rounded-3 text-center h-100" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08) !important;">
                            <strong class="text-white d-block mb-2 text-xs text-uppercase tracking-wider">CAC Business Doc</strong>
                            @if($agent->business_registration_doc_path)
                                @php
                                    $isImg = preg_match('/\.(jpg|jpeg|png|webp)$/i', $agent->business_registration_doc_path);
                                    $docUrl = asset('storage/' . $agent->business_registration_doc_path);
                                @endphp
                                @if($isImg)
                                    <div class="mb-2 rounded overflow-hidden" style="height: 90px; background: #000;">
                                        <img src="{{ $docUrl }}" class="w-100 h-100 object-fit-cover" alt="CAC Doc" role="button" onclick="openLightbox('{{ $docUrl }}', 'CAC Business Document')">
                                    </div>
                                @else
                                    <div class="mb-2 py-3 bg-dark rounded text-warning">
                                        <i class="fa-solid fa-file-contract fa-2x"></i>
                                    </div>
                                @endif
                                <div class="d-flex gap-1 justify-content-center">
                                    @if($isImg)
                                        <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-2.5 py-1 text-2xs" onclick="openLightbox('{{ $docUrl }}', 'CAC Business Document')">
                                            <i class="fa-solid fa-eye me-1"></i>Preview
                                        </button>
                                    @endif
                                    <a href="{{ $docUrl }}" target="_blank" class="btn btn-xs btn-outline-light rounded-pill px-2.5 py-1 text-2xs">
                                        <i class="fa-solid fa-external-link me-1"></i>Open
                                    </a>
                                </div>
                            @else
                                <div class="py-4 text-white-50 small">
                                    <i class="fa-solid fa-minus mb-1 d-block opacity-50"></i>
                                    N/A (Individual)
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">
            <!-- Approval Actions Card (One-Click Actions) -->
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-sliders text-primary me-2"></i>Approval & Access Controls</h5>

                <div class="d-flex flex-column gap-2 mb-3">
                    @if($agent->isPending() || $agent->isRejected() || $agent->isSuspended())
                        <form action="{{ route('admin.agents.approve', $agent->id) }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-success rounded-pill w-100 py-2 fw-bold">
                                <i class="fa-solid fa-check-circle me-2"></i>Approve Agent Application
                            </button>
                        </form>
                    @endif

                    @if($agent->isApproved())
                        <form action="{{ route('admin.agents.suspend', $agent->id) }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-warning text-dark rounded-pill w-100 py-2 fw-bold">
                                <i class="fa-solid fa-ban me-2"></i>Suspend Agent
                            </button>
                        </form>
                    @endif

                    @if(!$agent->isRejected())
                        <button class="btn btn-outline-danger rounded-pill w-100 py-2 fw-bold" data-toggle="collapse" data-bs-toggle="collapse" data-target="#rejectForm" data-bs-target="#rejectForm">
                            <i class="fa-solid fa-times-circle me-2"></i>Reject Application
                        </button>

                        <div class="collapse mt-2" id="rejectForm">
                            <form action="{{ route('admin.agents.reject', $agent->id) }}" method="POST" class="card card-body bg-dark border-danger rounded-3 p-3">
                                @csrf
                                <label class="form-label text-white small fw-bold">Rejection Reason</label>
                                <textarea name="rejection_reason" rows="3" class="form-control form-control-sm mb-3 bg-dark text-white border-secondary" required placeholder="Specify why the agent application was rejected..."></textarea>
                                <button type="submit" class="btn btn-danger btn-sm rounded-pill w-100">Confirm Rejection</button>
                            </form>
                        </div>
                    @endif

                    <button type="button" class="btn btn-outline-info rounded-pill w-100 py-2 fw-bold" data-toggle="modal" data-bs-toggle="modal" data-target="#directNotificationModal" data-bs-target="#directNotificationModal">
                        <i class="fa-solid fa-paper-plane me-2"></i>Dispatch Direct Message
                    </button>
                </div>

                <div class="border-top border-secondary border-opacity-25 pt-3">
                    <form action="{{ route('admin.agents.destroy', $agent->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this Enrollment Agent profile? This will remove their agent credentials and hardware record, but their main user account, wallet balance, and other profiles will remain completely intact.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-secondary text-white-50 rounded-pill w-100 py-1.5 small fw-bold">
                            <i class="fa-solid fa-trash me-2"></i>Delete Agent Profile
                        </button>
                    </form>
                </div>
            </div>

            <!-- Linked User Account Card -->
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-user-shield text-info me-2"></i>Linked User Account</h5>
                @if($agent->user)
                    <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom border-secondary border-opacity-25">
                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 48px; height: 48px; flex-shrink: 0;">
                            {{ substr($agent->user->name ?? $agent->user->username ?? 'U', 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <h6 class="text-white fw-bold mb-0 text-truncate">{{ $agent->user->name ?? $agent->user->username }}</h6>
                            <span class="text-white-50 text-xs text-truncate d-block">{{ $agent->user->email }}</span>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-2 text-xs text-white-50 mb-1">
                        <div class="d-flex justify-content-between">
                            <span>User ID:</span>
                            <strong class="text-white">#{{ $agent->user->id }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Phone:</span>
                            <strong class="text-white">{{ $agent->user->phone ?? 'N/A' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Registered On:</span>
                            <strong class="text-white">{{ $agent->user->created_at?->format('d M Y') ?? 'N/A' }}</strong>
                        </div>
                        @if(isset($agent->user->wallet_balance))
                        <div class="d-flex justify-content-between">
                            <span>Wallet Balance:</span>
                            <strong class="text-emerald-400">₦{{ number_format((float)$agent->user->wallet_balance, 2) }}</strong>
                        </div>
                        @endif
                    </div>
                @else
                    <p class="text-white-50 small mb-0">No linked user account attached to this agent record.</p>
                @endif
            </div>

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

                <div class="d-flex flex-column gap-2 text-white small mb-3">
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
        </div>
    </div>

    <!-- Audit & Activity Lifecycle Timeline -->
    <div class="card border-0 rounded-4 p-4 mt-2 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-timeline text-primary me-2"></i>Agent Lifecycle & Audit Timeline</h5>

        <div class="row g-3">
            <div class="col-md-3">
                <div class="p-3 bg-dark rounded-3 border border-secondary border-opacity-30 h-100">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary text-white rounded-circle p-2"><i class="fa-solid fa-user-plus"></i></span>
                        <strong class="text-white text-xs">Application Created</strong>
                    </div>
                    <span class="text-white-50 text-2xs d-block">{{ $agent->created_at ? $agent->created_at->format('d M Y, h:i A') : 'N/A' }}</span>
                    <small class="text-white-50 text-2xs">{{ $agent->created_at ? $agent->created_at->diffForHumans() : '' }}</small>
                </div>
            </div>

            <div class="col-md-3">
                <div class="p-3 bg-dark rounded-3 border border-secondary border-opacity-30 h-100">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge {{ $agent->accepted_terms ? 'bg-success' : 'bg-warning text-dark' }} rounded-circle p-2"><i class="fa-solid fa-file-signature"></i></span>
                        <strong class="text-white text-xs">Terms & Compliance</strong>
                    </div>
                    @if($agent->accepted_terms)
                        <span class="text-success text-2xs d-block">Signed & Accepted</span>
                        <small class="text-white-50 text-2xs">{{ $agent->terms_accepted_at?->format('d M Y, h:i A') }}</small>
                    @else
                        <span class="text-warning text-2xs d-block">Awaiting Signature</span>
                    @endif
                </div>
            </div>

            <div class="col-md-3">
                <div class="p-3 bg-dark rounded-3 border border-secondary border-opacity-30 h-100">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge {{ $agent->isApproved() ? 'bg-success' : ($agent->isPending() ? 'bg-warning text-dark' : 'bg-danger') }} rounded-circle p-2"><i class="fa-solid fa-stamp"></i></span>
                        <strong class="text-white text-xs">Approval Decision</strong>
                    </div>
                    @if($agent->isApproved())
                        <span class="text-success text-2xs d-block">Approved Status</span>
                        <small class="text-white-50 text-2xs">{{ $agent->approved_at ? $agent->approved_at->format('d M Y, h:i A') : 'Active' }}</small>
                    @else
                        <span class="text-white-50 text-2xs d-block">{{ ucfirst($agent->status) }}</span>
                    @endif
                </div>
            </div>

            <div class="col-md-3">
                <div class="p-3 bg-dark rounded-3 border border-secondary border-opacity-30 h-100">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge {{ $agent->isLicensePaid() ? 'bg-success' : 'bg-secondary' }} rounded-circle p-2"><i class="fa-solid fa-certificate"></i></span>
                        <strong class="text-white text-xs">License Status</strong>
                    </div>
                    @if($agent->isLicensePaid())
                        <span class="text-success text-2xs d-block">Accredited & Paid</span>
                        <small class="text-white-50 text-2xs">{{ $agent->license_paid_at ? $agent->license_paid_at->format('d M Y') : 'Active' }}</small>
                    @else
                        <span class="text-white-50 text-2xs d-block">{{ ucfirst($agent->license_status ?? 'unpaid') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Update Enrollments Count Modal -->
    <div class="modal fade text-start" id="updateEnrollmentsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white rounded-4 border border-emerald-500">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-chart-line text-emerald-400 me-2"></i>Update Agent Enrollments</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" style="background:transparent; border:0; font-size:1.5rem;">&times;</button>
                </div>
                <form action="{{ route('admin.agents.update_enrollments', $agent->id) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="text-white-50 small mb-3">Adjust the verified enrollment counts for <strong>{{ $agent->full_name }}</strong>.</p>
                        <div class="mb-3">
                            <label class="form-label text-white small fw-bold">Total Successful Enrollments (All-Time)</label>
                            <input type="number" min="0" name="total_enrollments" value="{{ $agent->total_enrollments }}" class="form-control text-white bg-dark border-secondary rounded-3" required>
                            <small class="text-white-50 text-2xs">Cumulative total of citizen NIMC captures completed.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white small fw-bold">Current Month Enrollments</label>
                            <input type="number" min="0" name="monthly_enrollments" value="{{ $agent->monthly_enrollments }}" class="form-control text-white bg-dark border-secondary rounded-3" required>
                            <small class="text-white-50 text-2xs">Used for monthly performance & MVA leaderboard calculations.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold"><i class="fa-solid fa-save me-1"></i>Save Production Counts</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Direct Notification Modal -->
    <div class="modal fade text-start" id="directNotificationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white rounded-4 border border-info">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-envelope-open-text text-info me-2"></i>Direct Message to {{ $agent->full_name }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" style="background:transparent; border:0; font-size:1.5rem;">&times;</button>
                </div>
                <form action="{{ route('admin.agents.notify_direct', $agent->id) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-white small fw-bold">Subject / Title</label>
                            <input type="text" name="subject" class="form-control text-white bg-dark border-secondary rounded-3" required placeholder="e.g. Hardware Configuration & Station Check">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white small fw-bold">Message Content</label>
                            <textarea name="message" rows="4" class="form-control text-white bg-dark border-secondary rounded-3" required placeholder="Write your urgent message or notice to this agent..."></textarea>
                        </div>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="send_email" value="1" class="form-check-input" id="sendDirectEmail" checked>
                            <label class="form-check-label text-white small" for="sendDirectEmail">Also dispatch as direct email (to {{ $agent->user?->email ?? ($agent->meta['email'] ?? 'agent email') }})</label>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="fa-solid fa-paper-plane me-1"></i>Send Message</button>
                    </div>
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

    <!-- Document Lightbox Modal -->
    <div class="modal fade" id="docLightboxModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark text-white rounded-4 border border-white-10">
                <div class="modal-header border-secondary">
                    <h6 class="modal-title fw-bold text-white" id="docLightboxTitle">Document Preview</h6>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" style="background:transparent; border:0; font-size:1.5rem;">&times;</button>
                </div>
                <div class="modal-body text-center p-3">
                    <img src="" id="docLightboxImg" class="img-fluid rounded" style="max-height: 70vh; object-fit: contain;" alt="Document">
                </div>
                <div class="modal-footer border-secondary">
                    <a href="" id="docLightboxDownload" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3">
                        <i class="fa-solid fa-arrow-down me-1"></i>Open Full Size
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openLightbox(url, title) {
    document.getElementById('docLightboxImg').src = url;
    document.getElementById('docLightboxTitle').textContent = title;
    document.getElementById('docLightboxDownload').href = url;
    
    // Support Bootstrap 5 & fallback
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        var modal = new bootstrap.Modal(document.getElementById('docLightboxModal'));
        modal.show();
    } else if (window.jQuery && jQuery.fn.modal) {
        $('#docLightboxModal').modal('show');
    }
}
</script>
@endsection
