@extends('layouts.nexus')

@section('title', 'Edit Agent: ' . $agent->full_name . ' | Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb & Header -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.agents.overview') }}" class="text-white-50 text-xs text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1"></i> Command Center
                </a>
                <span class="text-white-50 text-xs">/</span>
                <a href="{{ route('admin.agents.index') }}" class="text-white-50 text-xs text-decoration-none">Directory</a>
                <span class="text-white-50 text-xs">/</span>
                <span class="text-emerald-400 text-xs fw-semibold">Edit Agent</span>
            </div>
            <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-user-pen text-primary me-2"></i>Edit Agent Profile & Hardware</h3>
            <p class="text-white-50 mb-0">Modify agent attributes, hardware binding, state assignment, and status credentials.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.agents.show', $agent->id) }}" class="btn btn-outline-info rounded-pill px-3 fw-bold">
                <i class="fa-solid fa-eye me-1"></i>View Profile
            </a>
            <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-light rounded-pill px-3 fw-bold">
                Back to Directory
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 rounded-3 mb-4 p-3" style="background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3) !important;">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.agents.update', $agent->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Left Column: Personal, Location & Status -->
            <div class="col-lg-7">
                <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                    <h5 class="text-white fw-bold mb-4 pb-2 border-bottom border-secondary border-opacity-25">
                        <i class="fa-solid fa-address-card text-primary me-2"></i>Personal & Station Details
                    </h5>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-white text-xs fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" value="{{ old('full_name', $agent->full_name) }}" class="form-control bg-dark border-secondary text-white rounded-3 py-2 text-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-white text-xs fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone_number" value="{{ old('phone_number', $agent->phone_number) }}" class="form-control bg-dark border-secondary text-white rounded-3 py-2 text-sm" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-white text-xs fw-semibold">State of Station</label>
                            <input type="text" name="state" value="{{ old('state', $agent->state) }}" class="form-control bg-dark border-secondary text-white rounded-3 py-2 text-sm" placeholder="e.g. Lagos, Kano, FCT">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-white text-xs fw-semibold">Company Agent Code</label>
                            <input type="text" name="company_agent_code" value="{{ old('company_agent_code', $agent->company_agent_code) }}" class="form-control bg-dark border-secondary text-white rounded-3 py-2 text-sm font-monospace" placeholder="e.g. AGT-10294">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white text-xs fw-semibold">Office / Enrollment Center Address</label>
                        <textarea name="office_address" rows="3" class="form-control bg-dark border-secondary text-white rounded-3 py-2 text-sm">{{ old('office_address', $agent->office_address) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white text-xs fw-semibold">Residential Address</label>
                        <textarea name="residential_address" rows="3" class="form-control bg-dark border-secondary text-white rounded-3 py-2 text-sm">{{ old('residential_address', $agent->residential_address) }}</textarea>
                    </div>
                </div>

                <!-- Hardware & Terminal Assignment -->
                <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                    <h5 class="text-white fw-bold mb-4 pb-2 border-bottom border-secondary border-opacity-25">
                        <i class="fa-solid fa-laptop-code text-warning me-2"></i>Hardware & Machine Provisioning
                    </h5>

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label text-white text-xs fw-semibold">Machine IMEI Number</label>
                            <input type="text" name="machine_imei" value="{{ old('machine_imei', $agent->machine_imei) }}" class="form-control bg-dark border-secondary text-white rounded-3 py-2 text-sm font-monospace text-warning" placeholder="15-digit Terminal IMEI">
                            <small class="text-white-50 text-2xs">Biometric machines bound to this IMEI will be authenticated under this agent's license.</small>
                        </div>
                        <div class="col-md-4 d-flex align-items-center">
                            <div class="form-check form-switch pt-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="hasMachineSwitch" name="has_machine" value="1" {{ old('has_machine', $agent->has_machine) ? 'checked' : '' }}>
                                <label class="form-check-label text-white text-xs fw-semibold ms-2" for="hasMachineSwitch">
                                    Has Assigned Machine
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Status & Operational Performance -->
            <div class="col-lg-5">
                <!-- Status & Governance -->
                <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                    <h5 class="text-white fw-bold mb-4 pb-2 border-bottom border-secondary border-opacity-25">
                        <i class="fa-solid fa-shield-halved text-success me-2"></i>Status & Governance
                    </h5>

                    <div class="mb-3">
                        <label class="form-label text-white text-xs fw-semibold">Agent Operational Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select bg-dark border-secondary text-white rounded-3 py-2 text-sm">
                            <option value="approved" {{ old('status', $agent->status) === 'approved' ? 'selected' : '' }}>Approved (Active)</option>
                            <option value="pending" {{ old('status', $agent->status) === 'pending' ? 'selected' : '' }}>Pending Review</option>
                            <option value="suspended" {{ old('status', $agent->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="rejected" {{ old('status', $agent->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white text-xs fw-semibold">Rejection / Suspension Reason (if applicable)</label>
                        <textarea name="rejection_reason" rows="3" class="form-control bg-dark border-secondary text-white rounded-3 py-2 text-sm" placeholder="Provide note or justification visible to agent...">{{ old('rejection_reason', $agent->rejection_reason) }}</textarea>
                    </div>

                    <div class="p-3 rounded-3 mb-3" style="background: rgba(234, 179, 8, 0.08); border: 1px solid rgba(234, 179, 8, 0.2);">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="mvaSwitch" name="is_mva_of_month" value="1" {{ old('is_mva_of_month', $agent->is_mva_of_month) ? 'checked' : '' }}>
                            <label class="form-check-label text-warning text-xs fw-bold ms-2" for="mvaSwitch">
                                <i class="fa-solid fa-crown me-1"></i> Crown as MVA of the Month
                            </label>
                        </div>
                        <small class="text-white-50 text-2xs d-block mt-1">Displays this agent prominently on the agent portal dashboard and public spotlight.</small>
                    </div>
                </div>

                <!-- Performance Metrics Adjustment -->
                <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                    <h5 class="text-white fw-bold mb-4 pb-2 border-bottom border-secondary border-opacity-25">
                        <i class="fa-solid fa-chart-line text-info me-2"></i>Performance Adjustments
                    </h5>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label text-white text-xs fw-semibold">Monthly Enrollments</label>
                            <input type="number" name="monthly_enrollments" min="0" value="{{ old('monthly_enrollments', $agent->monthly_enrollments) }}" class="form-control bg-dark border-secondary text-white rounded-3 py-2 text-sm">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label text-white text-xs fw-semibold">All-Time Total Enrollments</label>
                            <input type="number" name="total_enrollments" min="0" value="{{ old('total_enrollments', $agent->total_enrollments) }}" class="form-control bg-dark border-secondary text-white rounded-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Save Action Button -->
                <button type="submit" class="btn btn-primary rounded-pill w-100 py-3 fw-bold shadow-lg">
                    <i class="fa-solid fa-save me-2"></i>Save Changes to Agent Profile
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
