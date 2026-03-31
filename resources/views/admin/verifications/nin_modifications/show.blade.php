@extends('layouts.nexus')

@section('title', 'Review NIN Modification Request | Admin Control')

@section('content')
<div class="admin-page fade-in">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('admin.verifications.nin_modifications.index') }}" class="btn btn-sm btn-link text-white-50 text-decoration-none mb-2"><i class="fa fa-arrow-left mr-1"></i> Back to Requests</a>
            <h1 class="h4 font-weight-bold mb-1 text-white">Review Request: {{ $request->reference }}</h1>
            <p class="text-white-50 small">Submitted by {{ $request->user->fullname ?? $request->user->username }} ({{ $request->user->email }})</p>
        </div>
        <div class="status-badge">
            @if($request->status === 'waiting_for_review')
                <span class="badge badge-pill badge-primary px-3 py-2">Waiting Review</span>
            @elseif($request->status === 'pending')
                <span class="badge badge-pill badge-warning px-3 py-2">Processing</span>
            @elseif($request->status === 'successful')
                <span class="badge badge-pill badge-success px-3 py-2">Success</span>
            @else
                <span class="badge badge-pill badge-danger px-3 py-2">Failed</span>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="panel-card p-4 mb-4">
                <h5 class="h6 font-weight-bold mb-3 text-white border-bottom pb-2" style="border-color: rgba(255,255,255,0.05) !important;">Modification Details</h5>
                
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="text-white-50 small mb-1">National Identification Number (NIN)</label>
                        <div class="font-weight-bold text-white font-monospace">{{ $request->data['nin'] ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-white-50 small mb-1">Full Name on Record</label>
                        <div class="font-weight-bold text-white">{{ $request->data['full_name'] ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-white-50 small mb-1">Modification Type</label>
                        <div class="font-weight-bold text-info">{{ ucfirst($request->data['modification_type'] ?? 'N/A') }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-white-50 small mb-1">Submitted Date</label>
                        <div class="font-weight-bold text-white small">{{ $request->created_at->format('F d, Y - H:i') }}</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="p-3 rounded-lg" style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.1);">
                            <label class="text-danger small font-weight-bold mb-1">Old Detail (Current)</label>
                            <div class="text-white small font-italic">{{ $request->data['old_value'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="p-3 rounded-lg" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.1);">
                            <label class="text-success small font-weight-bold mb-1">New Detail (Requested)</label>
                            <div class="text-white small font-weight-bold">{{ $request->data['new_value'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-white-50 small mb-2">Supporting Document/Photo</label>
                    <div class="rounded-lg overflow-hidden border p-2" style="border: 1px solid rgba(255,255,255,0.05) !important;">
                        @php
                            $path = $request->data['document_path'] ?? null;
                            $url = $path ? asset('storage/' . $path) : null;
                        @endphp
                        @if($url)
                            <img src="{{ $url }}" class="img-fluid rounded shadow-sm w-100" style="max-height: 400px; object-fit: contain; cursor: zoom-in;" onclick="window.open('{{ $url }}', '_blank')">
                            <div class="mt-3 text-center">
                                <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-dark rounded-pill px-4" style="background: rgba(255,255,255,0.05);">
                                    <i class="fa fa-download mr-2"></i> Download Document
                                </a>
                            </div>
                        @else
                            <div class="py-5 text-center text-white-50 bg-dark rounded-lg small">
                                <i class="fa fa-image-slash fa-2x mb-2 d-block opacity-2"></i>
                                No supporting document attached.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="panel-card p-4 mb-4">
                <h5 class="h6 font-weight-bold mb-3 text-white border-bottom pb-2" style="border-color: rgba(255,255,255,0.05) !important;">Action Control</h5>
                
                <form action="{{ route('admin.verifications.nin_modifications.update', $request->id) }}" method="POST">
                    @csrf
                    <div class="form-group mb-4">
                        <label class="text-white-50 small mb-2">Update Status</label>
                        <select name="status" class="form-control" required>
                            <option value="waiting_for_review" {{ $request->status === 'waiting_for_review' ? 'selected' : '' }}>Waiting Review</option>
                            <option value="pending" {{ $request->status === 'pending' ? 'selected' : '' }}>Processing / Pending</option>
                            <option value="successful" {{ $request->status === 'successful' ? 'selected' : '' }}>Successful (Completed)</option>
                            <option value="failed" {{ $request->status === 'failed' ? 'selected' : '' }}>Rejected / Failed (Refund User)</option>
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label class="text-white-50 small mb-2">Admin Remarks / Response Report</label>
                        <textarea name="admin_note" class="form-control" style="min-height: 150px; padding-top: 15px;" placeholder="Enter details to be sent to the user dashboard...">{{ $request->admin_note }}</textarea>
                    </div>

                    <div class="alert alert-info border-0 small mb-4" style="background: rgba(59, 130, 246, 0.05); color: #60a5fa;">
                        <i class="fa-solid fa-circle-info mr-2"></i> 
                        Marking as <strong>Failed</strong> will automatically refund the service fee to the user's wallet.
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill">
                        <i class="fa-solid fa-save mr-2"></i> Save Status Update
                    </button>
                </form>
            </div>

            <div class="panel-card p-4">
                <h5 class="h6 font-weight-bold mb-3 text-white border-bottom pb-2" style="border-color: rgba(255,255,255,0.05) !important;">User Audit Trail</h5>
                <div class="small text-white-50">
                    <div class="d-flex justify-content-between mb-2">
                        <span>User ID:</span>
                        <span class="text-white">#{{ $request->user->id }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Wallet Balance:</span>
                        <span class="text-success">₦{{ number_format($request->user->balance->user_balance ?? 0, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Last Active:</span>
                        <span class="text-white">{{ $request->user->last_login_at ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
