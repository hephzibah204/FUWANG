@extends('layouts.nexus')

@section('title', 'Queue Monitor')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-white">Queue Monitor</h1>
            <p class="text-muted mb-0">Database queue health, failures, batches, and campaign delivery.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm" style="background:#1e293b;">
                <div class="card-body">
                    <h5 class="text-white mb-3">Jobs</h5>
                    <div class="table-responsive">
                        <table class="table table-hover text-white mb-0">
                            <thead style="background: rgba(255,255,255,0.05);">
                                <tr>
                                    <th class="border-0 p-3">Queue</th>
                                    <th class="border-0 p-3">Pending</th>
                                    <th class="border-0 p-3">Oldest</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jobQueues as $q)
                                    <tr>
                                        <td class="border-0 p-3">{{ $q->queue }}</td>
                                        <td class="border-0 p-3"><span class="badge badge-info">{{ $q->total }}</span></td>
                                        <td class="border-0 p-3 text-white-50">
                                            @if($q->oldest_created_at)
                                                {{ \Carbon\Carbon::createFromTimestamp($q->oldest_created_at)->diffForHumans() }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center p-4 text-muted">No queued jobs.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm" style="background:#1e293b;">
                <div class="card-body">
                    <h5 class="text-white mb-3">Failed Jobs</h5>
                    <div class="table-responsive">
                        <table class="table table-hover text-white mb-0">
                            <thead style="background: rgba(255,255,255,0.05);">
                                <tr>
                                    <th class="border-0 p-3">Queue</th>
                                    <th class="border-0 p-3">Failed</th>
                                    <th class="border-0 p-3">Last</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($failedQueues as $q)
                                    <tr>
                                        <td class="border-0 p-3">{{ $q->queue }}</td>
                                        <td class="border-0 p-3"><span class="badge badge-danger">{{ $q->total }}</span></td>
                                        <td class="border-0 p-3 text-white-50">
                                            @if($q->last_failed_at)
                                                {{ \Carbon\Carbon::parse($q->last_failed_at)->diffForHumans() }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center p-4 text-muted">No failed jobs.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <h6 class="text-white-50 mb-2">Recent failures</h6>
                        <div class="table-responsive">
                            <table class="table table-sm text-white mb-0">
                                <thead style="background: rgba(255,255,255,0.05);">
                                    <tr>
                                        <th class="border-0 p-2">#</th>
                                        <th class="border-0 p-2">Queue</th>
                                        <th class="border-0 p-2">Failed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentFailed as $f)
                                        <tr>
                                            <td class="border-0 p-2 text-white-50">{{ $f->id }}</td>
                                            <td class="border-0 p-2">{{ $f->queue }}</td>
                                            <td class="border-0 p-2 text-white-50">{{ \Carbon\Carbon::parse($f->failed_at)->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center p-3 text-muted">No records.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm" style="background:#1e293b;">
                <div class="card-body">
                    <h5 class="text-white mb-3">Job Batches</h5>
                    <div class="table-responsive">
                        <table class="table table-hover text-white mb-0">
                            <thead style="background: rgba(255,255,255,0.05);">
                                <tr>
                                    <th class="border-0 p-3">Name</th>
                                    <th class="border-0 p-3">Pending</th>
                                    <th class="border-0 p-3">Failed</th>
                                    <th class="border-0 p-3">Created</th>
                                    <th class="border-0 p-3">Finished</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($batches as $b)
                                    <tr>
                                        <td class="border-0 p-3">{{ $b->name }}</td>
                                        <td class="border-0 p-3"><span class="badge badge-info">{{ $b->pending_jobs }}</span></td>
                                        <td class="border-0 p-3"><span class="badge badge-danger">{{ $b->failed_jobs }}</span></td>
                                        <td class="border-0 p-3 text-white-50">{{ \Carbon\Carbon::createFromTimestamp($b->created_at)->format('Y-m-d H:i') }}</td>
                                        <td class="border-0 p-3 text-white-50">
                                            @if($b->finished_at)
                                                {{ \Carbon\Carbon::createFromTimestamp($b->finished_at)->format('Y-m-d H:i') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center p-4 text-muted">No batches yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm" style="background:#1e293b;">
                <div class="card-body">
                    <h5 class="text-white mb-3">Latest Email Campaigns</h5>
                    <div class="table-responsive">
                        <table class="table table-sm text-white mb-0">
                            <thead style="background: rgba(255,255,255,0.05);">
                                <tr>
                                    <th class="border-0 p-2">Name</th>
                                    <th class="border-0 p-2">Status</th>
                                    <th class="border-0 p-2">Delivery</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($emailCampaigns as $c)
                                    <tr>
                                        <td class="border-0 p-2">{{ $c->name }}</td>
                                        <td class="border-0 p-2"><span class="badge badge-dark">{{ $c->status }}</span></td>
                                        <td class="border-0 p-2 text-white-50">{{ $c->delivered_count }}/{{ $c->recipient_count }} (fail {{ $c->failed_count }})</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center p-3 text-muted">No campaigns.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm" style="background:#1e293b;">
                <div class="card-body">
                    <h5 class="text-white mb-3">Latest SMS Campaigns</h5>
                    <div class="table-responsive">
                        <table class="table table-sm text-white mb-0">
                            <thead style="background: rgba(255,255,255,0.05);">
                                <tr>
                                    <th class="border-0 p-2">Name</th>
                                    <th class="border-0 p-2">Status</th>
                                    <th class="border-0 p-2">Delivery</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($smsCampaigns as $c)
                                    <tr>
                                        <td class="border-0 p-2">{{ $c->name }}</td>
                                        <td class="border-0 p-2"><span class="badge badge-dark">{{ $c->status }}</span></td>
                                        <td class="border-0 p-2 text-white-50">{{ $c->delivered_count }}/{{ $c->recipient_count }} (fail {{ $c->failed_count }})</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center p-3 text-muted">No campaigns.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

