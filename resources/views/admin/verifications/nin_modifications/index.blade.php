@extends('layouts.nexus')

@section('title', 'NIN Modification Requests | Admin Control')

@section('content')
<div class="admin-page fade-in">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 font-weight-bold mb-1 text-white">NIN Modification Jobs</h1>
            <p class="text-white-50 small">Manage and review NIN modification requests from users.</p>
        </div>
        <div class="stats-pills d-flex gap-2">
            <span class="badge-accent bg-primary-transparent"><i class="fa fa-clock mr-1"></i> {{ $requests->where('status', 'waiting_for_review')->count() }} Waiting</span>
            <span class="badge-accent bg-warning-transparent ml-2"><i class="fa fa-spinner fa-spin mr-1"></i> {{ $requests->where('status', 'pending')->count() }} Pending</span>
        </div>
    </div>

    <div class="panel-card p-0 overflow-hidden border-0 shadow-sm" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05) !important;">
        <div class="table-responsive">
            <table class="table table-hover text-white mb-0">
                <thead style="background: rgba(255,255,255,0.03);">
                    <tr>
                        <th class="border-0 small font-weight-bold text-uppercase py-3 pl-4">Reference</th>
                        <th class="border-0 small font-weight-bold text-uppercase py-3">User</th>
                        <th class="border-0 small font-weight-bold text-uppercase py-3">Type</th>
                        <th class="border-0 small font-weight-bold text-uppercase py-3">NIN</th>
                        <th class="border-0 small font-weight-bold text-uppercase py-3">Status</th>
                        <th class="border-0 small font-weight-bold text-uppercase py-3">Submitted</th>
                        <th class="border-0 small font-weight-bold text-uppercase py-3 text-right pr-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                            <td class="py-3 pl-4">
                                <span class="font-weight-bold text-primary">{{ $req->reference }}</span>
                            </td>
                            <td class="py-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-dark mr-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; border: 1px solid rgba(255,255,255,0.1);">
                                        <i class="fa fa-user small text-white-50"></i>
                                    </div>
                                    <span class="small">{{ $req->user->fullname ?? $req->user->username }}</span>
                                </div>
                            </td>
                            <td class="py-3">
                                <span class="badge badge-soft-info">{{ ucfirst($req->data['modification_type'] ?? 'N/A') }}</span>
                            </td>
                            <td class="py-3 font-monospace small">{{ $req->data['nin'] ?? 'N/A' }}</td>
                            <td class="py-3">
                                @if($req->status === 'waiting_for_review')
                                    <span class="badge badge-pill badge-primary px-3">Waiting</span>
                                @elseif($req->status === 'pending')
                                    <span class="badge badge-pill badge-warning px-3">Processing</span>
                                @elseif($req->status === 'successful')
                                    <span class="badge badge-pill badge-success px-3">Success</span>
                                @else
                                    <span class="badge badge-pill badge-danger px-3">Failed</span>
                                @endif
                            </td>
                            <td class="py-3 small text-white-50">
                                {{ $req->created_at->format('M d, H:i') }}
                            </td>
                            <td class="py-3 text-right pr-4">
                                <a href="{{ route('admin.verifications.nin_modifications.show', $req->id) }}" class="btn btn-sm btn-dark rounded-pill px-3" style="background: rgba(255,255,255,0.05);">
                                    <i class="fa fa-eye mr-1"></i> Review
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-white-50">
                                <i class="fa fa-inbox fa-3x mb-3 d-block opacity-2"></i>
                                No modification requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $requests->links() }}
        </div>
    </div>
</div>

<style>
    .badge-soft-info { background: rgba(59, 130, 246, 0.1); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.2); }
    .bg-primary-transparent { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
    .bg-warning-transparent { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
</style>
@endsection
