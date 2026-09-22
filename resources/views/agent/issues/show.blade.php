@extends('layouts.app')

@section('title', 'Issue #TK-' . str_pad($ticket->id, 5, '0', STR_PAD_LEFT) . ' | ' . config('app.name'))

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <span class="badge bg-secondary font-monospace mb-1">#TK-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</span>
            <h3 class="text-white fw-bold mb-1">{{ $ticket->subject }}</h3>
            <p class="text-white-50 mb-0">Category: <span class="text-white text-uppercase">{{ str_replace('_', ' ', $ticket->category) }}</span> | Machine IMEI: <code class="text-warning">{{ $ticket->machine_imei ?? 'N/A' }}</code></p>
        </div>
        <a href="{{ route('agent.issues.index') }}" class="btn btn-outline-light rounded-pill btn-sm">Back to Issues</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-3 mb-4 p-3" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0;">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="row g-4">
        <!-- Conversation Timeline -->
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h5 class="text-white fw-bold mb-4"><i class="fa-solid fa-comments text-primary me-2"></i>Resolution Timeline</h5>

                @foreach($ticket->replies as $reply)
                    <div class="mb-4 p-3 rounded-4 {{ $reply->sender_type === 'admin' ? 'bg-primary bg-opacity-10 border border-primary border-opacity-25 ms-3' : 'bg-dark bg-opacity-50 border border-secondary border-opacity-25 me-3' }}">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <strong class="{{ $reply->sender_type === 'admin' ? 'text-info' : 'text-white' }}">
                                @if($reply->sender_type === 'admin')
                                    <i class="fa-solid fa-user-shield me-1"></i>Fuwa.NG Admin Resolution Team
                                @else
                                    <i class="fa-solid fa-user me-1"></i>You (Agent)
                                @endif
                            </strong>
                            <small class="text-white-50">{{ $reply->created_at->format('d M Y, h:i A') }} ({{ $reply->created_at->diffForHumans() }})</small>
                        </div>
                        <p class="text-white-50 mb-2" style="white-space: pre-wrap;">{{ $reply->message }}</p>

                        @if($reply->attachment_path)
                            <div class="mt-3 pt-2 border-top border-secondary">
                                <span class="text-white-50 small d-block mb-1"><i class="fa-solid fa-paperclip me-1"></i>Attached Screenshot / Document:</span>
                                @if(Str::endsWith(strtolower($reply->attachment_path), ['.png', '.jpg', '.jpeg', '.webp']))
                                    <a href="{{ asset('storage/' . $reply->attachment_path) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $reply->attachment_path) }}" alt="Screenshot Proof" class="img-thumbnail bg-dark border-secondary rounded-3" style="max-height: 180px;">
                                    </a>
                                @else
                                    <a href="{{ asset('storage/' . $reply->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill">
                                        <i class="fa-solid fa-file-download me-1"></i>View Attachment
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Agent Reply Box -->
            @if($ticket->status !== 'closed')
                <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                    <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-reply me-2"></i>Post Reply / Follow-up Screenshot</h5>

                    <form action="{{ route('agent.issues.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <textarea name="message" rows="4" class="form-control" required placeholder="Type your follow-up message or updates..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white small fw-bold">Attach Screenshot (Optional)</label>
                            <input type="file" name="attachment" accept="image/png,image/jpeg,image/webp,application/pdf" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="fa-solid fa-paper-plane me-2"></i>Send Reply
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- Issue Metadata Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h5 class="text-white fw-bold mb-3">Ticket Information</h5>

                <div class="mb-3">
                    <strong class="text-white-50 small d-block">Status</strong>
                    @if($ticket->status === 'open')
                        <span class="badge bg-primary px-3 py-1 rounded-pill fs-6">Open</span>
                    @elseif($ticket->status === 'in_progress')
                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fs-6">In Progress</span>
                    @elseif($ticket->status === 'resolved')
                        <span class="badge bg-success px-3 py-1 rounded-pill fs-6">Resolved</span>
                    @elseif($ticket->status === 'closed')
                        <span class="badge bg-secondary px-3 py-1 rounded-pill fs-6">Closed</span>
                    @endif
                </div>

                <div class="mb-3">
                    <strong class="text-white-50 small d-block">Priority</strong>
                    <span class="text-white fw-bold text-uppercase">{{ $ticket->priority }}</span>
                </div>

                <div class="mb-3">
                    <strong class="text-white-50 small d-block">Machine IMEI</strong>
                    <code class="text-warning fs-6">{{ $ticket->machine_imei ?? 'N/A' }}</code>
                </div>

                <div class="mb-3">
                    <strong class="text-white-50 small d-block">Reported Date</strong>
                    <span class="text-white small">{{ $ticket->created_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
