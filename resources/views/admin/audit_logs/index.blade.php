@extends('layouts.nexus')

@section('title', 'Admin Audit Logs')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-white">Admin Audit Logs</h1>
            <p class="text-muted mb-0">Track administrative actions and security events.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3" style="background: #1e293b;">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.audit_logs.index') }}">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="text-white-50 small mb-2">Search</label>
                        <input type="text" name="q" class="form-control" value="{{ $q }}" placeholder="action, ip, user agent, meta">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="text-white-50 small mb-2">Admin</label>
                        <select name="admin_id" class="form-control">
                            <option value="">All</option>
                            @foreach($admins as $a)
                                <option value="{{ $a->id }}" {{ (string) $adminId === (string) $a->id ? 'selected' : '' }}>
                                    {{ $a->username }} ({{ $a->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="text-white-50 small mb-2">Action Prefix</label>
                        <input type="text" name="action" class="form-control" value="{{ $action }}" placeholder="security., settings., custom_apis.">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="text-white-50 small mb-2">From</label>
                        <input type="date" name="from" class="form-control" value="{{ $from }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="text-white-50 small mb-2">To</label>
                        <input type="date" name="to" class="form-control" value="{{ $to }}">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary mr-2" type="submit">
                            <i class="fa-solid fa-filter mr-2"></i> Apply
                        </button>
                        <a class="btn btn-outline-light" href="{{ route('admin.audit_logs.index') }}">
                            <i class="fa-solid fa-rotate-left mr-2"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="background: #1e293b;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-white mb-0">
                    <thead style="background: rgba(255,255,255,0.05);">
                        <tr>
                            <th class="border-0 p-3">Time</th>
                            <th class="border-0 p-3">Admin</th>
                            <th class="border-0 p-3">Action</th>
                            <th class="border-0 p-3">IP</th>
                            <th class="border-0 p-3">Meta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="border-0 p-3 align-middle">
                                    <div class="small">{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</div>
                                </td>
                                <td class="border-0 p-3 align-middle">
                                    <div class="font-weight-bold">{{ $log->admin?->username ?? '—' }}</div>
                                    <div class="small text-muted">{{ $log->admin?->email }}</div>
                                </td>
                                <td class="border-0 p-3 align-middle">
                                    <span class="font-weight-bold">{{ $log->action }}</span>
                                </td>
                                <td class="border-0 p-3 align-middle">
                                    <span class="small text-muted">{{ $log->ip }}</span>
                                </td>
                                <td class="border-0 p-3 align-middle">
                                    @if(is_array($log->meta) && count($log->meta))
                                        <details>
                                            <summary class="small text-info">View</summary>
                                            <pre class="small text-white-50 mb-0" style="white-space: pre-wrap;">{{ json_encode($log->meta, JSON_PRETTY_PRINT) }}</pre>
                                        </details>
                                    @else
                                        <span class="small text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center p-5 text-muted">No audit logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

