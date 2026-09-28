@extends('layouts.nexus')

@section('title', 'Agent Broadcast & Notification Center | Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.agents.overview') }}" class="text-white-50 text-xs text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1"></i> Command Center
                </a>
                <span class="text-white-50 text-xs">/</span>
                <span class="text-emerald-400 text-xs fw-semibold">Agency Broadcasts</span>
            </div>
            <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-bullhorn text-warning me-2"></i>Agent Notification & Broadcast Center</h3>
            <p class="text-white-50 mb-0">Send instant priority alerts, compliance notices, and mass emails to active and onboarding enrollment agents.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-light rounded-pill px-3 fw-bold">
                <i class="fa-solid fa-users me-1"></i>Directory ({{ $counts['total_agents'] }})
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-3 mb-4 p-3 d-flex align-items-center justify-content-between" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0; border: 1px solid rgba(34, 197, 94, 0.3) !important;">
            <div><i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}</div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-0 rounded-3 mb-4 p-3" style="background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3) !important;">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <!-- Broadcast Composer Form -->
        <div class="col-lg-5">
            <div class="card border-0 rounded-4 p-4 sticky-top" style="top: 2rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="text-white fw-bold mb-0"><i class="fa-solid fa-paper-plane text-primary me-2"></i>Compose Announcement</h5>
                    <span class="badge bg-primary/20 text-primary border border-primary/30 rounded-pill px-2 py-0.5 text-2xs">Agents Only</span>
                </div>
                <p class="text-white-50 text-xs mb-4">Announcements are displayed directly on agents' dashboards, with optional email delivery to registered addresses.</p>

                <form action="{{ route('admin.agents.notifications.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label text-white text-xs fw-semibold">Subject / Title <span class="text-danger">*</span></label>
                        <input type="text" name="subject" value="{{ old('subject') }}" class="form-control bg-dark border-secondary text-white rounded-3 py-2 text-sm" placeholder="e.g. Critical Update: New Biometric Capture Guidelines" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label text-white text-xs fw-semibold">Agent Status Filter</label>
                            <select name="target_status" class="form-select bg-dark border-secondary text-white rounded-3 py-2 text-sm">
                                <option value="all">All Statuses ({{ $counts['total_agents'] }})</option>
                                <option value="approved" selected>Approved Only ({{ $counts['approved_agents'] }})</option>
                                <option value="pending">Pending Only ({{ $counts['pending_agents'] }})</option>
                                <option value="suspended">Suspended Only</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label text-white text-xs fw-semibold">State Filter</label>
                            <select name="target_state" class="form-select bg-dark border-secondary text-white rounded-3 py-2 text-sm">
                                <option value="all">All States (Nationwide)</option>
                                @foreach($states as $st)
                                    <option value="{{ $st }}">{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white text-xs fw-semibold">Notice Message Body <span class="text-danger">*</span></label>
                        <textarea name="message" rows="6" class="form-control bg-dark border-secondary text-white rounded-3 py-2 text-sm" placeholder="Type instructions, operational updates, terminal configuration tips, or compliance directives here..." required>{{ old('message') }}</textarea>
                    </div>

                    <div class="p-3 rounded-3 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06);">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="sendEmailSwitch" name="send_email" value="1" checked>
                            <label class="form-check-label text-white text-xs fw-semibold ms-2" for="sendEmailSwitch">
                                Also deliver via Email
                            </label>
                        </div>
                        <small class="text-white-50 text-2xs d-block mt-1">Dispatches personalized email notifications to the targeted agents via configured mail driver.</small>
                    </div>

                    <button type="submit" class="btn btn-primary rounded-pill w-100 py-2.5 fw-bold shadow-sm">
                        <i class="fa-solid fa-bullhorn me-2"></i>Publish Notice to Agents
                    </button>
                </form>
            </div>
        </div>

        <!-- History of Agency Broadcasts -->
        <div class="col-lg-7">
            <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h5 class="text-white fw-bold mb-1"><i class="fa-solid fa-clock-rotate-left text-info me-2"></i>Dispatched Announcements</h5>
                        <p class="text-white-50 text-xs mb-0">Historical log of broadcast directives sent to agents.</p>
                    </div>
                    <span class="badge bg-secondary rounded-pill px-3 py-1 text-xs">{{ $broadcasts->total() }} Total</span>
                </div>

                <div class="d-flex flex-column gap-3">
                    @forelse($broadcasts as $item)
                        <div class="p-3 rounded-3 position-relative" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06);">
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                <div>
                                    <h6 class="text-white fw-bold mb-1">{{ $item->subject }}</h6>
                                    <div class="d-flex align-items-center gap-2 flex-wrap text-2xs text-white-50">
                                        <span><i class="fa-solid fa-calendar me-1"></i>{{ $item->created_at->format('M d, Y h:i A') }}</span>
                                        <span>&bull;</span>
                                        <span class="badge bg-dark border border-secondary text-info rounded-pill">
                                            Audience: {{ ucfirst($item->meta['target_status'] ?? 'all') }}
                                        </span>
                                        @if(!empty($item->meta['target_state']) && $item->meta['target_state'] !== 'all')
                                            <span class="badge bg-dark border border-secondary text-warning rounded-pill">
                                                State: {{ $item->meta['target_state'] }}
                                            </span>
                                        @endif
                                        @if(!empty($item->meta['send_email']))
                                            <span class="badge bg-success/20 text-success border border-success/30 rounded-pill">
                                                <i class="fa-solid fa-envelope me-1"></i>{{ $item->meta['emails_delivered'] ?? 0 }} Emails Sent
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <form action="{{ route('admin.agents.notifications.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete this broadcast notice from agents dashboard?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1" style="width: 30px; height: 30px;" title="Delete broadcast">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>

                            <p class="text-white-50 text-xs mb-0 line-clamp-3" style="white-space: pre-line;">{{ Str::limit($item->message, 300) }}</p>
                        </div>
                    @empty
                        <div class="text-center py-5 text-white-50">
                            <i class="fa-solid fa-comment-slash fa-2x mb-3 text-secondary"></i>
                            <p class="mb-0 text-sm">No agency broadcasts recorded yet.</p>
                            <small class="text-2xs">Use the composer on the left to send an announcement to your agents.</small>
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $broadcasts->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
