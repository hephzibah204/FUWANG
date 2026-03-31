@extends('layouts.nexus')

@section('title', 'Notifications Center')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-white">Notifications Center</h1>
            <p class="text-white-50 mb-0">Manage your private alerts and direct messages.</p>
        </div>
        @if($notifications->count() > 0)
            <form action="{{ route('notifications.mark_all_read') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light rounded-pill px-4">
                    <i class="fa-solid fa-check-double mr-2"></i> Mark all as Read
                </button>
            </form>
        @endif
    </div>

    <div class="card glass-card border-0" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
        <div class="card-body p-0">
            <div class="list-group list-group-flush" style="background: transparent;">
                @forelse($notifications as $n)
                    @php($data = $n->data)
                    <div class="list-group-item bg-transparent py-4 px-4 border-bottom" style="border-color: rgba(255,255,255,0.05) !important; transition: all 0.3s ease; @if(!$n->read_at) background: rgba(59, 130, 246, 0.05) !important; @endif" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='@if(!$n->read_at) rgba(59, 130, 246, 0.05) @else transparent @endif'">
                        <div class="d-flex align-items-start">
                            <div class="mr-4 mt-1">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(59,130,246,0.15); color: #3b82f6; border: 1px solid rgba(59,130,246,0.3);">
                                    <i class="fa-solid {{ str_contains($n->type, 'DirectMessage') ? 'fa-comment-dots' : 'fa-bell' }}" style="font-size: 1.2rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h5 class="h6 mb-0 font-weight-bold @if(!$n->read_at) text-primary @else text-white @endif">
                                        {{ $data['title'] ?? 'System Alert' }}
                                    </h5>
                                    <span class="small text-white-50">{{ $n->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-white-50 mb-3" style="font-size: 0.92rem; line-height: 1.5;">
                                    {!! Str::words($data['message'] ?? '...', 25, '...') !!}
                                </p>
                                <div class="d-flex align-items-center gap-2" style="gap:12px;">
                                    <a href="{{ route('notifications.show', $n->id) }}" class="btn btn-sm btn-glass px-4 py-2" style="font-size: 0.75rem; border: 1px solid rgba(255,255,255,0.08);">
                                        View Details
                                    </a>
                                    @if(!$n->read_at)
                                        <span class="badge badge-primary px-2 py-1" style="font-size: 0.65rem; border-radius: 4px;">UNREAD</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <div class="mb-4 opacity-50">
                            <i class="fa-solid fa-inbox" style="font-size: 3rem; color: #64748b;"></i>
                        </div>
                        <h4 class="text-white-50 fw-light">Your inbox is empty</h4>
                        <p class="text-white-50">No new notifications at this time.</p>
                    </div>
                @endforelse
            </div>
        </div>
        @if($notifications->hasPages())
            <div class="card-footer bg-transparent border-top py-4 px-4 d-flex justify-content-center" style="border-color: rgba(255,255,255,0.05) !important;">
                {{ $notifications->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>
@endsection
