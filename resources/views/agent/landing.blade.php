@extends('layouts.nexus')

@section('title', 'Become a Licensed NIN Registration Agent in Nigeria | Complete Guide & Portal - ' . config('app.name'))
@section('meta_description', 'Learn how to become an accredited NIN Enrollment Agent in Nigeria with Fuwa.NG. Discover hardware requirements, registration steps, and step-by-step onboarding.')
@section('meta_keywords', 'become nin registration agent nigeria, nin enrollment agent, nimc agent registration, start nin registration business, nin agent hardware, nimc license agent, fuwa ng agents')
@section('canonical', route('agent.landing'))

@section('og_title', 'Become a Licensed NIN Registration Agent in Nigeria - Fuwa.NG Agent Portal')
@section('og_description', 'Start your own profitable NIN registration agency in Nigeria. Easy onboarding, verified hardware support, and NIMC compliance.')
@section('og_type', 'website')
@section('public_wrapper_class', 'none')
@section('is_public_page', 'true')

@section('content')
<style>
    .hero-section {
        background: radial-gradient(circle at 80% 20%, rgba(37, 99, 235, 0.15) 0%, transparent 40%),
                    radial-gradient(circle at 20% 80%, rgba(37, 99, 235, 0.1) 0%, transparent 40%);
        position: relative;
        overflow: hidden;
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 1rem;
        transition: all 0.3s ease;
    }
    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        border-color: rgba(37, 99, 235, 0.4);
        background: rgba(255, 255, 255, 0.05);
    }
    .icon-box {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 1rem;
        background: linear-gradient(135deg, rgba(37,99,235,0.2), rgba(37,99,235,0.05));
        border: 1px solid rgba(37,99,235,0.2);
        color: #60a5fa;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .timeline-modern {
        border-left: 2px dashed rgba(255,255,255,0.1);
        padding-left: 2.5rem;
        margin-left: 1rem;
    }
    .timeline-modern .timeline-step {
        position: relative;
        margin-bottom: 3rem;
    }
    .timeline-modern .timeline-step:last-child {
        margin-bottom: 0;
    }
    .timeline-modern .step-indicator {
        position: absolute;
        left: -3.5rem;
        top: 0;
        width: 2.2rem;
        height: 2.2rem;
        background: #2563eb;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
        box-shadow: 0 0 0 0.5rem #0f172a;
    }
    .gradient-text {
        background: linear-gradient(to right, #60a5fa, #a78bfa);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .cta-gradient {
        background: linear-gradient(135deg, #1e3a8a, #2563eb, #3b82f6);
        position: relative;
        overflow: hidden;
        border-radius: 1.5rem;
    }
    .cta-gradient::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCI+PGRlZnM+PHBhdHRlcm4gaWQ9InBhdHRlcm4iIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPbkVzZSI+PHBhdGggZD0iTTAgNDBoNDBWMEgwem0yMCAyMGMxMS4wNDYgMCAyMC04Ljk1NCAyMC0yMFMyOC45NTQgMCAyMCAwIDAgOC45NTQgMCAyMHM4Ljk1NCAyMCAyMCAyMHptMCAyQzguOTU0IDQyIDAgMzMuMDQ2IDAgMjJTMiA4Ljk1NCAxMyA4Ljk1NCAyNCAxNy45MDggMjQgMjguOTU0IDMzLjA0NiA0MiAyMiA0MnoiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiIGZpbGwtcnVsZT0iZXZlbm9kZCIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNwYXR0ZXJuKSIvPjwvc3ZnPg==');
        opacity: 0.3;
    }
    .accordion-custom .accordion-item {
        background: rgba(255,255,255,0.02) !important;
        border: 1px solid rgba(255,255,255,0.05) !important;
        border-radius: 1rem !important;
        margin-bottom: 1rem;
        overflow: hidden;
    }
    .accordion-custom .accordion-button {
        background: transparent !important;
        color: white !important;
        box-shadow: none !important;
        padding: 1.5rem;
    }
    .accordion-custom .accordion-button:not(.collapsed) {
        background: rgba(37,99,235,0.1) !important;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .accordion-custom .accordion-body {
        color: #94a3b8;
        padding: 1.5rem;
    }
    .hover-translate-up {
        transition: transform 0.2s ease;
    }
    .hover-translate-up:hover {
        transform: translateY(-3px);
    }
    .promo-section-card {
        background: linear-gradient(135deg, rgba(6, 78, 59, 0.85) 0%, rgba(15, 23, 42, 0.95) 55%, rgba(20, 83, 45, 0.85) 100%);
        border: 2px solid rgba(250, 204, 21, 0.45);
        border-radius: 1.5rem;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45), 0 0 40px rgba(16, 185, 129, 0.15);
        position: relative;
        overflow: hidden;
    }
    .promo-price-box {
        background: linear-gradient(135deg, #065f46 0%, #047857 100%);
        border: 2px solid rgba(250, 204, 21, 0.6);
        border-radius: 1.25rem;
        position: relative;
    }
    .promo-discount-badge {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: #fff;
        border: 3px solid #facc15;
        border-radius: 1rem;
        transform: rotate(-6deg);
        box-shadow: 0 10px 25px rgba(220, 38, 38, 0.45);
    }
    .promo-req-card {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(16, 185, 129, 0.3);
        border-radius: 1rem;
        transition: all 0.25s ease;
    }
    .promo-req-card:hover {
        background: rgba(16, 185, 129, 0.1);
        border-color: rgba(250, 204, 21, 0.5);
        transform: translateY(-3px);
    }
    .promo-flyer-img {
        border-radius: 1.25rem;
        border: 3px solid rgba(250, 204, 21, 0.5);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        transition: transform 0.3s ease;
    }
    .promo-flyer-img:hover {
        transform: scale(1.015);
    }
    .whatsapp-promo-btn {
        background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        color: #fff !important;
        border: none;
        box-shadow: 0 8px 20px rgba(37, 211, 102, 0.3);
    }
    .whatsapp-promo-btn:hover {
        background: linear-gradient(135deg, #22c35e 0%, #0f766e 100%);
        color: #fff !important;
    }
    .countdown-box {
        min-width: 58px;
        background: rgba(15, 23, 42, 0.75);
        border: 1px solid rgba(250, 204, 21, 0.4);
        border-radius: 0.75rem;
        padding: 0.4rem 0.6rem;
        text-align: center;
    }
</style>

@php
    $promoEndsAt = \Carbon\Carbon::create(2026, 10, 10, 23, 59, 59, 'Africa/Lagos');
    $isPromoActive = now('Africa/Lagos')->lte($promoEndsAt);
@endphp

<div class="hero-section py-5">
    <div class="container-fluid px-lg-5">
        
        <!-- Hero Section -->
        <div class="row align-items-center mb-5 py-lg-4 g-5">
            <div class="col-lg-7">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                    <div class="d-inline-flex align-items-center badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill shadow-sm">
                        <span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true" style="animation-duration: 2s;"></span>
                        <i class="fa-solid fa-id-card me-2 d-none"></i> NIMC Accredited Ecosystem Partner
                    </div>
                    @if($isPromoActive)
                        <a href="#fuwaLaunchPromoSection" id="fuwaHeroPromoBadge" class="d-inline-flex align-items-center badge bg-danger text-white border border-warning px-3 py-2 rounded-pill text-decoration-none shadow-sm hover-translate-up">
                            <i class="fa-solid fa-bullhorn text-warning me-2"></i>
                            <span>LAUNCH PROMO: 50% OFF LICENCE (4–10 OCT)</span>
                        </a>
                    @endif
                </div>
                <h1 class="display-4 text-white fw-extrabold mb-4 lh-sm">
                    Build a Profitable <br>
                    <span class="gradient-text">NIN Enrollment Center</span> <br>
                    in Nigeria.
                </h1>
                <p class="lead text-white-50 mb-5 me-lg-4" style="max-width: 600px;">
                    Join over 250+ verified agents powering Nigeria's digital identity network. Enjoy instant approvals, high-speed biometric synchronization, and unparalleled 24/7 technical support.
                </p>
                
                <div class="d-flex flex-column flex-sm-row flex-wrap align-items-stretch align-items-sm-center gap-2 gap-sm-3 mb-4">
                    @auth
                        @if($agent && $agent->isApproved())
                            <a href="{{ route('agent.dashboard') }}" class="btn btn-primary rounded-pill px-3 px-md-4 py-2 py-md-2.5 fw-semibold hover-translate-up shadow-sm d-inline-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-gauge-high me-2"></i>Enter Workspace
                            </a>
                        @elseif($agent)
                            <a href="{{ route('agent.onboarding.index') }}" class="btn btn-primary rounded-pill px-3 px-md-4 py-2 py-md-2.5 fw-semibold hover-translate-up shadow-sm d-inline-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-list-check me-2"></i>Resume Onboarding
                            </a>
                        @else
                            <a href="{{ route('agent.register', ['type' => 'new']) }}" class="btn btn-primary rounded-pill px-3 px-md-4 py-2 py-md-2.5 fw-semibold hover-translate-up shadow-sm d-inline-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-user-plus me-2"></i>Register as New Agent
                            </a>
                            <a href="{{ route('agent.register', ['type' => 'existing']) }}" class="btn btn-outline-warning rounded-pill px-3 px-md-4 py-2 py-md-2.5 fw-semibold hover-translate-up d-inline-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-building-user me-2"></i>Claim Existing Profile
                            </a>
                        @endif
                        <a href="{{ route('login') }}" class="btn btn-outline-light rounded-pill px-3 px-md-4 py-2 py-md-2.5 fw-semibold hover-translate-up d-inline-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>Agent Login
                        </a>
                    @else
                        <a href="{{ route('agent.register', ['type' => 'new']) }}" class="btn btn-primary rounded-pill px-3 px-md-4 py-2 py-md-2.5 fw-semibold hover-translate-up shadow-sm d-inline-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-user-plus me-2"></i>Start Enrollment Business
                        </a>
                        <a href="{{ route('agent.register', ['type' => 'existing']) }}" class="btn btn-outline-warning rounded-pill px-3 px-md-4 py-2 py-md-2.5 fw-semibold hover-translate-up d-inline-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-building-user me-2"></i>Claim Existing Profile
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light rounded-pill px-3 px-md-4 py-2 py-md-2.5 fw-semibold hover-translate-up d-inline-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>Sign In
                        </a>
                    @endauth
                </div>

                <div class="d-flex align-items-center gap-4 mt-4 opacity-75">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-shield-halved text-success fs-4 me-2"></i>
                        <span class="text-white small fw-bold">Bank-Grade Security</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-bolt text-warning fs-4 me-2"></i>
                        <span class="text-white small fw-bold">Lightning Fast API</span>
                    </div>
                </div>
            </div>

            <!-- Hero Abstract Visuals -->
            <div class="col-lg-5 d-none d-lg-block">
                <div class="position-relative">
                    <div class="position-absolute top-0 start-50 translate-middle w-75 h-75 bg-primary rounded-circle blur-3xl opacity-25" style="filter: blur(60px);"></div>
                    <div class="glass-card p-4 position-relative z-1 mb-4 ms-5 transform-rotate-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="text-white fw-bold mb-0">Daily Enrollments</h6>
                                <span class="text-success small"><i class="fa-solid fa-arrow-trend-up me-1"></i>+24% this week</span>
                            </div>
                            <div class="bg-primary bg-opacity-25 p-2 rounded text-primary">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="progress bg-dark mb-2" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 85%"></div>
                        </div>
                        <div class="d-flex justify-content-between text-white-50 small">
                            <span>Target: 500</span>
                            <span>425 Completed</span>
                        </div>
                    </div>

                    <div class="glass-card p-4 position-relative z-2 me-5 mt-n4" style="transform: translateY(-20px) translateX(-20px);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success bg-opacity-25 p-3 rounded-circle text-success">
                                <i class="fa-solid fa-check-double fs-4"></i>
                            </div>
                            <div>
                                <h5 class="text-white fw-bold mb-0">NIN Slip Generated</h5>
                                <p class="text-white-50 small mb-0">Applicant: Ibrahim O. • 2 mins ago</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row g-4 mb-5 pb-4 border-bottom border-secondary border-opacity-25">
            <div class="col-12 col-md-4">
                <div class="glass-card p-4 text-center text-md-start d-flex flex-column flex-md-row align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary fs-3">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <h3 class="text-white fw-bold mb-0">250+</h3>
                        <p class="text-white-50 small mb-0 text-uppercase tracking-wider">Active Agents</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="glass-card p-4 text-center text-md-start d-flex flex-column flex-md-row align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning fs-3">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div>
                        <h3 class="text-white fw-bold mb-0">99.9%</h3>
                        <p class="text-white-50 small mb-0 text-uppercase tracking-wider">API Uptime</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="glass-card p-4 text-center text-md-start d-flex flex-column flex-md-row align-items-center gap-3">
                    <div class="bg-info bg-opacity-10 p-3 rounded-circle text-info fs-3">
                        <i class="fa-solid fa-shield-check"></i>
                    </div>
                    <div>
                        <h3 class="text-white fw-bold mb-0">100%</h3>
                        <p class="text-white-50 small mb-0 text-uppercase tracking-wider">NIMC Compliant</p>
                    </div>
                </div>
            </div>
        </div>

        @if($isPromoActive)
        <!-- FUWA.NG Launch Promo Section (Auto-hidden after 10 October 2026) -->
        <div id="fuwaLaunchPromoSection" class="mb-5 pb-3" data-promo-end="2026-10-10T23:59:59+01:00">
            <div class="promo-section-card p-4 p-lg-5">
                <div class="row align-items-center g-4 g-lg-5">
                    <!-- Left Column: Promo Details & CTAs -->
                    <div class="col-lg-7">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-bold fs-6 shadow-sm">
                                <i class="fa-solid fa-bullhorn text-warning me-2"></i>FUWA.NG LAUNCH PROMO
                            </span>
                            <span class="badge bg-success text-white border border-warning px-3 py-2 rounded-pill fw-bold fs-6 shadow-sm">
                                <i class="fa-regular fa-calendar-check text-warning me-2"></i>4 – 10 OCTOBER 2026 ONLY
                            </span>
                        </div>

                        <h2 class="text-white fw-extrabold display-6 mb-2">
                            BECOME A <span class="text-warning">FUWA.NG</span> AGENT
                        </h2>
                        <p class="text-white-50 fs-5 mb-4">
                            Offer <strong class="text-white">NIN &amp; BVN enrolment services</strong> in your location and be part of a growing network.
                        </p>

                        <!-- Pricing Box -->
                        <div class="promo-price-box p-4 mb-4 shadow-lg">
                            <div class="row align-items-center g-3">
                                <div class="col-sm-8 text-center text-sm-start">
                                    <span class="badge bg-warning text-dark fw-extrabold px-3 py-1 rounded-pill text-uppercase mb-2">
                                        Licence Price
                                    </span>
                                    <div class="d-flex align-items-baseline justify-content-center justify-content-sm-start gap-3 flex-wrap">
                                        <span class="text-white-50 fs-3 fw-bold text-decoration-line-through" style="text-decoration-color: #ef4444 !important; text-decoration-thickness: 3px !important;">
                                            &#8358;200,000
                                        </span>
                                        <span class="badge bg-dark bg-opacity-50 text-warning small text-uppercase">Now Only</span>
                                    </div>
                                    <div class="display-4 fw-extrabold text-warning lh-1 mt-1">
                                        &#8358;100,000
                                    </div>
                                </div>
                                <div class="col-sm-4 text-center">
                                    <div class="promo-discount-badge d-inline-block px-4 py-3">
                                        <div class="display-5 fw-extrabold lh-1">50<small class="fs-4">%</small></div>
                                        <div class="fw-bold text-warning text-uppercase small tracking-wider">OFF</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3 Promo Pillars -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="promo-req-card p-3 h-100 text-center text-md-start">
                                    <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-25 text-success rounded-circle mb-2" style="width: 42px; height: 42px;">
                                        <i class="fa-solid fa-fingerprint fs-5"></i>
                                    </div>
                                    <h6 class="text-white fw-bold mb-1">Have an Enrolment Device</h6>
                                    <p class="text-white-50 small mb-0">You should already have an enrolment device.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="promo-req-card p-3 h-100 text-center text-md-start">
                                    <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-25 text-success rounded-circle mb-2" style="width: 42px; height: 42px;">
                                        <i class="fa-solid fa-users fs-5"></i>
                                    </div>
                                    <h6 class="text-white fw-bold mb-1">Ready for Mass Enrolment</h6>
                                    <p class="text-white-50 small mb-0">Be ready to carry out at least <strong class="text-white">500 enrolments per month</strong>.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="promo-req-card p-3 h-100 text-center text-md-start">
                                    <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-25 text-success rounded-circle mb-2" style="width: 42px; height: 42px;">
                                        <i class="fa-solid fa-user-plus fs-5"></i>
                                    </div>
                                    <h6 class="text-white fw-bold mb-1">No Existing Users? No Problem.</h6>
                                    <p class="text-white-50 small mb-0">We will support you with users to help you get started.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Countdown & Promo End Bar -->
                        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between bg-dark bg-opacity-50 border border-secondary border-opacity-25 rounded-4 p-3 mb-4 gap-3">
                            <div class="d-flex align-items-center gap-2 text-center text-md-start">
                                <i class="fa-solid fa-clock text-danger fs-3"></i>
                                <div>
                                    <div class="text-white-50 small text-uppercase fw-bold">Promo Ends</div>
                                    <div class="text-warning fw-extrabold fs-5">10 OCTOBER 2026</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2" id="promoCountdownTimer">
                                <div class="countdown-box">
                                    <div class="text-warning fw-bold fs-5 lh-1" id="promoDays">--</div>
                                    <small class="text-white-50" style="font-size: 0.65rem;">DAYS</small>
                                </div>
                                <div class="countdown-box">
                                    <div class="text-warning fw-bold fs-5 lh-1" id="promoHours">--</div>
                                    <small class="text-white-50" style="font-size: 0.65rem;">HRS</small>
                                </div>
                                <div class="countdown-box">
                                    <div class="text-warning fw-bold fs-5 lh-1" id="promoMinutes">--</div>
                                    <small class="text-white-50" style="font-size: 0.65rem;">MINS</small>
                                </div>
                                <div class="countdown-box">
                                    <div class="text-warning fw-bold fs-5 lh-1" id="promoSeconds">--</div>
                                    <small class="text-white-50" style="font-size: 0.65rem;">SECS</small>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 gap-sm-3">
                            <a href="https://chat.whatsapp.com/D1NJKVpjqhg6T8wOaLyvOf" target="_blank" rel="noopener noreferrer" class="btn whatsapp-promo-btn rounded-pill px-4 py-2.5 fw-bold hover-translate-up d-inline-flex align-items-center justify-content-center">
                                <i class="fa-brands fa-whatsapp fs-5 me-2"></i>Join Promo WhatsApp Group
                            </a>
                            <a href="{{ route('agent.register', ['type' => 'new']) }}" class="btn btn-warning text-dark rounded-pill px-4 py-2.5 fw-bold hover-translate-up shadow-sm d-inline-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-bolt me-2"></i>Claim 50% Off Licence Now
                            </a>
                        </div>

                        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-sm-start gap-3 mt-3 pt-2 text-white-50 small">
                            <span><i class="fa-solid fa-handshake text-success me-1"></i>Get Licensed.</span>
                            <span>•</span>
                            <span><i class="fa-solid fa-chart-simple text-warning me-1"></i>Get Started.</span>
                            <span>•</span>
                            <span><i class="fa-solid fa-users text-info me-1"></i>Get Enrolling.</span>
                        </div>
                    </div>

                    <!-- Right Column: Official Flyer & QR Code Link -->
                    <div class="col-lg-5 text-center">
                        <div class="position-relative d-inline-block">
                            <a href="https://chat.whatsapp.com/D1NJKVpjqhg6T8wOaLyvOf" target="_blank" rel="noopener noreferrer" title="Click to join the FUWA.NG Launch Promo WhatsApp Group">
                                <img src="{{ asset('images/fuwa-launch-promo-oct-2026.jpg') }}" alt="FUWA.NG Launch Promo - 50% Off Agent Licence (4 - 10 October 2026)" class="img-fluid promo-flyer-img">
                            </a>
                            <div class="mt-3 bg-dark bg-opacity-75 border border-success border-opacity-50 rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2">
                                <i class="fa-brands fa-whatsapp text-success fs-5"></i>
                                <a href="https://chat.whatsapp.com/D1NJKVpjqhg6T8wOaLyvOf" target="_blank" rel="noopener noreferrer" class="text-white text-decoration-none small fw-semibold">
                                    Scan QR code on flyer or <span class="text-warning text-decoration-underline">tap here to join group</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            (function () {
                const promoSection = document.getElementById('fuwaLaunchPromoSection');
                const heroBadge = document.getElementById('fuwaHeroPromoBadge');
                if (!promoSection) return;

                const endIso = promoSection.getAttribute('data-promo-end');
                const endTime = new Date(endIso).getTime();

                function updatePromoTimer() {
                    const now = Date.now();
                    const diff = endTime - now;

                    if (diff <= 0) {
                        promoSection.remove();
                        if (heroBadge) heroBadge.remove();
                        return;
                    }

                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
                    const minutes = Math.floor((diff / 1000 / 60) % 60);
                    const seconds = Math.floor((diff / 1000) % 60);

                    const dEl = document.getElementById('promoDays');
                    const hEl = document.getElementById('promoHours');
                    const mEl = document.getElementById('promoMinutes');
                    const sEl = document.getElementById('promoSeconds');

                    if (dEl) dEl.textContent = String(days).padStart(2, '0');
                    if (hEl) hEl.textContent = String(hours).padStart(2, '0');
                    if (mEl) mEl.textContent = String(minutes).padStart(2, '0');
                    if (sEl) sEl.textContent = String(seconds).padStart(2, '0');
                }

                updatePromoTimer();
                setInterval(updatePromoTimer, 1000);
            })();
        </script>
        @endif

        <!-- Features Matrix -->
        <div class="row mb-5 py-5">
            <div class="col-12 text-center mb-5">
                <span class="text-primary fw-bold text-uppercase tracking-wider">Why Choose Fuwa.NG?</span>
                <h2 class="text-white fw-bold display-6 mt-2">Everything You Need to Succeed</h2>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="glass-card p-4 h-100">
                    <div class="icon-box"><i class="fa-solid fa-fingerprint"></i></div>
                    <h4 class="text-white fw-bold mb-3">Hardware Flexibility</h4>
                    <p class="text-white-50 mb-0">Already have a scanner? We support all major NIMC-compliant biometric fingerprint devices. Bring your own device and connect instantly.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="glass-card p-4 h-100">
                    <div class="icon-box"><i class="fa-solid fa-bolt-lightning"></i></div>
                    <h4 class="text-white fw-bold mb-3">Instant Slip Generation</h4>
                    <p class="text-white-50 mb-0">Eliminate waiting times. Our powerful servers validate and generate Standard, Premium, and Improved NIN slips within seconds.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="glass-card p-4 h-100">
                    <div class="icon-box"><i class="fa-solid fa-shield-cat"></i></div>
                    <h4 class="text-white fw-bold mb-3">Non-Blocking Verification</h4>
                    <p class="text-white-50 mb-0">Our intelligent gateway caching ensures you can continue to verify and onboard applicants even during central NIMC maintenance windows.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="glass-card p-4 h-100">
                    <div class="icon-box"><i class="fa-solid fa-headset"></i></div>
                    <h4 class="text-white fw-bold mb-3">24/7 Priority Support</h4>
                    <p class="text-white-50 mb-0">Skip the queue. Accredited agents get direct access to our technical engineering team to resolve synchronization or hardware issues swiftly.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="glass-card p-4 h-100">
                    <div class="icon-box"><i class="fa-solid fa-wallet"></i></div>
                    <h4 class="text-white fw-bold mb-3">High Profit Margins</h4>
                    <p class="text-white-50 mb-0">Enjoy transparent, wholesale pricing on all API queries and modifications. Maximize your enrollment center's daily revenue.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="glass-card p-4 h-100">
                    <div class="icon-box"><i class="fa-solid fa-trophy"></i></div>
                    <h4 class="text-white fw-bold mb-3">Monthly Agent Rewards</h4>
                    <p class="text-white-50 mb-0">Compete in our Most Valuable Agent (MVA) monthly leaderboard to win recognition, cash bonuses, and hardware upgrades.</p>
                </div>
            </div>
        </div>

        <!-- How it works (Timeline) & Requirements -->
        <div class="row mb-5 py-5 align-items-center g-5">
            <div class="col-lg-5">
                <span class="text-primary fw-bold text-uppercase tracking-wider">Prerequisites & Eligibility</span>
                <h2 class="text-white fw-bold display-6 mt-2 mb-4">Requirements to Become an Agent</h2>
                <p class="text-white-50 mb-4 lead">
                    Starting a National Identification Number enrollment business requires minimal setup. Whether you are an existing business owner, Cyber Café operator, or entrepreneur, here is what you need:
                </p>

                <ul class="list-unstyled text-white-50 d-flex flex-column gap-3 mb-0">
                    <li class="d-flex align-items-start gap-3">
                        <i class="fa-solid fa-circle-check text-success mt-1"></i>
                        <div>
                            <strong class="text-white">Identity & KYC Documents</strong><br>
                            Valid 11-digit NIN, BVN, proof of address, and a passport photograph.
                        </div>
                    </li>
                    <li class="d-flex align-items-start gap-3">
                        <i class="fa-solid fa-circle-check text-success mt-1"></i>
                        <div>
                            <strong class="text-white">Biometric Hardware</strong><br>
                            NIMC-compliant Fingerprint Scanner & ICAO compliant Web Camera.
                        </div>
                    </li>
                    <li class="d-flex align-items-start gap-3">
                        <i class="fa-solid fa-circle-check text-success mt-1"></i>
                        <div>
                            <strong class="text-white">Physical Location</strong><br>
                            A clean, accessible shop or office space to attend to applicants.
                        </div>
                    </li>
                    <li class="d-flex align-items-start gap-3">
                        <i class="fa-solid fa-circle-check text-success mt-1"></i>
                        <div>
                            <strong class="text-white">Internet Connectivity</strong><br>
                            Stable internet connection for real-time biometric packet uploads.
                        </div>
                    </li>
                </ul>
            </div>

            <div class="col-lg-7">
                <div class="glass-card p-4 p-lg-5">
                    <h3 class="text-white fw-bold mb-5">Step-by-Step Onboarding Process</h3>

                    <div class="timeline-modern">
                        <div class="timeline-step">
                            <div class="step-indicator">1</div>
                            <h5 class="text-white fw-bold mb-2">Create Your Workspace</h5>
                            <p class="text-white-50 mb-0">Register with your full name, phone number, and email address to instantly open your agent account.</p>
                        </div>

                        <div class="timeline-step">
                            <div class="step-indicator">2</div>
                            <h5 class="text-white fw-bold mb-2">Submit Technical Details</h5>
                            <p class="text-white-50 mb-0">Provide your BVN, NIN, machine details (IMEI / serial), and location address for rapid verification.</p>
                        </div>

                        <div class="timeline-step">
                            <div class="step-indicator">3</div>
                            <h5 class="text-white fw-bold mb-2">Upload KYC Documents</h5>
                            <p class="text-white-50 mb-0">Upload a recent utility bill, passport photo, and your CAC business registration (if applicable).</p>
                        </div>

                        <div class="timeline-step">
                            <div class="step-indicator bg-success"><i class="fa-solid fa-check"></i></div>
                            <h5 class="text-white fw-bold mb-2">Get Approved & Start Enrolling</h5>
                            <p class="text-white-50 mb-0">Once accredited, access the portal to generate NIN slips, run verifications, and execute modifications!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="row justify-content-center mb-5 py-5">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <h2 class="text-white fw-bold display-6">Frequently Asked Questions</h2>
                    <p class="text-white-50">Got questions? We've got answers.</p>
                </div>

                <div class="accordion accordion-custom" id="agentFaqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold fs-5" type="button" data-toggle="collapse" data-bs-toggle="collapse" data-target="#faq1" data-bs-target="#faq1">
                                How long does the agent approval process take?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-parent="#agentFaqAccordion" data-bs-parent="#agentFaqAccordion">
                            <div class="accordion-body">
                                Agent applications are reviewed within 24 to 48 hours. If you are an existing agent with a registered company agent code, your account is fast-tracked for instant access!
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold fs-5" type="button" data-toggle="collapse" data-bs-toggle="collapse" data-target="#faq2" data-bs-target="#faq2">
                                Can I use my existing biometric machine?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-parent="#agentFaqAccordion" data-bs-parent="#agentFaqAccordion">
                            <div class="accordion-body">
                                Absolutely! You can connect your existing NIMC-compliant biometric scanner (like Digital Persona, Mantra, or SecuGen). Simply input your machine's IMEI or serial number during the registration phase.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold fs-5" type="button" data-toggle="collapse" data-bs-toggle="collapse" data-target="#faq3" data-bs-target="#faq3">
                                Is there a fee to register as a Fuwa.NG Enrollment Agent?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-parent="#agentFaqAccordion" data-bs-parent="#agentFaqAccordion">
                            <div class="accordion-body">
                                Yes, agent registration requires a licensing and accreditation fee. Once your application is submitted, our team will contact you with details regarding the accreditation payment process.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Final Call to Action -->
        <div class="row justify-content-center text-center py-5">
            <div class="col-lg-10">
                <div class="cta-gradient p-5 shadow-lg">
                    <div class="position-relative z-1 py-4">
                        <h2 class="text-white fw-extrabold display-5 mb-4">Ready to Start Your Registration Center?</h2>
                        <p class="text-white-50 lead mb-5 mx-auto" style="max-width: 700px;">Join the most advanced and robust digital identity verification network in Nigeria today. Create your account in minutes.</p>
                        
                        <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 gap-sm-3 flex-wrap">
                            <a href="{{ route('agent.register', ['type' => 'new']) }}" class="btn btn-light text-primary rounded-pill px-4 py-2.5 fw-bold hover-translate-up shadow-sm d-inline-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-user-plus me-2"></i>Create Agent Account
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline-light rounded-pill px-4 py-2.5 fw-bold hover-translate-up d-inline-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-right-to-bracket me-2"></i>Sign In
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
