@extends('layouts.nexus')

@section('title', 'NIN Validation Review | Admin')

@section('content')
<div class="container py-4" style="max-width: 900px;">
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <div>
            <h3 class="font-weight-bold text-white mb-0"><i class="fa-solid fa-file-shield text-primary me-2"></i>Review &amp; Resolve NIN Validation</h3>
            <p class="text-white-50 small mb-0">Inspect applicant submission, approve validated status, or reject with automatic wallet refund.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('admin.verifications.nin_validation.sync_robosttech', $request->id) }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-outline-info btn-sm">
                    <i class="fa-solid fa-arrows-rotate me-1"></i> Sync Robosttech API
                </button>
            </form>
            <a href="{{ route('admin.verifications.nin_validation.index') }}" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Requests
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success text-white bg-success border-0 mb-4 rounded-3 shadow-sm">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger text-white bg-danger border-0 mb-4 rounded-3 shadow-sm">
            <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
        </div>
    @endif

    @php 
        $d = $request->response_data ?? []; 
        $currentNin = $d['nin'] ?? ($request->identifier ?? null);
    @endphp

    <div class="card glass-card border-0 shadow rounded-4 p-4 mb-4">
        <h5 class="text-white mb-3 border-bottom border-secondary pb-2 d-flex justify-content-between align-items-center">
            <span>Request Identification</span>
            @if($request->status === 'successful')
                <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Validated</span>
            @elseif($request->status === 'failed')
                <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> Failed / Refunded</span>
            @else
                <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> Under Review</span>
            @endif
        </h5>

        <div class="row g-3 text-white-50">
            <div class="col-md-6">
                <span class="d-block small text-white-50">System Reference</span>
                <strong class="text-white"><code>{{ $request->reference_id ?? ('REF-' . $request->id) }}</code></strong>
            </div>
            <div class="col-md-6">
                <span class="d-block small text-white-50">NIN Number</span>
                <strong class="text-warning font-monospace fs-5">{{ $currentNin }}</strong>
            </div>
            <div class="col-md-6">
                <span class="d-block small text-white-50">Reason / Purpose</span>
                <strong class="text-white">{{ $d['validation_reason'] ?? ($d['category'] ?? 'NIN Validation') }}</strong>
            </div>
            <div class="col-md-6">
                <span class="d-block small text-white-50">Applicant User</span>
                <strong class="text-white">{{ $request->user->fullname ?? ($d['user_name'] ?? 'Unknown') }}</strong>
                <div class="small text-white-50">{{ $request->user->email ?? ($d['user_email'] ?? 'N/A') }}</div>
            </div>
            <div class="col-md-6">
                <span class="d-block small text-white-50">Amount Paid</span>
                <strong class="text-success font-monospace">₦{{ number_format($d['amount_paid'] ?? 700, 2) }}</strong>
            </div>
            <div class="col-md-6">
                <span class="d-block small text-white-50">Processing Engine</span>
                @if(($request->provider_name ?? '') === 'Robosttech')
                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 py-1 px-2">
                        <i class="fa-solid fa-robot me-1"></i> Robosttech API
                    </span>
                @else
                    <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-50 py-1 px-2">
                        <i class="fa-solid fa-user-check me-1"></i> Manual Admin Vetting
                    </span>
                @endif
            </div>
        </div>

        @if(!empty($d['remarks']))
            <div class="mt-4 pt-3 border-top border-secondary">
                <span class="d-block small text-white-50 mb-1">Applicant Notes / Remarks:</span>
                <p class="p-3 bg-dark rounded-3 border border-secondary text-white mb-0">{{ $d['remarks'] }}</p>
            </div>
        @endif

        @if($request->admin_note)
            <div class="mt-4 p-3 bg-dark rounded-3 border border-secondary">
                <span class="d-block small text-warning mb-1"><i class="fa-solid fa-note-sticky me-1"></i> Current Admin Note:</span>
                <p class="mb-0 text-white">{{ $request->admin_note }}</p>
            </div>
        @endif
    </div>

    <!-- Admin Action Form -->
    <div class="card glass-card border-0 shadow rounded-4 p-4">
        <h5 class="text-white mb-3">Process Validation Decision</h5>
        <form action="{{ route('admin.verifications.nin_validation.update', $request->id) }}" method="POST">
            @csrf

            <!-- Status Decision -->
            <div class="mb-3">
                <label for="statusSelect" class="form-label text-white-50 small font-weight-bold">Review Decision</label>
                <select name="status" id="statusSelect" class="form-select bg-dark text-white border-secondary" required>
                    <option value="waiting_for_review" @selected($request->status === 'waiting_for_review' || $request->status === 'pending')>Under Review / In Progress</option>
                    <option value="successful" @selected($request->status === 'successful')>Successful (Validated &amp; Approved)</option>
                    <option value="failed" @selected($request->status === 'failed')>Failed (Rejected &amp; Triggers Automatic Wallet Refund)</option>
                </select>
            </div>

            <!-- Confirm/Edit NIN -->
            <div class="mb-3">
                <label for="ninInput" class="form-label text-white-50 small font-weight-bold">Subject NIN Number</label>
                <input type="text" name="nin" id="ninInput" class="form-control bg-dark text-white border-secondary font-monospace" value="{{ $currentNin }}" placeholder="11-digit NIN Number">
            </div>

            <!-- Admin Note / Remarks -->
            <div class="mb-4">
                <label for="adminNote" class="form-label text-white-50 small font-weight-bold">Admin Remarks / Resolution Note (Visible to User)</label>
                <textarea name="admin_note" id="adminNote" rows="3" class="form-control bg-dark text-white border-secondary" placeholder="e.g. Identity verified and cleared on NIMC portal.">{{ $request->admin_note }}</textarea>
                <div class="form-text text-white-50 small">
                    This note will be displayed directly to the applicant when they check their validation status.
                </div>
            </div>

            <div class="alert alert-warning bg-warning bg-opacity-25 border-warning border-opacity-50 text-white small mb-4">
                <i class="fa-solid fa-triangle-exclamation me-1 text-warning"></i>
                <strong>Notice:</strong> Marking this validation as <strong>Failed</strong> will automatically refund <strong>₦{{ number_format($d['amount_paid'] ?? 700, 2) }}</strong> directly to the user's wallet balance.
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.verifications.nin_validation.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary font-weight-bold px-4">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Decision
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
