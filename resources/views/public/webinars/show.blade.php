@extends('layouts.nexus')

@section('title', $webinar->title . ' - Webinar & Masterclass')

@section('content')
<div class="container py-5">
    <div class="mb-4">
        <a href="{{ route('webinars.index') }}" class="btn btn-outline-secondary text-white rounded-pill px-3 py-1.5 small text-decoration-none">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Webinars
        </a>
    </div>

    <div class="row g-4">
        <!-- Main Webinar Details -->
        <div class="col-lg-8">
            <div class="glass-card p-4 p-md-5 rounded-20 border border-white-10 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge bg-primary text-white rounded-pill px-3 py-1 text-uppercase font-weight-bold text-xs">{{ ucfirst($webinar->category) }}</span>
                    <span class="badge {{ $webinar->isLive() ? 'bg-danger animate-pulse' : ($webinar->isUpcoming() ? 'bg-info' : 'bg-secondary') }} text-white rounded-pill px-3 py-1 text-uppercase font-weight-bold text-xs">
                        {{ $webinar->isLive() ? 'LIVE NOW' : ($webinar->isUpcoming() ? 'UPCOMING' : 'CONCLUDED') }}
                    </span>
                    @if($webinar->price > 0)
                        <span class="badge bg-warning text-dark font-weight-bold rounded-pill px-3 py-1 text-xs">₦{{ number_format($webinar->price, 2) }}</span>
                    @else
                        <span class="badge bg-success text-white font-weight-bold rounded-pill px-3 py-1 text-xs">FREE ACCESS</span>
                    @endif
                </div>

                <h1 class="h2 text-white font-weight-extrabold mb-3">
                    {{ $webinar->title }}
                </h1>

                <!-- Date & Speaker Info Bar -->
                <div class="p-3 bg-white-5 rounded-15 d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 border border-white-5">
                    <div class="d-flex align-items-center gap-3">
                        @if($webinar->speaker_avatar || $webinar->presenter_avatar)
                            <img src="{{ $webinar->speaker_avatar ?? $webinar->presenter_avatar }}" class="rounded-circle" width="48" height="48" alt="{{ $webinar->speaker_name ?? $webinar->presenter_name }}">
                        @else
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center font-weight-bold fs-5" style="width: 48px; height: 48px;">
                                {{ substr($webinar->speaker_name ?? $webinar->presenter_name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <span class="d-block text-white font-weight-bold fs-6">{{ $webinar->speaker_name ?? $webinar->presenter_name }}</span>
                            <span class="d-block text-muted small">{{ $webinar->speaker_title ?? $webinar->presenter_title ?? 'Featured Speaker' }}</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-4">
                        <div>
                            <span class="d-block text-muted text-2xs text-uppercase font-weight-bold">Scheduled Time</span>
                            <span class="d-block text-warning font-weight-bold small">
                                <i class="fa-regular fa-calendar-check me-1"></i> {{ $webinar->scheduled_at->format('M d, Y @ h:i A') }}
                            </span>
                        </div>
                        <div>
                            <span class="d-block text-muted text-2xs text-uppercase font-weight-bold">Duration</span>
                            <span class="d-block text-white font-weight-semibold small">
                                <i class="fa-regular fa-clock me-1"></i> {{ $webinar->duration_minutes }} Minutes
                            </span>
                        </div>
                    </div>
                </div>

                @if($webinar->banner_image)
                    <div class="rounded-15 overflow-hidden mb-4 border border-white-10">
                        <img src="{{ $webinar->banner_image }}" alt="{{ $webinar->title }}" class="w-100 h-auto">
                    </div>
                @endif

                <!-- Overview & Agenda -->
                <div class="text-white-80 lh-lg mb-4">
                    <h3 class="h5 text-white font-weight-bold mb-3">About This Masterclass</h3>
                    {!! nl2br(e($webinar->description)) !!}
                </div>

                @if($webinar->speaker_bio)
                <div class="p-4 rounded-15 bg-white-5 border border-white-10 mb-4">
                    <h4 class="h6 text-white font-weight-bold mb-2"><i class="fa-solid fa-user-tie text-primary me-2"></i> About the Speaker</h4>
                    <p class="text-muted small mb-0">{{ $webinar->speaker_bio }}</p>
                </div>
                @endif

                <!-- Live Stream or Recording Embed (If Registered or Past) -->
                @if(($isRegistered || Auth::guard('admin')->check()) && $webinar->meeting_link && ($webinar->isLive() || $webinar->isUpcoming()))
                    <div class="p-4 rounded-15 bg-primary-subtle border border-primary text-white mb-4">
                        <h4 class="h6 text-white font-weight-bold mb-2"><i class="fa-solid fa-video me-2 text-warning"></i> Access Pass & Live Stream Link</h4>
                        <p class="small mb-3">You are confirmed for this session! Use your exclusive access code below to enter the meeting:</p>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-dark text-warning p-3 font-monospace fs-5 rounded-12 border border-warning">{{ $userReg->access_code ?? 'WEB-MEMBER' }}</span>
                            <a href="{{ $webinar->meeting_link }}" target="_blank" class="btn btn-warning font-weight-bold px-4 py-2 rounded-12">
                                Join Live Meeting <i class="fa-solid fa-external-link-alt ms-1"></i>
                            </a>
                        </div>
                    </div>
                @endif

                @if($webinar->recording_url && !$webinar->isUpcoming())
                    <div class="p-4 rounded-15 bg-success-subtle border border-success text-white mb-4">
                        <h4 class="h6 text-white font-weight-bold mb-2"><i class="fa-solid fa-circle-play me-2 text-success"></i> Masterclass Replay Recording</h4>
                        <p class="small mb-3">Watch the full video recording and presentation deck for this session.</p>
                        <a href="{{ $webinar->recording_url }}" target="_blank" class="btn btn-success font-weight-bold px-4 py-2 rounded-12">
                            Watch Replay Video <i class="fa-solid fa-play ms-1"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar Registration / Pass Card -->
        <div class="col-lg-4">
            <div class="glass-card p-4 rounded-20 border border-white-10 sticky-top" style="top: 90px;">
                @if(session('success'))
                    <div class="alert alert-success border-0 rounded-12 p-3 text-sm mb-4">
                        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                    </div>
                @endif
                @if(session('info'))
                    <div class="alert alert-info border-0 rounded-12 p-3 text-sm mb-4">
                        <i class="fa-solid fa-circle-info me-2"></i> {{ session('info') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger border-0 rounded-12 p-3 text-sm mb-4">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
                    </div>
                @endif

                @if($isRegistered)
                    <div class="text-center py-4">
                        <div class="avatar-icon-wrap bg-success-subtle text-success rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                            <i class="fa-solid fa-check fa-2x"></i>
                        </div>
                        <h3 class="h5 text-white font-weight-bold mb-1">Registration Confirmed</h3>
                        <p class="text-muted small mb-4">You have a reserved seat for this masterclass.</p>

                        <div class="p-3 bg-dark rounded-15 border border-white-10 mb-4">
                            <span class="d-block text-muted text-2xs text-uppercase font-weight-bold">Your Access Code</span>
                            <span class="font-monospace text-warning font-weight-bold fs-4">{{ $userReg->access_code }}</span>
                        </div>

                        <p class="text-white-50 text-2xs mb-0">We sent confirmation details to <strong>{{ $userReg->email }}</strong>.</p>
                    </div>
                @elseif(!$webinar->isSeatsAvailable())
                    <div class="text-center py-4">
                        <i class="fa-solid fa-users-slash text-danger fa-3x mb-3"></i>
                        <h3 class="h5 text-white font-weight-bold">Capacity Reached</h3>
                        <p class="text-muted small">This webinar has reached maximum attendee limits.</p>
                    </div>
                @elseif(!$webinar->isUpcoming() && !$webinar->isLive())
                    <div class="text-center py-4">
                        <i class="fa-solid fa-clock-rotate-left text-muted fa-3x mb-3"></i>
                        <h3 class="h5 text-white font-weight-bold">Webinar Concluded</h3>
                        <p class="text-muted small">Live registration for this event is closed.</p>
                    </div>
                @else
                    <h3 class="h5 text-white font-weight-bold mb-1">Reserve Your Free Seat</h3>
                    <p class="text-muted small mb-4">Fill out the quick form below to get instant pass access & calendar invite.</p>

                    <form action="{{ route('webinars.register', $webinar->slug) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-white small font-weight-semibold">Full Name</label>
                            <input type="text" name="full_name" class="form-control" placeholder="e.g. John Doe" value="{{ Auth::check() ? Auth::user()->fullname ?? Auth::user()->username : old('full_name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white small font-weight-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="name@company.com" value="{{ Auth::check() ? Auth::user()->email : old('email') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white small font-weight-semibold">Phone Number (Optional)</label>
                            <input type="text" name="phone_number" class="form-control" placeholder="08012345678" value="{{ Auth::check() ? Auth::user()->phone : old('phone_number') }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-white small font-weight-semibold">Organization / Business (Optional)</label>
                            <input type="text" name="organization" class="form-control" placeholder="e.g. Acme Tech Ltd" value="{{ old('organization') }}">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-12 font-weight-bold text-uppercase">
                            Register Now <i class="fa-solid fa-paper-plane ms-1"></i>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
