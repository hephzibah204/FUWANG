@extends('layouts.nexus')

@section('title', 'Webinars & Virtual Masterclasses - Fuwa.NG')

@section('content')
<div class="container py-5">
    <!-- Hero Header -->
    <div class="text-center mb-5 fade-in">
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 text-uppercase font-weight-bold mb-3">
            <i class="fa-solid fa-video me-2"></i> Knowledge & Live Masterclasses
        </span>
        <h1 class="display-4 font-weight-extrabold text-white mb-3">
            Elevate Your Business & Tech Skills
        </h1>
        <p class="lead text-muted mx-auto" style="max-width: 650px;">
            Join expert-led webinars on identity verification, legal compliance, agency growth, fintech, and government tech innovations.
        </p>

        <!-- Category Filter -->
        @if(count($categories) > 0)
        <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
            <a href="{{ route('webinars.index') }}" class="btn btn-sm {{ !$category || $category === 'all' ? 'btn-primary' : 'btn-outline-secondary text-white' }} rounded-pill px-3">
                All Topics
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('webinars.index', ['category' => $cat]) }}" class="btn btn-sm {{ $category === $cat ? 'btn-primary' : 'btn-outline-secondary text-white' }} rounded-pill px-3">
                    {{ ucfirst($cat) }}
                </a>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Upcoming Webinars Section -->
    <div class="mb-5">
        <h3 class="h4 text-white font-weight-bold mb-4 d-flex align-items-center gap-2">
            <i class="fa-solid fa-calendar-star text-warning"></i> Upcoming Live Sessions
        </h3>

        @if($upcoming->isEmpty())
            <div class="glass-card p-5 text-center rounded-20 border border-white-10">
                <i class="fa-solid fa-calendar-xmark text-muted fa-3x mb-3"></i>
                <h4 class="text-white h5 font-weight-semibold">No upcoming webinars scheduled</h4>
                <p class="text-muted mb-0">Check back soon for new sessions, or explore our past masterclass recordings below.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach($upcoming as $webinar)
                    <div class="col-md-6 col-lg-4">
                        <div class="glass-card h-100 d-flex flex-column rounded-20 overflow-hidden border border-white-10 shadow-sm position-relative hover-lift">
                            <div class="position-relative bg-dark" style="height: 180px;">
                                @if($webinar->banner_image)
                                    <img src="{{ $webinar->banner_image }}" alt="{{ $webinar->title }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-gradient-primary">
                                        <i class="fa-solid fa-laptop-code text-white-50 fa-4x"></i>
                                    </div>
                                @endif
                                <span class="position-absolute top-3 right-3 badge {{ $webinar->isLive() ? 'bg-danger animate-pulse' : 'bg-primary' }} rounded-pill px-3 py-1 text-uppercase font-weight-bold">
                                    {{ $webinar->isLive() ? 'LIVE NOW' : 'UPCOMING' }}
                                </span>
                            </div>

                            <div class="p-4 d-flex flex-column flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-2 text-xs text-muted">
                                    <span class="badge bg-secondary text-white rounded-pill px-2.5 py-0.5">{{ ucfirst($webinar->category) }}</span>
                                    <span>•</span>
                                    <span><i class="fa-regular fa-clock me-1"></i> {{ $webinar->duration_minutes }} mins</span>
                                </div>

                                <h4 class="h5 text-white font-weight-bold mb-2 text-truncate-2">
                                    <a href="{{ route('webinars.show', $webinar->slug) }}" class="text-white text-decoration-none">
                                        {{ $webinar->title }}
                                    </a>
                                </h4>

                                <p class="text-muted small mb-4 flex-grow-1 text-truncate-3">
                                    {{ Str::limit($webinar->description, 110) }}
                                </p>

                                <div class="pt-3 border-top border-white-10 mt-auto">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            @if($webinar->speaker_avatar || $webinar->presenter_avatar)
                                                <img src="{{ $webinar->speaker_avatar ?? $webinar->presenter_avatar }}" class="rounded-circle" width="32" height="32" alt="{{ $webinar->speaker_name ?? $webinar->presenter_name }}">
                                            @else
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center font-weight-bold text-xs" style="width: 32px; height: 32px;">
                                                    {{ substr($webinar->speaker_name ?? $webinar->presenter_name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <span class="d-block text-white font-weight-semibold text-xs">{{ $webinar->speaker_name ?? $webinar->presenter_name }}</span>
                                                <span class="d-block text-muted text-2xs">{{ $webinar->speaker_title ?? $webinar->presenter_title ?? 'Presenter' }}</span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="d-block text-warning font-weight-bold text-xs">
                                                <i class="fa-regular fa-calendar me-1"></i> {{ $webinar->scheduled_at->format('M d, Y') }}
                                            </span>
                                            <span class="d-block text-muted text-2xs">{{ $webinar->scheduled_at->format('h:i A T') }}</span>
                                        </div>
                                    </div>

                                    <a href="{{ route('webinars.show', $webinar->slug) }}" class="btn btn-primary w-100 rounded-12 font-weight-semibold">
                                        View Details & Register <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Past Webinars Section -->
    @if(!$past->isEmpty())
    <div class="mt-5 pt-4 border-top border-white-10">
        <h3 class="h4 text-white font-weight-bold mb-4 d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-play text-info"></i> Past Webinars & On-Demand Replays
        </h3>

        <div class="row g-4">
            @foreach($past as $webinar)
                <div class="col-md-6 col-lg-4">
                    <div class="glass-card h-100 d-flex flex-column rounded-20 overflow-hidden border border-white-10 opacity-90">
                        <div class="position-relative bg-dark" style="height: 160px;">
                            @if($webinar->banner_image)
                                <img src="{{ $webinar->banner_image }}" alt="{{ $webinar->title }}" class="w-100 h-100 object-fit-cover filter-grayscale">
                            @else
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-secondary">
                                    <i class="fa-solid fa-video-slash text-white-50 fa-3x"></i>
                                </div>
                            @endif
                            <span class="position-absolute top-3 right-3 badge bg-secondary rounded-pill px-3 py-1 text-uppercase font-weight-bold">
                                CONCLUDED
                            </span>
                        </div>

                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <span class="badge bg-secondary-subtle text-muted rounded-pill px-2.5 py-0.5 align-self-start mb-2 text-2xs">{{ ucfirst($webinar->category) }}</span>
                            <h4 class="h6 text-white font-weight-semibold mb-2">
                                <a href="{{ route('webinars.show', $webinar->slug) }}" class="text-white text-decoration-none">
                                    {{ $webinar->title }}
                                </a>
                            </h4>
                            <p class="text-muted small mb-3 flex-grow-1">
                                Hosted by {{ $webinar->speaker_name ?? $webinar->presenter_name }} on {{ $webinar->scheduled_at->format('M d, Y') }}
                            </p>
                            <a href="{{ route('webinars.show', $webinar->slug) }}" class="btn btn-outline-secondary text-white w-100 rounded-12 font-weight-semibold text-xs">
                                Watch Replay / Summary <i class="fa-solid fa-play ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
