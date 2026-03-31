@extends('layouts.nexus')

@section('title', 'G-Soft Verify - AI Legal Hub & Next-Gen Identity Verification')

@section('content')
<div class="welcome-wrapper">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-7 text-left fade-in-up">
                    <div class="hero-badge mb-4">
                        <span class="badge-dot"></span>
                        <span class="badge-text">Trusted by 10,000+ Businesses Nationwide</span>
                    </div>
                    <h1 class="hero-title display-3 font-weight-bold mb-4">
                        Verify <span class="text-gradient">Identities</span>.<br>
                        Draft <span class="text-gradient">Legal Docs</span>.<br>
                        Scale <span class="text-gradient">Faster</span>.
                    </h1>
                    <p class="hero-subtitle lead mb-5">
                        The all-in-one ecosystem for AI-powered legal drafting, instant identity verification, and seamless financial services. Built for the modern African enterprise.
                    </p>
                    <div class="hero-actions d-flex flex-wrap gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-xl">
                                <i class="fa-solid fa-gauge-high mr-2"></i> Enter Dashboard
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="btn btn-primary btn-xl">
                                <i class="fa-solid fa-rocket mr-2"></i> Get Started Free
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline-glass btn-xl">
                                <i class="fa-solid fa-right-to-bracket mr-2"></i> Member Login
                            </a>
                        @endauth
                    </div>
                    <div class="hero-stats mt-5 d-flex gap-5">
                        <div class="stat-item">
                            <div class="stat-num">99.9%</div>
                            <div class="stat-desc">Uptime API</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-num">50+</div>
                            <div class="stat-desc">Services</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-num">24/7</div>
                            <div class="stat-desc">Support</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="hero-visual">
                        <img src="{{ request()->getBaseUrl() }}/images/hero_visual.png" class="img-fluid float-anim shadow-glow" alt="Fuwa.ng Ecosystem Features" decoding="async" fetchpriority="high" style="border-radius: 30px; border: 1px solid rgba(255,255,255,0.1);">
                        <div class="hero-blob"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Grid -->
    <section class="services-section py-100">
        <div class="container text-center mb-5">
            <h2 class="section-title">One Platform, <span class="text-primary">Endless Possibilities</span></h2>
            <p class="section-subtitle">Everything you need to verify, document, and transact in one place.</p>
        </div>
        <div class="container">
            <div class="row g-4">
                <!-- Identity Verification -->
                <div class="col-md-4">
                    <div class="service-card h-100">
                        <img src="{{ request()->getBaseUrl() }}/images/identity_vault.png" class="img-fluid mb-4 rounded-lg" alt="Identity Vault" loading="lazy" decoding="async" style="max-height:180px; width:100%; object-fit:cover;">
                        <h3>Identity Vault</h3>
                        <p>Instant verification for NIN, BVN, CAC, Drivers License, and International Passports with downloadable PDF reports.</p>
                        <ul class="sc-list">
                            <li><i class="fa-solid fa-check"></i> Real-time API response</li>
                            <li><i class="fa-solid fa-check"></i> Biometric matching</li>
                            <li><i class="fa-solid fa-check"></i> Verification history</li>
                        </ul>
                    </div>
                </div>
                <!-- AI Legal Hub -->
                <div class="col-md-4">
                    <div class="service-card h-100 active">
                        <img src="{{ request()->getBaseUrl() }}/images/legal_hub.png" class="img-fluid mb-4 rounded-lg" alt="AI Legal Hub" loading="lazy" decoding="async" style="max-height:180px; width:100%; object-fit:cover;">
                        <h3>AI Legal Hub</h3>
                        <p>Generate professional legal documents in seconds. NDAs, Sales Agreements, and Employment Contracts at your fingertips.</p>
                        <ul class="sc-list">
                            <li><i class="fa-solid fa-check"></i> AI-Powered Drafting</li>
                            <li><i class="fa-solid fa-check"></i> E-Stamping Ready</li>
                            <li><i class="fa-solid fa-check"></i> Legal Templates</li>
                        </ul>
                    </div>
                </div>
                <!-- Agency Banking -->
                <div class="col-md-4">
                    <div class="service-card h-100">
                        <img src="{{ request()->getBaseUrl() }}/images/finance_suite.png" class="img-fluid mb-4 rounded-lg" alt="Financial Suite" loading="lazy" decoding="async" style="max-height:180px; width:100%; object-fit:cover;">
                        <h3>Financial Suite</h3>
                        <p>Seamless agency banking, FX exchange, virtual cards, and automated invoicing for your business operations.</p>
                        <ul class="sc-list">
                            <li><i class="fa-solid fa-check"></i> Virtual USD/NGN Cards</li>
                            <li><i class="fa-solid fa-check"></i> Real-time FX Rates</li>
                            <li><i class="fa-solid fa-check"></i> Bulk Disbursements</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Admin Control Tower CTA -->
    <section class="cta-section py-80">
        <div class="container">
            <div class="cta-card glass-panel p-5 text-center">
                <div class="cta-badge mb-3">ADMIN CONTROL TOWER</div>
                <h2 class="mb-4">Are you a Business Owner?</h2>
                <p class="lead mb-5 opacity-75">Take full control of your verification providers, set custom pricing for your sub-users, and monitor all transactions from a single dashboard.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('register') }}" class="btn btn-white btn-lg px-5">Start Scaling Today</a>
                </div>
            </div>
        </div>
    </section>
</div>

@push('styles')
@php
    try {
        echo app(\Illuminate\Foundation\Vite::class)(['resources/css/welcome.css']);
    } catch (\Throwable $e) {
    }
@endphp
@endpush
@endsection
