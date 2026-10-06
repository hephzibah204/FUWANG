@extends('layouts.nexus')

@section('title', 'IPE Clearance Requests | Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="font-weight-bold text-white mb-1"><i class="fa-solid fa-user-check text-warning me-2"></i>IPE Clearance Requests</h3>
            <p class="text-white-50 small mb-0">Review, verify, and manually process background clearance requests.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success text-white bg-success border-0 mb-4 rounded-3">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="card glass-card border-0 shadow-sm rounded-4 mb-4 p-3">
        <form method="GET" action="{{ route('admin.verifications.ipe_clearance.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control bg-dark text-white border-secondary" placeholder="Search by tracking ID, reference, or user...">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select bg-dark text-white border-secondary">
                    <option value="">All Statuses</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="waiting_for_review" @selected(request('status') === 'waiting_for_review')>Waiting for Review</option>
                    <option value="successful" @selected(request('status') === 'successful')>Successful</option>
                    <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-warning flex-grow-1"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                <a href="{{ route('admin.verifications.ipe_clearance.index') }}" class="btn btn-outline-light"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>

    <div class="card glass-card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-white">
                    <thead style="background: rgba(255,255,255,0.05);">
                        <tr>
                            <th>Reference</th>
                            <th>Tracking ID / NIN</th>
                            <th>User</th>
                            <th>Mode / Details</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td><code>{{ $req->reference_id ?? ('REF-' . $req->id) }}</code></td>
                                <td><span class="font-weight-600 text-warning">{{ $req->identifier }}</span></td>
                                <td>
                                    <div>{{ $req->user->fullname ?? 'Unknown' }}</div>
                                    <small class="text-white-50">{{ $req->user->email ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $req->response_data['service_mode'] ?? 'Manual Vetting' }}
                                    </span>
                                </td>
                                <td>
                                    @if($req->status === 'successful')
                                        <span class="badge bg-success px-2.5 py-1">Successful</span>
                                    @elseif($req->status === 'failed')
                                        <span class="badge bg-danger px-2.5 py-1">Failed</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-2.5 py-1">Under Review</span>
                                    @endif
                                </td>
                                <td>{{ $req->created_at->format('M d, Y H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.verifications.ipe_clearance.show', $req->id) }}" class="btn btn-sm btn-outline-light">
                                        <i class="fa-solid fa-eye me-1"></i> Review
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-white-50">
                                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary d-block"></i>
                                    No IPE clearance requests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($requests->hasPages())
                <div class="p-3 border-top border-secondary-subtle">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
