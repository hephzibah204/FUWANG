@extends('layouts.nexus')

@section('title', 'NIN Modification Requests | Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="font-weight-bold text-white mb-1"><i class="fa-solid fa-id-card text-warning me-2"></i>NIN Modification Requests</h3>
            <p class="text-white-50 small mb-0">Manage customer NIMC data update and modification requests.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success text-white bg-success border-0 mb-4 rounded-3">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="card glass-card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-white">
                    <thead style="background: rgba(255,255,255,0.05);">
                        <tr>
                            <th>Reference</th>
                            <th>User ID</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td><code>{{ $req->reference_id ?? ('REF-' . $req->id) }}</code></td>
                                <td>User #{{ $req->user_id }}</td>
                                <td>NIN Modification</td>
                                <td>
                                    @if($req->status === 'successful')
                                        <span class="badge bg-success px-2.5 py-1">Successful</span>
                                    @elseif($req->status === 'failed')
                                        <span class="badge bg-danger px-2.5 py-1">Failed</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-2.5 py-1">Pending</span>
                                    @endif
                                </td>
                                <td>{{ $req->created_at->format('M d, Y H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.verifications.nin_modifications.show', $req->id) }}" class="btn btn-sm btn-outline-light">
                                        <i class="fa-solid fa-eye me-1"></i> Review
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-white-50">
                                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary d-block"></i>
                                    No NIN modification requests found.
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
