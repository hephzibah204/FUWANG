@extends('layouts.nexus')

@section('title', 'Broadcast Messaging')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-white">Broadcast Messaging</h1>
            <p class="text-muted">Manage and send targeted notifications to users.</p>
        </div>
        <a href="{{ route('admin.broadcasts.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-paper-plane mr-2"></i> New Broadcast
        </a>
    </div>

    <div class="card border-0 shadow-sm" style="background: #1e293b;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-white mb-0">
                    <thead style="background: rgba(255,255,255,0.05);">
                        <tr>
                            <th class="border-0 p-3">Subject</th>
                            <th class="border-0 p-3">Audience</th>
                            <th class="border-0 p-3">Status</th>
                            <th class="border-0 p-3">Scheduled / Sent</th>
                            <th class="border-0 p-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($broadcasts as $broadcast)
                        <tr>
                            <td class="border-0 p-3 align-middle">
                                <div class="font-weight-bold">{{ $broadcast->subject }}</div>
                                <div class="small text-muted text-truncate" style="max-width: 300px;">{{ Str::limit($broadcast->message, 50) }}</div>
                            </td>
                            <td class="border-0 p-3 align-middle">
                                <span class="badge badge-pill badge-secondary">{{ ucfirst($broadcast->target_audience) }}</span>
                            </td>
                            <td class="border-0 p-3 align-middle">
                                @if($broadcast->status === 'sent')
                                    <span class="badge badge-success">Sent</span>
                                @elseif($broadcast->status === 'scheduled')
                                    <span class="badge badge-warning">Scheduled</span>
                                @else
                                    <span class="badge badge-secondary">Draft</span>
                                @endif
                            </td>
                            <td class="border-0 p-3 align-middle">
                                <div class="small">
                                    @if($broadcast->sent_at)
                                        <i class="fa-solid fa-check-circle text-success mr-1"></i> {{ $broadcast->sent_at->format('M d, Y H:i') }}
                                    @elseif($broadcast->scheduled_at)
                                        <i class="fa-solid fa-clock text-warning mr-1"></i> {{ $broadcast->scheduled_at->format('M d, Y H:i') }}
                                    @else
                                        —
                                    @endif
                                </div>
                            </td>
                            <td class="border-0 p-3 align-middle text-right">
                                @if($broadcast->status !== 'sent')
                                    <form action="{{ route('admin.broadcasts.send', $broadcast->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Send this broadcast now?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success mr-1" title="Send Now">
                                            <i class="fa-solid fa-paper-plane"></i>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.broadcasts.destroy', $broadcast->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this broadcast?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center p-5 text-muted">
                                <i class="fa-regular fa-envelope fa-3x mb-3 opacity-50"></i>
                                <p>No broadcasts found. Create your first message.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $broadcasts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
