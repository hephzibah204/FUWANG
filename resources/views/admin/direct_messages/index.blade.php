@extends('layouts.nexus')

@section('title', 'Direct Messages')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-white">Direct Messages</h1>
            <p class="text-muted mb-0">Send messages to users or user lists.</p>
        </div>
        <a href="{{ route('admin.direct_messages.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-paper-plane mr-2"></i> New Message
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
                            <th class="border-0 p-3">Title</th>
                            <th class="border-0 p-3">Audience</th>
                            <th class="border-0 p-3">Channels</th>
                            <th class="border-0 p-3">Status</th>
                            <th class="border-0 p-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $m)
                            <tr>
                                <td class="border-0 p-3 align-middle">
                                    <div class="font-weight-bold">{{ $m->title }}</div>
                                    <div class="small text-white-50">{{ optional($m->created_at)->format('M d, Y H:i') }}</div>
                                </td>
                                <td class="border-0 p-3 align-middle">
                                    <span class="badge badge-dark">{{ $m->audience_type }}</span>
                                    @if(is_array($m->audience) && count($m->audience))
                                        <div class="small text-white-50">{{ count($m->audience) }} items</div>
                                    @endif
                                </td>
                                <td class="border-0 p-3 align-middle">
                                    <span class="badge badge-secondary">{{ implode(', ', $m->channels ?? []) ?: 'database' }}</span>
                                </td>
                                <td class="border-0 p-3 align-middle">
                                    @if($m->status === 'sent')
                                        <span class="badge badge-success">Sent</span>
                                    @elseif($m->status === 'sending')
                                        <span class="badge badge-info">Sending</span>
                                    @else
                                        <span class="badge badge-secondary">Draft</span>
                                    @endif
                                </td>
                                <td class="border-0 p-3 align-middle text-right">
                                    @if($m->status !== 'sent')
                                        <form action="{{ route('admin.direct_messages.send', $m) }}" method="POST" class="d-inline" onsubmit="return confirm('Send this message now?');">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success">
                                                <i class="fa-solid fa-paper-plane"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="small text-white-50">Delivered: {{ $m->delivered_count }}/{{ $m->recipient_count }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center p-5 text-muted">No direct messages yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $messages->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

