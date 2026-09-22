@extends('layouts.nexus')

@section('title', 'Agent Details: ' . $agent->full_name . ' | Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-id-card text-primary me-2"></i>Agent Application Details</h3>
            <p class="text-white-50 mb-0">Review identity proof, uploaded KYC documents, NIMC compliance, and grant agency access.</p>
        </div>
        <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-light rounded-pill btn-sm">Back to Agent List</a>
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
                    <div>
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
            </div>
        </div>
    </div>
</div>
@endsection
