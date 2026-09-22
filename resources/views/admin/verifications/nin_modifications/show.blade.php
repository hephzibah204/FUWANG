@extends('layouts.nexus')

@section('title', 'NIN Modification Review | Admin')

@section('content')
<div class="container py-4" style="max-width: 800px;">
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <h3 class="font-weight-bold text-white mb-0"><i class="fa-solid fa-id-card text-warning me-2"></i>Review NIN Modification Request</h3>
        <a href="{{ route('admin.verifications.nin_modifications.index') }}" class="btn btn-outline-light btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Requests
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success text-white bg-success border-0 mb-4 rounded-3">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="card glass-card border-0 shadow rounded-4 p-4 mb-4">
        <h5 class="text-white mb-3">Request Details</h5>
        <div class="row g-3 text-white-50">
            <div class="col-md-6">
                <span class="d-block small text-white-50">Reference ID</span>
                <strong class="text-white"><code>{{ $request->reference_id ?? ('REF-' . $request->id) }}</code></strong>
            </div>
            <div class="col-md-6">
                <span class="d-block small text-white-50">User ID</span>
                <strong class="text-white">User #{{ $request->user_id }}</strong>
            </div>
            <div class="col-md-6">
                <span class="d-block small text-white-50">Status</span>
                @if($request->status === 'successful')
                    <span class="badge bg-success">Successful</span>
                @elseif($request->status === 'failed')
                    <span class="badge bg-danger">Failed</span>
                @else
                    <span class="badge bg-warning text-dark">Pending</span>
                @endif
            </div>
            <div class="col-md-6">
                <span class="d-block small text-white-50">Submitted At</span>
                <strong class="text-white">{{ $request->created_at->format('M d, Y H:i:s') }}</strong>
            </div>
        </div>

        @if($request->admin_note)
            <div class="mt-3 p-3 bg-dark rounded-3 border border-secondary">
                <span class="d-block small text-white-50 mb-1">Current Admin Note:</span>
                <p class="mb-0 text-white">{{ $request->admin_note }}</p>
            </div>
        @endif
    </div>

    <div class="card glass-card border-0 shadow rounded-4 p-4">
        <h5 class="text-white mb-3">Update Status</h5>
        <form action="{{ route('admin.verifications.nin_modifications.update', $request->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="status" class="form-label text-white-50 small">New Status</label>
                <select name="status" id="status" class="form-select bg-dark text-white border-secondary" required>
                    <option value="pending" @selected($request->status === 'pending')>Pending</option>
                    <option value="successful" @selected($request->status === 'successful')>Successful</option>
                    <option value="failed" @selected($request->status === 'failed')>Failed (Triggers Refund)</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="admin_note" class="form-label text-white-50 small">Admin Remarks / Note</label>
                <textarea name="admin_note" id="admin_note" rows="3" class="form-control bg-dark text-white border-secondary" placeholder="Enter remarks or failure reason...">{{ old('admin_note', $request->admin_note) }}</textarea>
            </div>

            <button type="submit" class="btn btn-warning font-weight-bold w-100 py-2.5">
                <i class="fa-solid fa-save me-2"></i> Update Request Status
            </button>
        </form>
    </div>
</div>
@endsection
