@extends('layouts.nexus')

@section('title', 'Email Campaign Recipients')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-white">Recipients</h1>
            <p class="text-muted mb-0">{{ $emailCampaign->name }} • {{ $emailCampaign->subject }}</p>
        </div>
        <div class="d-flex" style="gap: 10px;">
            <a class="btn btn-outline-light" href="{{ route('admin.email_campaigns.index') }}">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back
            </a>
            <a class="btn btn-outline-info" href="{{ route('admin.email_campaigns.recipients.export', $emailCampaign) }}?status={{ urlencode($status) }}&q={{ urlencode($q) }}">
                <i class="fa-solid fa-file-csv mr-2"></i> Export CSV
            </a>
            @if(($counts['failed'] ?? 0) > 0)
                <form method="POST" action="{{ route('admin.email_campaigns.retry_failed', $emailCampaign) }}" class="d-inline" onsubmit="return confirm('Retry all failed recipients for this campaign?');">
                    @csrf
                    <button class="btn btn-warning" type="submit">
                        <i class="fa-solid fa-rotate-right mr-2"></i> Retry Failed ({{ $counts['failed'] }})
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-3" style="background:#1e293b;">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.email_campaigns.recipients', $emailCampaign) }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="text-white-50 small mb-2">Status</label>
                        <select name="status" class="form-control">
                            <option value="" {{ $status === '' ? 'selected' : '' }}>All</option>
                            <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending ({{ $counts['pending'] ?? 0 }})</option>
                            <option value="sent" {{ $status === 'sent' ? 'selected' : '' }}>Sent ({{ $counts['sent'] ?? 0 }})</option>
                            <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Failed ({{ $counts['failed'] ?? 0 }})</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-white-50 small mb-2">Search Email</label>
                        <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="user@example.com">
                    </div>
                    <div class="col-md-3 mb-3 d-flex align-items-end" style="gap: 10px;">
                        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-filter mr-2"></i>Apply</button>
                        <a class="btn btn-outline-light" href="{{ route('admin.email_campaigns.recipients', $emailCampaign) }}"><i class="fa-solid fa-rotate-left mr-2"></i>Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="background:#1e293b;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-white mb-0">
                    <thead style="background: rgba(255,255,255,0.05);">
                        <tr>
                            <th class="border-0 p-3">Email</th>
                            <th class="border-0 p-3">Status</th>
                            <th class="border-0 p-3">Attempts</th>
                            <th class="border-0 p-3">Sent</th>
                            <th class="border-0 p-3">Provider Response</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recipients as $r)
                            <tr>
                                <td class="border-0 p-3">{{ $r->email }}</td>
                                <td class="border-0 p-3">
                                    @if($r->status === 'sent')
                                        <span class="badge badge-success">sent</span>
                                    @elseif($r->status === 'failed')
                                        <span class="badge badge-danger">failed</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $r->status }}</span>
                                    @endif
                                </td>
                                <td class="border-0 p-3 text-white-50">{{ $r->attempts ?? 0 }}</td>
                                <td class="border-0 p-3 text-white-50">{{ optional($r->sent_at)->format('Y-m-d H:i') ?: '—' }}</td>
                                <td class="border-0 p-3 text-white-50">
                                    {{ \Illuminate\Support\Str::limit((string) ($r->provider_response ?? ''), 120) ?: '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center p-5 text-muted">No recipients.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $recipients->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

