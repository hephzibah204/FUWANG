@extends('layouts.nexus')

@section('title', 'Admin Issue Resolution #TK-' . str_pad($ticket->id, 5, '0', STR_PAD_LEFT) . ' | Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <span class="badge bg-secondary font-monospace mb-1">#TK-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</span>
            <h3 class="text-white fw-bold mb-1">{{ $ticket->subject }}</h3>
            <p class="text-white-50 mb-0">
                Agent: <strong class="text-white">{{ $ticket->agent->full_name ?? 'N/A' }}</strong> |
                IMEI: <code class="text-warning">{{ $ticket->machine_imei ?? 'N/A' }}</code> |
                Category: <span class="text-white text-uppercase">{{ str_replace('_', ' ', $ticket->category) }}</span>
            </p>
        </div>
        <a href="{{ route('admin.agents.issues.index') }}" class="btn btn-outline-light rounded-pill btn-sm">Back to Issues</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-3 mb-4 p-3" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0;">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="row g-4">
        <!-- Main Conversation Thread -->
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h5 class="text-white fw-bold mb-4"><i class="fa-solid fa-comments text-primary me-2"></i>Agent Issue Thread</h5>

                @foreach($ticket->replies as $reply)
                    <div class="mb-4 p-3 rounded-4 {{ $reply->sender_type === 'admin' ? 'bg-primary bg-opacity-10 border border-primary border-opacity-25 ms-3' : 'bg-dark bg-opacity-50 border border-secondary border-opacity-25 me-3' }}">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <strong class="{{ $reply->sender_type === 'admin' ? 'text-info' : 'text-warning' }}">
                                @if($reply->sender_type === 'admin')
                                    <i class="fa-solid fa-user-shield me-1"></i>Admin Support Reply
                                @else
                                    <i class="fa-solid fa-user me-1"></i>Agent: {{ $ticket->agent->full_name ?? $ticket->user_email }}
                                @endif
                            </strong>
                            <small class="text-white-50">{{ $reply->created_at->format('d M Y, h:i A') }}</small>
                        </div>
                        <p class="text-white-50 mb-2" style="white-space: pre-wrap;">{{ $reply->message }}</p>

                        @if($reply->attachment_path)
                            <div class="mt-3 pt-2 border-top border-secondary">
                                <span class="text-white-50 small d-block mb-1"><i class="fa-solid fa-image me-1"></i>Attached Screenshot Proof:</span>
                                @if(Str::endsWith(strtolower($reply->attachment_path), ['.png', '.jpg', '.jpeg', '.webp']))
                                    <a href="{{ asset('storage/' . $reply->attachment_path) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $reply->attachment_path) }}" alt="Screenshot Proof" class="img-thumbnail bg-dark border-secondary rounded-3" style="max-height: 220px;">
                                    </a>
                                @else
                                    <a href="{{ asset('storage/' . $reply->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill">
                                        <i class="fa-solid fa-file-download me-1"></i>View File
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Admin Reply Box -->
            <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-reply me-2"></i>Post Admin Resolution Response</h5>

                <form action="{{ route('admin.agents.issues.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <textarea name="message" rows="4" class="form-control" required placeholder="Type instructions or resolution feedback for the agent..."></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">Update Ticket Status</label>
                            <select name="status" class="form-select">
                                <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">Attach Admin Screenshot/File (Optional)</label>
                            <input type="file" name="attachment" accept="image/png,image/jpeg,image/webp,application/pdf" class="form-control">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-paper-plane me-2"></i>Post Reply & Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Sidebar Actions & Agent Metadata -->
        <div class="col-lg-4">
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h5 class="text-white fw-bold mb-3">Agent & Terminal Info</h5>

                <div class="mb-3">
                    <strong class="text-white-50 small d-block">Agent Name</strong>
                    <span class="text-white fw-bold">{{ $ticket->agent->full_name ?? 'N/A' }}</span>
                </div>

                <div class="mb-3">
                    <strong class="text-white-50 small d-block">Phone Number</strong>
                    <span class="text-white">{{ $ticket->agent->phone_number ?? 'N/A' }}</span>
                </div>

                <div class="mb-3">
                    <strong class="text-white-50 small d-block">Machine IMEI Number</strong>
                    <code class="text-warning fs-6">{{ $ticket->machine_imei ?? $ticket->agent->machine_imei ?? 'N/A' }}</code>
                </div>

                <div class="mb-3">
                    <strong class="text-white-50 small d-block">Station Address</strong>
                    <span class="text-white-50 small d-block">{{ $ticket->agent->office_address ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- Quick Status Switch Form -->
            <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <h5 class="text-white fw-bold mb-3">Quick Status Override</h5>

                <form action="{{ route('admin.agents.issues.status', $ticket->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <select name="status" class="form-select mb-3">
                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                        <button type="submit" class="btn btn-outline-info rounded-pill w-100">Update Status Only</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
