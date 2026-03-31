@extends('layouts.nexus')

@section('title', 'Email Campaigns')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-white">Email Campaigns</h1>
            <p class="text-muted mb-0">Compose and send email blasts with templates.</p>
        </div>
        <a href="{{ route('admin.email_campaigns.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-envelopes-bulk mr-2"></i> New Campaign
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card border-0 shadow-sm" style="background:#1e293b;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-white mb-0">
                    <thead style="background: rgba(255,255,255,0.05);">
                        <tr>
                            <th class="border-0 p-3">Name</th>
                            <th class="border-0 p-3">Audience</th>
                            <th class="border-0 p-3">Status</th>
                            <th class="border-0 p-3">Delivery</th>
                            <th class="border-0 p-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campaigns as $c)
                            <tr>
                                <td class="border-0 p-3 align-middle">
                                    <div class="font-weight-bold">{{ $c->name }}</div>
                                    <div class="small text-white-50">{{ $c->subject }}</div>
                                </td>
                                <td class="border-0 p-3 align-middle">
                                    <span class="badge badge-dark">{{ $c->audience_type }}</span>
                                </td>
                                <td class="border-0 p-3 align-middle">
                                    @if($c->status === 'sent')
                                        <span class="badge badge-success">Sent</span>
                                    @elseif($c->status === 'queued')
                                        <span class="badge badge-warning">Queued</span>
                                    @elseif($c->status === 'sending')
                                        <span class="badge badge-info">Sending</span>
                                    @elseif($c->status === 'failed')
                                        <span class="badge badge-danger">Failed</span>
                                    @else
                                        <span class="badge badge-secondary">Draft</span>
                                    @endif
                                </td>
                                <td class="border-0 p-3 align-middle">
                                    <span class="small text-white-50">{{ $c->delivered_count }}/{{ $c->recipient_count }}</span>
                                </td>
                                <td class="border-0 p-3 align-middle text-right">
                                    <a href="{{ route('admin.email_campaigns.recipients', $c) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fa-solid fa-users"></i>
                                    </a>
                                    @if($c->failed_count > 0)
                                        <form action="{{ route('admin.email_campaigns.retry_failed', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Retry failed recipients for this campaign?');">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-warning" type="submit">
                                                <i class="fa-solid fa-rotate-right"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if(in_array($c->status, ['draft','failed']))
                                        <form action="{{ route('admin.email_campaigns.send', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Send this email campaign now?');">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success">
                                                <i class="fa-solid fa-paper-plane"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="small text-white-50">{{ optional($c->sent_at)->format('M d, Y H:i') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center p-5 text-muted">No email campaigns yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $campaigns->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
