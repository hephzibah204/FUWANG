@extends('layouts.nexus')

@section('title', 'Notification Details')

@section('content')
<div class="row justify-content-center py-5">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary rounded-pill px-4" style="background: rgba(255,255,255,0.05);">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Notifications
            </a>
            <span class="text-white-50 small">{{ $notification->created_at->format('M d, Y H:i') }}</span>
        </div>

        <div class="card glass-card border-0 mb-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="card-body p-5">
                <div class="mb-4 text-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 64px; height: 64px; background: rgba(59,130,246,0.1); color: #3b82f6; border: 1px solid rgba(59,130,246,0.2);">
                        <i class="fa-solid {{ str_contains($notification->type, 'DirectMessage') ? 'fa-comment-dots' : 'fa-bell' }}" style="font-size: 1.5rem;"></i>
                    </div>
                    <h2 class="h4 text-white font-weight-bold mb-1">{{ $notification->data['title'] ?? 'System Announcement' }}</h2>
                    <p class="text-white-50"><span class="badge badge-dark">Sender: System Admin</span></p>
                </div>

                <hr style="border-top-color: rgba(255,255,255,0.05); margin: 2rem 0;">

                <div class="notification-body text-white" style="font-size: 1.1rem; line-height: 1.7; color: rgba(255,255,255,0.85);">
                    {!! nl2br(e($notification->data['message'] ?? '')) !!}
                </div>

                <div class="mt-5 p-4 rounded-3 text-center" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                    <small class="text-white-50">This message is intended only for your account security and service updates.</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
