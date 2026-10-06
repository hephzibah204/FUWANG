@extends('layouts.nexus')

@section('title', 'IPE Clearance Review | Admin')

@section('content')
<div class="container py-4" style="max-width: 850px;">
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <div>
            <h3 class="font-weight-bold text-white mb-0"><i class="fa-solid fa-user-check text-warning me-2"></i>Review IPE Clearance Request</h3>
            <p class="text-white-50 small mb-0">Inspect user submission details, update clearance status, or append vetting notes.</p>
        </div>
        <a href="{{ route('admin.verifications.ipe_clearance.index') }}" class="btn btn-outline-light btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Requests
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success text-white bg-success border-0 mb-4 rounded-3">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="card glass-card border-0 shadow rounded-4 p-4 mb-4">
        <h5 class="text-white mb-3 border-bottom border-secondary pb-2">Request Details</h5>
        <div class="row g-3 text-white-50">
            <div class="col-md-6">
                <span class="d-block small text-white-50">Reference ID</span>
                <strong class="text-white"><code>{{ $request->reference_id ?? ('REF-' . $request->id) }}</code></strong>
            </div>
            <div class="col-md-6">
                <span class="d-block small text-white-50">Tracking ID / Number</span>
                <strong class="text-warning font-monospace fs-6">{{ $request->identifier }}</strong>
            </div>
            <div class="col-md-6">
                <span class="d-block small text-white-50">User Information</span>
                <strong class="text-white">{{ $request->user->fullname ?? 'Unknown' }} ({{ $request->user->email ?? 'N/A' }})</strong>
                @if(!empty($request->user->phone))
                    <div class="small text-white-50">{{ $request->user->phone }}</div>
                @endif
            </div>
            <div class="col-md-6">
                <span class="d-block small text-white-50">Submitted At</span>
                <strong class="text-white">{{ $request->created_at->format('M d, Y H:i:s') }}</strong>
            </div>
            <div class="col-md-6">
                <span class="d-block small text-white-50">Current Status</span>
                @if($request->status === 'successful')
                    <span class="badge bg-success">Successful</span>
                @elseif($request->status === 'failed')
                    <span class="badge bg-danger">Failed</span>
                @else
                    <span class="badge bg-warning text-dark">Under Review</span>
                @endif
            </div>
            <div class="col-md-6">
                <span class="d-block small text-white-50">Provider / Processor</span>
                <strong class="text-white">{{ $request->provider_name }}</strong>
            </div>
        </div>

        @if(!empty($request->response_data))
            <div class="mt-4 pt-3 border-top border-secondary">
                <h6 class="text-white mb-2">Submitted Data & Payload:</h6>
                <div class="p-3 bg-dark rounded-3 border border-secondary">
                    <pre class="mb-0 text-white-50 font-monospace small" style="white-space: pre-wrap;">{{ json_encode($request->response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        @endif

        @if($request->admin_note)
            <div class="mt-4 p-3 bg-dark rounded-3 border border-secondary">
                <span class="d-block small text-warning mb-1"><i class="fa-solid fa-note-sticky me-1"></i> Current Admin Note / Remark:</span>
                <p class="mb-0 text-white">{{ $request->admin_note }}</p>
            </div>
        @endif
    </div>

    <div class="card glass-card border-0 shadow rounded-4 p-4">
        <h5 class="text-white mb-3">Update Clearance Status</h5>
        <form action="{{ route('admin.verifications.ipe_clearance.update', $request->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="status" class="form-label text-white-50 small">Review Decision</label>
                <select name="status" id="status" class="form-select bg-dark text-white border-secondary" required>
                    <option value="waiting_for_review" @selected($request->status === 'waiting_for_review' || $request->status === 'pending')>Waiting for Review / In Progress</option>
                    <option value="successful" @selected($request->status === 'successful')>Successful (Cleared)</option>
                    <option value="failed" @selected($request->status === 'failed')>Failed (Rejected &amp; Triggers Refund)</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="admin_note" class="form-label text-white-50 small">Admin Remarks / Clearance Result Note</label>
                <textarea name="admin_note" id="admin_note" rows="4" class="form-control bg-dark text-white border-secondary" placeholder="Enter findings, certificate details, or reason for rejection (visible to user)...">{{ old('admin_note', $request->admin_note) }}</textarea>
                <div class="form-text text-white-50 small mt-1">
                    If marked as <strong>Failed</strong>, the user's wallet will automatically be refunded and this note will serve as the reason.
                </div>
            </div>

            <button type="submit" class="btn btn-warning font-weight-bold w-100 py-2.5">
                <i class="fa-solid fa-save me-2"></i> Save Status &amp; Update User
            </button>
        </form>
    </div>
</div>
@endsection
