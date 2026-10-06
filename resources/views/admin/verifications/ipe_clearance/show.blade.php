@extends('layouts.nexus')

@section('title', 'IPE Clearance Review | Admin')

@section('content')
<div class="container py-4" style="max-width: 900px;">
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <div>
            <h3 class="font-weight-bold text-white mb-0"><i class="fa-solid fa-user-check text-warning me-2"></i>Review &amp; Resolve IPE Clearance</h3>
            <p class="text-white-50 small mb-0">Inspect applicant submission, assign a new tracking ID upon success, or reject with automatic wallet refund.</p>
        </div>
        <a href="{{ route('admin.verifications.ipe_clearance.index') }}" class="btn btn-outline-light btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Requests
        </a>
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
        $currentNin = $d['nin'] ?? null;
        $currentNewTracking = $d['new_tracking_id'] ?? null;
    @endphp

    <div class="card glass-card border-0 shadow rounded-4 p-4 mb-4">
        <h5 class="text-white mb-3 border-bottom border-secondary pb-2 d-flex justify-content-between align-items-center">
            <span>Request Identification</span>
            @if($request->status === 'successful')
                <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Successful / Cleared</span>
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
                <span class="d-block small text-white-50">Original Tracking ID</span>
                <strong class="text-warning font-monospace fs-6">{{ $request->identifier }}</strong>
            </div>
            <div class="col-md-6">
                <span class="d-block small text-white-50">NIN Number</span>
                @if(!empty($currentNin))
                    <strong class="text-info font-monospace fs-6">{{ $currentNin }}</strong>
                @else
                    <span class="text-white-50 fst-italic">Not attached by user</span>
                @endif
            </div>
            <div class="col-md-6">
                <span class="d-block small text-white-50">IPE Category</span>
                <strong class="text-white">{{ $d['category'] ?? 'Improcessing Error' }}</strong>
            </div>
            <div class="col-md-6">
                <span class="d-block small text-white-50">User Information</span>
                <strong class="text-white">{{ $request->user->fullname ?? ($d['user_name'] ?? 'Unknown') }}</strong>
                <div class="small text-white-50">{{ $request->user->email ?? ($d['user_email'] ?? 'N/A') }}</div>
            </div>
            <div class="col-md-6">
                <span class="d-block small text-white-50">Amount Paid</span>
                <strong class="text-success font-monospace">₦{{ number_format($d['amount_paid'] ?? 700, 2) }}</strong>
            </div>

            @if(!empty($currentNewTracking))
                <div class="col-12 mt-2">
                    <div class="p-3 bg-success bg-opacity-25 rounded-3 border border-success border-opacity-50 text-white">
                        <small class="d-block text-success-emphasis font-weight-bold mb-1">
                            <i class="fa-solid fa-key me-1"></i> Assigned New Tracking ID:
                        </small>
                        <span class="font-monospace fs-5 text-white font-weight-bold">{{ $currentNewTracking }}</span>
                    </div>
                </div>
            @endif
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
        <h5 class="text-white mb-3">Process Clearance Decision</h5>
        <form action="{{ route('admin.verifications.ipe_clearance.update', $request->id) }}" method="POST">
            @csrf

            <!-- Status Decision -->
            <div class="mb-3">
                <label for="statusSelect" class="form-label text-white-50 small font-weight-bold">Review Decision</label>
                <select name="status" id="statusSelect" class="form-select bg-dark text-white border-secondary" required onchange="toggleResolutionFields(this.value)">
                    <option value="waiting_for_review" @selected($request->status === 'waiting_for_review' || $request->status === 'pending')>Under Review / In Progress</option>
                    <option value="successful" @selected($request->status === 'successful')>Successful (Issue Resolved &amp; Cleared)</option>
                    <option value="failed" @selected($request->status === 'failed')>Failed (Rejected &amp; Triggers Automatic Wallet Refund)</option>
                </select>
            </div>

            <!-- New Tracking ID Input (Crucial for successful clearance) -->
            <div class="mb-3" id="newTrackingWrap">
                <label for="new_tracking_id" class="form-label text-white-50 small font-weight-bold">
                    <i class="fa-solid fa-key text-warning me-1"></i> Assign New Tracking ID (Provided to user if Successful)
                </label>
                <input type="text" name="new_tracking_id" id="new_tracking_id" class="form-control bg-dark text-white border-secondary font-monospace" placeholder="Enter new generated Tracking ID e.g. BTX947E60001099" value="{{ old('new_tracking_id', $currentNewTracking) }}">
                <div class="form-text text-white-50 small">
                    When marked <b>Successful</b>, this new Tracking ID will be returned to the applicant on their dashboard and status lookup.
                </div>
            </div>

            <!-- NIN Update / Correction Input -->
            <div class="mb-3">
                <label for="ninInput" class="form-label text-white-50 small font-weight-bold">
                    <i class="fa-solid fa-id-card text-info me-1"></i> NIN Number (Attach or verify 11-digit NIN)
                </label>
                <input type="text" name="nin" id="ninInput" class="form-control bg-dark text-white border-secondary font-monospace" placeholder="Enter 11-digit NIN if retrieved or verified" value="{{ old('nin', $currentNin) }}" maxlength="20">
            </div>

            <!-- Admin Remarks Note -->
            <div class="mb-4">
                <label for="admin_note" class="form-label text-white-50 small font-weight-bold">Admin Remarks / Clearance Result Note</label>
                <textarea name="admin_note" id="admin_note" rows="3" class="form-control bg-dark text-white border-secondary" placeholder="Enter resolution details, clearance instructions, or rejection reason (visible to user)...">{{ old('admin_note', $request->admin_note) }}</textarea>
                <div id="refundNotice" class="alert alert-danger border-0 small mt-2 mb-0" style="display: {{ $request->status === 'failed' ? 'block' : 'none' }};">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Marking this request as <b>Failed</b> will immediately refund <b>₦{{ number_format($d['amount_paid'] ?? 700, 2) }}</b> back to user's wallet!
                </div>
            </div>

            <button type="submit" class="btn btn-warning font-weight-bold w-100 py-2.5">
                <i class="fa-solid fa-floppy-disk me-2"></i> Update Clearance Report &amp; Notify User
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleResolutionFields(status) {
        if (status === 'successful') {
            $('#newTrackingWrap').slideDown();
            $('#refundNotice').slideUp();
        } else if (status === 'failed') {
            $('#newTrackingWrap').slideUp();
            $('#refundNotice').slideDown();
        } else {
            $('#newTrackingWrap').slideDown();
            $('#refundNotice').slideUp();
        }
    }
</script>
@endpush
