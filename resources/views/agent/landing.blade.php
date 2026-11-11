@extends('layouts.nexus')

@section('title', 'Licensed NIN Registration Agent Portal | Hardware & Onboarding - ' . config('app.name'))
@section('meta_description', 'Accredited NIN enrollment partner portal. Hardware specifications, licensing requirements, and agent registration in Nigeria.')
@section('meta_keywords', 'nin enrollment agent, nimc agent registration, start nin registration business, nin agent hardware, nimc license agent, fuwa ng agents')
@section('canonical', route('agent.landing'))

@section('og_title', 'Licensed NIN Registration Agent Portal - Fuwa.NG')
@section('og_description', 'Operate an accredited NIN enrollment center in Nigeria. Verified hardware support, instant slip processing, and NIMC compliance.')
@section('og_type', 'website')
@section('public_wrapper_class', 'none')
@section('is_public_page', 'true')

@section('content')
<style>
    /* =========================================================
       Unslopped Agent Portal Styles: Institutional & Crisp
       ========================================================= */
    .agent-landing-page {
        --agent-surface: #0f172a;
        --agent-surface-raised: #1e293b;
        --agent-surface-subtle: rgba(15, 23, 42, 0.6);
        --agent-border: rgba(255, 255, 255, 0.08);
        --agent-border-subtle: rgba(255, 255, 255, 0.05);
        --agent-text-main: #f8fafc;
        --agent-text-muted: #94a3b8;
        --agent-text-soft: #64748b;
        --agent-accent: #2563eb;
        --agent-accent-hover: #1d4ed8;
        --agent-accent-soft: rgba(37, 99, 235, 0.12);
        --agent-success: #10b981;
        --agent-success-soft: rgba(16, 185, 129, 0.12);
        color: var(--agent-text-main);
    }

    /* Light Mode Overrides */
    [data-theme="light"] .agent-landing-page,
    body.theme-light .agent-landing-page {
        --agent-surface: #ffffff;
        --agent-surface-raised: #f8fafc;
        --agent-surface-subtle: #f1f5f9;
        --agent-border: #e2e8f0;
        --agent-border-subtle: #edf2f7;
        --agent-text-main: #0f172a;
        --agent-text-muted: #475569;
        --agent-text-soft: #64748b;
        --agent-accent: #1d4ed8;
        --agent-accent-hover: #1e40af;
        --agent-accent-soft: #eff6ff;
        --agent-success: #059669;
        --agent-success-soft: #ecfdf5;
    }

    /* Typography & Hierarchy */
    .agent-hero-title {
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 700;
        line-height: 1.18;
        letter-spacing: -0.025em;
        color: var(--agent-text-main);
    }

    .agent-hero-lead {
        font-size: 1.125rem;
        line-height: 1.6;
        color: var(--agent-text-muted);
        max-width: 580px;
    }

    .agent-section-title {
        font-size: 1.75rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--agent-text-main);
    }

    .agent-section-eyebrow {
        font-size: 0.8125rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--agent-accent);
        margin-bottom: 0.5rem;
        display: inline-block;
    }

    /* Status Pill */
    .agent-badge-partner {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--agent-accent-soft);
        color: var(--agent-accent);
        border: 1px solid var(--agent-border);
        padding: 0.35rem 0.85rem;
        border-radius: 9999px;
        font-size: 0.8125rem;
        font-weight: 600;
    }

    .agent-live-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background-color: var(--agent-success);
        display: inline-block;
    }

    /* Buttons */
    .agent-btn-primary {
        background-color: var(--agent-accent);
        border: 1px solid var(--agent-accent);
        color: #ffffff !important;
        font-weight: 600;
        padding: 0.65rem 1.4rem;
        border-radius: 8px;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }
    .agent-btn-primary:hover {
        background-color: var(--agent-accent-hover);
        border-color: var(--agent-accent-hover);
    }

    .agent-btn-secondary {
        background-color: transparent;
        border: 1px solid var(--agent-border);
        color: var(--agent-text-main) !important;
        font-weight: 600;
        padding: 0.65rem 1.4rem;
        border-radius: 8px;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }
    .agent-btn-secondary:hover {
        background-color: var(--agent-surface-subtle);
        border-color: var(--agent-accent);
    }

    .agent-login-link {
        font-size: 0.875rem;
        color: var(--agent-text-muted);
    }

    /* Hero Terminal Mockup (The Moment) */
    .agent-terminal-card {
        background: var(--agent-surface);
        border: 1px solid var(--agent-border);
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .agent-terminal-header {
        background: var(--agent-surface-raised);
        border-bottom: 1px solid var(--agent-border);
        padding: 0.75rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .agent-window-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }
    .dot-red { background: #ef4444; opacity: 0.85; }
    .dot-yellow { background: #f59e0b; opacity: 0.85; }
    .dot-green { background: #10b981; opacity: 0.85; }

    .agent-terminal-title {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--agent-text-muted);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }

    .agent-status-badge {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--agent-success);
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .agent-terminal-body {
        padding: 1.25rem;
    }

    .agent-metric-box {
        background: var(--agent-surface-subtle);
        border: 1px solid var(--agent-border);
        border-radius: 8px;
        padding: 1rem;
    }

    .agent-metric-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--agent-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.25rem;
    }

    .agent-metric-val {
        font-size: 1.35rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        color: var(--agent-text-main);
    }

    .agent-metric-sub {
        font-size: 0.8125rem;
        color: var(--agent-text-soft);
    }

    .agent-progress-bar {
        height: 5px;
        background: var(--agent-border);
        border-radius: 4px;
        overflow: hidden;
    }

    .agent-progress-fill {
        height: 100%;
        background: var(--agent-accent);
        border-radius: 4px;
    }

    .agent-terminal-feed {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        margin-top: 1rem;
    }

    .agent-feed-item {
        background: var(--agent-surface-subtle);
        border: 1px solid var(--agent-border-subtle);
        border-radius: 8px;
        padding: 0.75rem 1rem;
    }

    .agent-feed-badge {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--agent-success);
        background: var(--agent-success-soft);
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
    }

    .agent-feed-time {
        font-size: 0.75rem;
        color: var(--agent-text-soft);
        font-variant-numeric: tabular-nums;
    }

    .agent-feed-detail {
        font-size: 0.8125rem;
        color: var(--agent-text-muted);
        margin-top: 0.25rem;
    }

    .agent-terminal-footer {
        border-top: 1px solid var(--agent-border);
        padding-top: 0.85rem;
        margin-top: 1rem;
        font-size: 0.75rem;
        color: var(--agent-text-soft);
    }

    /* Stats Row */
    .agent-stat-card {
        background: var(--agent-surface);
        border: 1px solid var(--agent-border);
        border-radius: 10px;
        padding: 1.25rem;
        height: 100%;
    }

    .agent-stat-num {
        font-size: 1.75rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        color: var(--agent-text-main);
        line-height: 1.2;
        margin-bottom: 0.25rem;
    }

    .agent-stat-label {
        font-size: 0.8125rem;
        color: var(--agent-text-muted);
        line-height: 1.4;
    }

    /* Feature Cards */
    .agent-feature-card {
        background: var(--agent-surface);
        border: 1px solid var(--agent-border);
        border-radius: 12px;
        padding: 1.5rem;
        height: 100%;
        transition: border-color 0.15s ease;
    }
    .agent-feature-card:hover {
        border-color: var(--agent-accent);
    }

    .agent-feature-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: var(--agent-accent-soft);
        color: var(--agent-accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        margin-bottom: 1.15rem;
    }

    .agent-feature-title {
        font-size: 1.0625rem;
        font-weight: 600;
        color: var(--agent-text-main);
        margin-bottom: 0.5rem;
    }

    .agent-feature-desc {
        font-size: 0.875rem;
        line-height: 1.55;
        color: var(--agent-text-muted);
        margin-bottom: 0;
    }

    /* Prerequisites & Timeline */
    .agent-prereq-item {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
    }

    .agent-prereq-icon {
        color: var(--agent-success);
        margin-top: 0.25rem;
        flex-shrink: 0;
    }

    .agent-prereq-title {
        font-weight: 600;
        color: var(--agent-text-main);
        font-size: 0.9375rem;
    }

    .agent-prereq-desc {
        color: var(--agent-text-muted);
        font-size: 0.875rem;
        margin-top: 0.15rem;
    }

    .agent-timeline {
        position: relative;
        padding-left: 2rem;
        border-left: 2px solid var(--agent-border);
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .agent-timeline-step {
        position: relative;
    }

    .agent-step-marker {
        position: absolute;
        left: -2.7rem;
        top: 0;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--agent-surface);
        border: 2px solid var(--agent-accent);
        color: var(--agent-accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .agent-step-marker.step-done {
        border-color: var(--agent-success);
        color: var(--agent-success);
    }

    .agent-step-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--agent-text-main);
        margin-bottom: 0.25rem;
    }

    .agent-step-desc {
        font-size: 0.875rem;
        color: var(--agent-text-muted);
        line-height: 1.5;
        margin-bottom: 0;
    }

    /* FAQ Accordion */
    .agent-accordion .accordion-item {
        background: var(--agent-surface) !important;
        border: 1px solid var(--agent-border) !important;
        border-radius: 10px !important;
        margin-bottom: 0.75rem;
        overflow: hidden;
    }

    .agent-accordion .accordion-button {
        background: var(--agent-surface) !important;
        color: var(--agent-text-main) !important;
        font-size: 1rem;
        font-weight: 600;
        padding: 1.15rem 1.25rem;
        box-shadow: none !important;
    }

    .agent-accordion .accordion-button:not(.collapsed) {
        border-bottom: 1px solid var(--agent-border);
        color: var(--agent-accent) !important;
    }

    .agent-accordion .accordion-body {
        background: var(--agent-surface);
        color: var(--agent-text-muted);
        font-size: 0.9375rem;
        line-height: 1.6;
        padding: 1.25rem;
    }

    /* CTA Card */
    .agent-cta-card {
        background: var(--agent-surface);
        border: 1px solid var(--agent-border);
        border-top: 3px solid var(--agent-accent);
        border-radius: 14px;
    }

    .agent-cta-title {
        font-size: 1.85rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--agent-text-main);
    }

    .agent-cta-desc {
        font-size: 1rem;
        line-height: 1.6;
        color: var(--agent-text-muted);
    }

    /* Promo Section (Structured & Unslopped) */
    .agent-promo-card {
        background: var(--agent-surface);
        border: 1px solid var(--agent-border);
        border-left: 4px solid var(--agent-accent);
        border-radius: 14px;
    }

    .agent-badge-danger {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.25);
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
    }

    .agent-badge-neutral {
        background: var(--agent-surface-subtle);
        color: var(--agent-text-muted);
        border: 1px solid var(--agent-border);
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
    }

    .agent-promo-sub {
        font-size: 1rem;
        color: var(--agent-text-muted);
    }

    .agent-promo-pricing {
        background: var(--agent-surface-subtle);
        border: 1px solid var(--agent-border);
        border-radius: 10px;
        padding: 1.25rem;
    }

    .agent-price-regular {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--agent-text-soft);
        text-decoration: line-through;
        font-variant-numeric: tabular-nums;
    }

    .agent-price-discounted {
        font-size: 2rem;
        font-weight: 800;
        color: var(--agent-text-main);
        font-variant-numeric: tabular-nums;
    }

    .agent-badge-discount {
        background: var(--agent-success-soft);
        color: var(--agent-success);
        border: 1px solid rgba(16, 185, 129, 0.25);
        font-size: 0.8125rem;
        font-weight: 700;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
    }

    .agent-price-caption {
        font-size: 0.8125rem;
        color: var(--agent-text-soft);
    }

    .agent-pillar-card {
        background: var(--agent-surface-subtle);
        border: 1px solid var(--agent-border);
        border-radius: 8px;
        padding: 1rem;
        height: 100%;
    }

    .agent-pillar-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--agent-text-main);
        margin-bottom: 0.25rem;
    }

    .agent-pillar-desc {
        font-size: 0.8125rem;
        color: var(--agent-text-muted);
        line-height: 1.45;
        margin-bottom: 0;
    }

    .agent-btn-whatsapp {
        background-color: #16a34a;
        border: 1px solid #16a34a;
        color: #ffffff !important;
        font-weight: 600;
        padding: 0.65rem 1.25rem;
        border-radius: 8px;
    }
    .agent-btn-whatsapp:hover {
        background-color: #15803d;
        border-color: #15803d;
    }

    .agent-flyer-wrap {
        border: 1px solid var(--agent-border);
        border-radius: 12px;
        overflow: hidden;
        background: var(--agent-surface-subtle);
        display: inline-block;
        max-width: 380px;
    }

    .agent-flyer-img {
        display: block;
        width: 100%;
        height: auto;
    }

    .agent-countdown-timer {
        display: flex;
        gap: 0.5rem;
    }

    .agent-countdown-unit {
        background: var(--agent-surface-subtle);
        border: 1px solid var(--agent-border);
        border-radius: 6px;
        padding: 0.35rem 0.5rem;
        text-align: center;
        min-width: 48px;
    }

    .agent-countdown-val {
        font-size: 1rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        color: var(--agent-text-main);
        line-height: 1.1;
    }

    .agent-countdown-label {
        font-size: 0.625rem;
        text-transform: uppercase;
        color: var(--agent-text-soft);
        letter-spacing: 0.05em;
    }
</style>

@php
    $promoEndsAt = \Carbon\Carbon::create(2026, 10, 10, 23, 59, 59, 'Africa/Lagos');
    $isPromoActive = now('Africa/Lagos')->lte($promoEndsAt);
@endphp

<div class="agent-landing-page py-4 py-lg-5">
    <div class="container-xl px-3 px-sm-4">

        <!-- Hero Section: Institutional Split -->
        <div class="row align-items-center g-4 g-lg-5 mb-5 pb-lg-3">
            <div class="col-lg-7">
                <div class="mb-3">
                    <span class="agent-badge-partner">
                        <span class="agent-live-dot"></span>
                        NIMC Accredited Ecosystem Partner
                    </span>
                    @if($isPromoActive)
                        <a href="#fuwaLaunchPromoSection" id="fuwaHeroPromoBadge" class="badge agent-badge-danger ms-2 text-decoration-none">
                            <i class="fa-solid fa-bullhorn me-1"></i> Launch Promo: 50% Off Licence
                        </a>
                    @endif
                </div>

                <h1 class="agent-hero-title mb-3">
                    National Identity Enrollment Infrastructure for Agents and Centers
                </h1>

                <p class="agent-hero-lead mb-4">
                    Join accredited operators powering Nigeria's digital identity network. Connect compliant biometric hardware, issue verified NIN slips, and access real-time NIMC synchronization.
                </p>

                <!-- Action Group -->
                <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                    @auth
                        @if($agent && $agent->isApproved())
                            <a href="{{ route('agent.dashboard') }}" class="btn btn-primary agent-btn-primary d-inline-flex align-items-center">
                                <i class="fa-solid fa-gauge-high me-2"></i>Enter Workspace
                            </a>
                        @elseif($agent)
                            <a href="{{ route('agent.onboarding.index') }}" class="btn btn-primary agent-btn-primary d-inline-flex align-items-center">
                                <i class="fa-solid fa-list-check me-2"></i>Resume Onboarding
                            </a>
                        @else
                            <a href="{{ route('agent.register', ['type' => 'new']) }}" class="btn btn-primary agent-btn-primary d-inline-flex align-items-center">
                                <i class="fa-solid fa-user-plus me-2"></i>Register as Enrollment Agent
                            </a>
                            <a href="{{ route('agent.register', ['type' => 'existing']) }}" class="btn btn-secondary agent-btn-secondary d-inline-flex align-items-center">
                                <i class="fa-solid fa-building-user me-2"></i>Claim Existing Profile
                            </a>
                        @endif
                        <span class="agent-login-link">
                            Already licensed? <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-semibold">Sign in</a>
                        </span>
                    @else
                        <a href="{{ route('agent.register', ['type' => 'new']) }}" class="btn btn-primary agent-btn-primary d-inline-flex align-items-center">
                            <i class="fa-solid fa-user-plus me-2"></i>Register as Enrollment Agent
                        </a>
                        <a href="{{ route('agent.register', ['type' => 'existing']) }}" class="btn btn-secondary agent-btn-secondary d-inline-flex align-items-center">
                            <i class="fa-solid fa-building-user me-2"></i>Claim Existing Profile
                        </a>
                        <span class="agent-login-link">
                            Already licensed? <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-semibold">Sign in</a>
                        </span>
                    @endauth
                </div>

                <!-- Trust / Verification Highlights -->
                <div class="d-flex flex-wrap align-items-center gap-4 text-muted small pt-2 border-top" style="border-color: var(--agent-border) !important;">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-circle-check text-success me-2"></i>
                        <span>NIMC Standards Compliant</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-server text-primary me-2"></i>
                        <span>Automated Gateway Sync</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-fingerprint text-primary me-2"></i>
                        <span>Certified Scanner Support</span>
                    </div>
                </div>
            </div>

            <!-- Hero Visual: Agent Enrollment Terminal Console -->
            <div class="col-lg-5">
                <div class="agent-terminal-card">
                    <div class="agent-terminal-header">
                        <div class="d-flex align-items-center gap-1.5">
                            <span class="agent-window-dot dot-red"></span>
                            <span class="agent-window-dot dot-yellow ms-1"></span>
                            <span class="agent-window-dot dot-green ms-1"></span>
                            <span class="agent-terminal-title ms-2">Terminal Node #042 · Lagos Central</span>
                        </div>
                        <span class="agent-status-badge">
                            <span class="agent-live-dot"></span> Online
                        </span>
                    </div>

                    <div class="agent-terminal-body">
                        <!-- Terminal Metrics -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="agent-metric-box">
                                    <div class="agent-metric-label">Daily Enrolments</div>
                                    <div class="agent-metric-val">425 <span class="agent-metric-sub">/ 500 Target</span></div>
                                    <div class="agent-progress-bar mt-2">
                                        <div class="agent-progress-fill" style="width: 85%;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="agent-metric-box">
                                    <div class="agent-metric-label">Scanner Status</div>
                                    <div class="agent-metric-val text-success" style="font-size: 1.15rem;">Connected</div>
                                    <div class="agent-metric-sub mt-2"><i class="fa-solid fa-fingerprint me-1"></i> SecuGen / Mantra</div>
                                </div>
                            </div>
                        </div>

                        <!-- Terminal Event Feed -->
                        <div class="agent-terminal-feed">
                            <div class="agent-feed-item">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="agent-feed-badge">NIN Slip Issued</span>
                                    <span class="agent-feed-time">1m ago</span>
                                </div>
                                <div class="agent-feed-detail">Applicant: Ibrahim O. · Standard Slip (NIMC 200 OK)</div>
                            </div>
                            <div class="agent-feed-item">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="agent-feed-badge" style="background: var(--agent-accent-soft); color: var(--agent-accent);">Biometric Sync</span>
                                    <span class="agent-feed-time">4m ago</span>
                                </div>
                                <div class="agent-feed-detail">10-Finger Packet Encrypted &amp; Dispatched</div>
                            </div>
                        </div>

                        <div class="agent-terminal-footer d-flex align-items-center justify-content-between">
                            <span><i class="fa-solid fa-shield-halved text-success me-1"></i> NIMC Ecosystem Protocol</span>
                            <span class="font-monospace">Latency: 48ms</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Grid -->
        <div class="row g-3 mb-5 pb-3">
            <div class="col-6 col-md-3">
                <div class="agent-stat-card">
                    <div class="agent-stat-num">250+</div>
                    <div class="agent-stat-label">Active Enrollment Agents</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="agent-stat-card">
                    <div class="agent-stat-num">99.9%</div>
                    <div class="agent-stat-label">Gateway Availability</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="agent-stat-card">
                    <div class="agent-stat-num">100%</div>
                    <div class="agent-stat-label">NIMC Compliant Architecture</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="agent-stat-card">
                    <div class="agent-stat-num">24–48h</div>
                    <div class="agent-stat-label">Standard Onboarding Window</div>
                </div>
            </div>
        </div>

        @if($isPromoActive)
        <!-- FUWA.NG Launch Promo Section -->
        <div id="fuwaLaunchPromoSection" class="mb-5 pb-3" data-promo-end="2026-10-10T23:59:59+01:00">
            <div class="agent-promo-card p-4 p-lg-5">
                <div class="row align-items-center g-4 g-lg-5">
                    <div class="col-lg-7">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            <span class="badge agent-badge-danger">
                                <i class="fa-solid fa-bullhorn me-1.5"></i>FUWA.NG LAUNCH PROMO
                            </span>
                            <span class="badge agent-badge-neutral">
                                <i class="fa-regular fa-calendar-check me-1.5"></i>4 – 10 OCTOBER 2026 ONLY
                            </span>
                        </div>

                        <h2 class="agent-section-title mb-2">
                            Become a Fuwa.NG Enrollment Agent
                        </h2>
                        <p class="agent-promo-sub mb-4">
                            Offer official NIN and BVN enrolment services in your location as part of an accredited identity network.
                        </p>

                        <!-- Pricing Breakdown -->
                        <div class="agent-promo-pricing mb-4">
                            <div class="row align-items-center g-3">
                                <div class="col-sm-8">
                                    <div class="d-flex align-items-baseline gap-3 flex-wrap">
                                        <span class="agent-price-regular">&#8358;200,000</span>
                                        <span class="agent-price-discounted">&#8358;100,000</span>
                                        <span class="badge agent-badge-discount">50% License Discount</span>
                                    </div>
                                    <div class="agent-price-caption mt-1">One-time accreditation and terminal license fee.</div>
                                </div>
                                <div class="col-sm-4 text-sm-end">
                                    <div class="agent-countdown-timer justify-content-sm-end" id="promoCountdownTimer">
                                        <div class="agent-countdown-unit">
                                            <div class="agent-countdown-val" id="promoDays">--</div>
                                            <div class="agent-countdown-label">Days</div>
                                        </div>
                                        <div class="agent-countdown-unit">
                                            <div class="agent-countdown-val" id="promoHours">--</div>
                                            <div class="agent-countdown-label">Hrs</div>
                                        </div>
                                        <div class="agent-countdown-unit">
                                            <div class="agent-countdown-val" id="promoMinutes">--</div>
                                            <div class="agent-countdown-label">Mins</div>
                                        </div>
                                        <div class="agent-countdown-unit">
                                            <div class="agent-countdown-val" id="promoSeconds">--</div>
                                            <div class="agent-countdown-label">Secs</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3 Pillars -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="agent-pillar-card">
                                    <i class="fa-solid fa-fingerprint text-primary mb-2 fs-5"></i>
                                    <div class="agent-pillar-title">Biometric Device</div>
                                    <div class="agent-pillar-desc">Bring your existing NIMC-compliant fingerprint scanner.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="agent-pillar-card">
                                    <i class="fa-solid fa-users text-primary mb-2 fs-5"></i>
                                    <div class="agent-pillar-title">Mass Enrolment</div>
                                    <div class="agent-pillar-desc">Operational capacity for at least 500 monthly enrolments.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="agent-pillar-card">
                                    <i class="fa-solid fa-user-plus text-primary mb-2 fs-5"></i>
                                    <div class="agent-pillar-title">Routing Support</div>
                                    <div class="agent-pillar-desc">Technical onboarding and user allocation support provided.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <a href="https://chat.whatsapp.com/D1NJKVpjqhg6T8wOaLyvOf" target="_blank" rel="noopener noreferrer" class="btn agent-btn-whatsapp d-inline-flex align-items-center">
                                <i class="fa-brands fa-whatsapp me-2 fs-5"></i>Join Promo WhatsApp Group
                            </a>
                            <a href="{{ route('agent.register', ['type' => 'new']) }}" class="btn btn-primary agent-btn-primary d-inline-flex align-items-center">
                                Claim 50% Off Licence
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-5 text-center">
                        <div class="agent-flyer-wrap">
                            <a href="https://chat.whatsapp.com/D1NJKVpjqhg6T8wOaLyvOf" target="_blank" rel="noopener noreferrer" title="Join Fuwa Launch Promo WhatsApp Group">
                                <img src="{{ asset('images/fuwa-launch-promo-oct-2026.jpg') }}" alt="FUWA.NG Launch Promo" class="img-fluid agent-flyer-img">
                            </a>
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

        <!-- Capabilities Matrix -->
        <div class="mb-5 py-4">
            <div class="text-center mb-5">
                <span class="agent-section-eyebrow">Platform Capabilities</span>
                <h2 class="agent-section-title">Built for High-Throughput Enrollment Centers</h2>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="agent-feature-card">
                        <div class="agent-feature-icon">
                            <i class="fa-solid fa-fingerprint"></i>
                        </div>
                        <h3 class="agent-feature-title">Hardware Interoperability</h3>
                        <p class="agent-feature-desc">Compatible with standard NIMC-certified biometric scanners (SecuGen, Mantra, Morpho, Digital Persona). Connect your existing scanner without proprietary lock-in.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="agent-feature-card">
                        <div class="agent-feature-icon">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                        <h3 class="agent-feature-title">Instant Slip Issuance</h3>
                        <p class="agent-feature-desc">Fast generation of Standard, Premium, and Improved NIN slips with automated cryptographic verification and print-ready layouts.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="agent-feature-card">
                        <div class="agent-feature-icon">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <h3 class="agent-feature-title">High-Availability Gateway</h3>
                        <p class="agent-feature-desc">Resilient verification caching and routing ensures your center stays operational without blocking walk-in applicants during maintenance windows.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="agent-feature-card">
                        <div class="agent-feature-icon">
                            <i class="fa-solid fa-headset"></i>
                        </div>
                        <h3 class="agent-feature-title">Dedicated Operations Desk</h3>
                        <p class="agent-feature-desc">Direct priority escalation channel to our technical operations team for driver setups, machine serial mapping, and sync troubleshooting.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="agent-feature-card">
                        <div class="agent-feature-icon">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                        <h3 class="agent-feature-title">Transparent Wholesale Tariffs</h3>
                        <p class="agent-feature-desc">Fixed wholesale rates on all identity lookups, verification calls, and slip modifications with instant automated wallet settlement.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="agent-feature-card">
                        <div class="agent-feature-icon">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <h3 class="agent-feature-title">Performance Tiers</h3>
                        <p class="agent-feature-desc">Volume-based fee discounts and recognition incentives for verified enrollment centers handling consistent monthly verification traffic.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requirements & Onboarding Steps -->
        <div class="row align-items-center g-5 mb-5 py-4">
            <div class="col-lg-5">
                <span class="agent-section-eyebrow">Prerequisites &amp; Eligibility</span>
                <h2 class="agent-section-title mb-3">Requirements to Become an Agent</h2>
                <p class="text-muted mb-4">
                    Operating a National Identification Number enrollment center requires standard business verification and compliant terminal equipment:
                </p>

                <div class="d-flex flex-column gap-3">
                    <div class="agent-prereq-item">
                        <i class="fa-solid fa-circle-check agent-prereq-icon"></i>
                        <div>
                            <div class="agent-prereq-title">Identity &amp; KYC Documentation</div>
                            <div class="agent-prereq-desc">Valid 11-digit NIN, BVN, proof of business address, and passport photograph.</div>
                        </div>
                    </div>
                    <div class="agent-prereq-item">
                        <i class="fa-solid fa-circle-check agent-prereq-icon"></i>
                        <div>
                            <div class="agent-prereq-title">Certified Biometric Scanner</div>
                            <div class="agent-prereq-desc">NIMC-approved optical fingerprint scanner and ICAO-standard web camera.</div>
                        </div>
                    </div>
                    <div class="agent-prereq-item">
                        <i class="fa-solid fa-circle-check agent-prereq-icon"></i>
                        <div>
                            <div class="agent-prereq-title">Physical Location</div>
                            <div class="agent-prereq-desc">Verifiable shop, office, or cyber café premises accessible to applicants.</div>
                        </div>
                    </div>
                    <div class="agent-prereq-item">
                        <i class="fa-solid fa-circle-check agent-prereq-icon"></i>
                        <div>
                            <div class="agent-prereq-title">Reliable Internet Connectivity</div>
                            <div class="agent-prereq-desc">Stable internet access for real-time biometric packet uploads and sync.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="agent-feature-card p-4 p-lg-5">
                    <h3 class="agent-section-title mb-4" style="font-size: 1.35rem;">Step-by-Step Onboarding Process</h3>

                    <div class="agent-timeline">
                        <div class="agent-timeline-step">
                            <div class="agent-step-marker">1</div>
                            <div class="agent-step-title">Create Your Operator Account</div>
                            <p class="agent-step-desc">Register with your full legal name, phone number, and verified email address.</p>
                        </div>

                        <div class="agent-timeline-step">
                            <div class="agent-step-marker">2</div>
                            <div class="agent-step-title">Submit Machine &amp; Location Details</div>
                            <p class="agent-step-desc">Provide your BVN, NIN, machine serial/IMEI, and physical location address.</p>
                        </div>

                        <div class="agent-timeline-step">
                            <div class="agent-step-marker">3</div>
                            <div class="agent-step-title">Upload KYC Documents</div>
                            <p class="agent-step-desc">Upload a recent utility bill, passport photo, and CAC registration certificate (if applicable).</p>
                        </div>

                        <div class="agent-timeline-step">
                            <div class="agent-step-marker step-done"><i class="fa-solid fa-check"></i></div>
                            <div class="agent-step-title">Accreditation &amp; Terminal Activation</div>
                            <p class="agent-step-desc">Upon approval, access your agent workspace to begin issuing slips and performing verifications.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="row justify-content-center mb-5 py-4">
            <div class="col-lg-8">
                <div class="text-center mb-4">
                    <span class="agent-section-eyebrow">Common Inquiries</span>
                    <h2 class="agent-section-title">Frequently Asked Questions</h2>
                </div>

                <div class="accordion agent-accordion" id="agentFaqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-toggle="collapse" data-bs-toggle="collapse" data-target="#faq1" data-bs-target="#faq1">
                                How long does the agent approval process take?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-parent="#agentFaqAccordion" data-bs-parent="#agentFaqAccordion">
                            <div class="accordion-body">
                                Applications are reviewed within 24 to 48 hours. Existing agents with pre-registered company agent codes are fast-tracked for expedited access.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-toggle="collapse" data-bs-toggle="collapse" data-target="#faq2" data-bs-target="#faq2">
                                Can I connect my existing biometric hardware?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-parent="#agentFaqAccordion" data-bs-parent="#agentFaqAccordion">
                            <div class="accordion-body">
                                Yes. You can connect any NIMC-compliant biometric scanner including SecuGen, Mantra, Morpho, and Digital Persona devices. Simply provide your machine's serial number during onboarding.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-toggle="collapse" data-bs-toggle="collapse" data-target="#faq3" data-bs-target="#faq3">
                                What is the fee structure for becoming an agent?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-parent="#agentFaqAccordion" data-bs-parent="#agentFaqAccordion">
                            <div class="accordion-body">
                                Agent licensing includes a one-time accreditation fee covering terminal licensing, gateway setup, and driver integration support. Details are confirmed during the application review.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="row justify-content-center text-center py-4 mb-4">
            <div class="col-lg-10">
                <div class="agent-cta-card p-4 p-lg-5">
                    <div class="mx-auto" style="max-width: 680px;">
                        <h2 class="agent-cta-title mb-3">Ready to Start Your Enrollment Center?</h2>
                        <p class="agent-cta-desc mb-4">
                            Connect your compliant hardware and access Nigeria's digital identity infrastructure in minutes.
                        </p>

                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            @auth
                                @if($agent && $agent->isApproved())
                                    <a href="{{ route('agent.dashboard') }}" class="btn btn-primary agent-btn-primary d-inline-flex align-items-center">
                                        <i class="fa-solid fa-gauge-high me-2"></i>Enter Workspace
                                    </a>
                                @elseif($agent)
                                    <a href="{{ route('agent.onboarding.index') }}" class="btn btn-primary agent-btn-primary d-inline-flex align-items-center">
                                        <i class="fa-solid fa-list-check me-2"></i>Resume Onboarding
                                    </a>
                                @else
                                    <a href="{{ route('agent.register', ['type' => 'new']) }}" class="btn btn-primary agent-btn-primary d-inline-flex align-items-center">
                                        <i class="fa-solid fa-user-plus me-2"></i>Register as Enrollment Agent
                                    </a>
                                    <a href="{{ route('agent.register', ['type' => 'existing']) }}" class="btn btn-secondary agent-btn-secondary d-inline-flex align-items-center">
                                        <i class="fa-solid fa-building-user me-2"></i>Claim Existing Profile
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('agent.register', ['type' => 'new']) }}" class="btn btn-primary agent-btn-primary d-inline-flex align-items-center">
                                    <i class="fa-solid fa-user-plus me-2"></i>Register as Enrollment Agent
                                </a>
                                <a href="{{ route('agent.register', ['type' => 'existing']) }}" class="btn btn-secondary agent-btn-secondary d-inline-flex align-items-center">
                                    <i class="fa-solid fa-building-user me-2"></i>Claim Existing Profile
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
