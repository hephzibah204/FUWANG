@extends('layouts.nexus')

@section('title', 'Become a Licensed NIN Registration Agent in Nigeria | Complete Guide & Portal - ' . config('app.name'))
@section('meta_description', 'Learn how to become an accredited NIN Enrollment Agent in Nigeria with Fuwa.NG. Discover hardware requirements, registration steps, and step-by-step onboarding.')
@section('meta_keywords', 'become nin registration agent nigeria, nin enrollment agent, nimc agent registration, start nin registration business, nin agent hardware, nimc license agent, fuwa ng agents')
@section('canonical', route('agent.landing'))

@section('og_title', 'Become a Licensed NIN Registration Agent in Nigeria - Fuwa.NG Agent Portal')
@section('og_description', 'Start your own profitable NIN registration agency in Nigeria. Easy onboarding, verified hardware support, and NIMC compliance.')
@section('og_type', 'website')

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
</style>

<div class="hero-section py-5">
    <div class="container-fluid px-lg-5">
        
        <!-- Hero Section -->
        <div class="row align-items-center mb-5 py-lg-4 g-5">
            <div class="col-lg-7">
                <div class="d-inline-flex align-items-center badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill mb-4 shadow-sm">
                    <span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true" style="animation-duration: 2s;"></span>
                    <i class="fa-solid fa-id-card me-2 d-none"></i> NIMC Accredited Ecosystem Partner
                </div>
                <h1 class="display-4 text-white fw-extrabold mb-4 lh-sm">
                    Build a Profitable <br>
                    <span class="gradient-text">NIN Enrollment Center</span> <br>
                    in Nigeria.
                </h1>
                <p class="lead text-white-50 mb-5 me-lg-4" style="max-width: 600px;">
                    Join thousands of verified agents powering Nigeria's digital identity network. Enjoy instant approvals, high-speed biometric synchronization, and unparalleled 24/7 technical support.
                </p>
                
                <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                    @auth
                        @if( && ->isApproved())
                            <a href="{{ route('agent.dashboard') }}" class="btn btn-primary btn-lg rounded-pill px-4 py-3 fw-bold hover-translate-up shadow-lg">
                                <i class="fa-solid fa-gauge-high me-2"></i>Enter Workspace
                            </a>
                        @elseif()
                            <a href="{{ route('agent.onboarding.index') }}" class="btn btn-primary btn-lg rounded-pill px-4 py-3 fw-bold hover-translate-up shadow-lg">
                                <i class="fa-solid fa-list-check me-2"></i>Resume Onboarding
                            </a>
                        @else
                            <a href="{{ route('agent.register', ['type' => 'new']) }}" class="btn btn-primary btn-lg rounded-pill px-4 py-3 fw-bold hover-translate-up shadow-lg">
                                <i class="fa-solid fa-user-plus me-2"></i>Register as New Agent
                            </a>
                            <a href="{{ route('agent.register', ['type' => 'existing']) }}" class="btn btn-outline-warning btn-lg rounded-pill px-4 py-3 fw-bold hover-translate-up">
                                <i class="fa-solid fa-building-user me-2"></i>Claim Existing Profile
                            </a>
                        @endif
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg rounded-pill px-4 py-3 hover-translate-up">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>Agent Login
                        </a>
                    @else
                        <a href="{{ route('agent.register', ['type' => 'new']) }}" class="btn btn-primary btn-lg rounded-pill px-4 py-3 fw-bold hover-translate-up shadow-lg">
                            <i class="fa-solid fa-user-plus me-2"></i>Start Enrollment Business
                        </a>
                        <a href="{{ route('agent.register', ['type' => 'existing']) }}" class="btn btn-outline-warning btn-lg rounded-pill px-4 py-3 fw-bold hover-translate-up">
                            <i class="fa-solid fa-building-user me-2"></i>Claim Existing Profile
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg rounded-pill px-4 py-3 hover-translate-up">
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
            <div class="col-md-3 col-6">
                <div class="glass-card p-4 text-center text-lg-start d-flex flex-column flex-lg-row align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary fs-3">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <h3 class="text-white fw-bold mb-0">15,000+</h3>
                        <p class="text-white-50 small mb-0 text-uppercase tracking-wider">Active Agents</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="glass-card p-4 text-center text-lg-start d-flex flex-column flex-lg-row align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success fs-3">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <div>
                        <h3 class="text-white fw-bold mb-0">2M+</h3>
                        <p class="text-white-50 small mb-0 text-uppercase tracking-wider">Slips Printed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="glass-card p-4 text-center text-lg-start d-flex flex-column flex-lg-row align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning fs-3">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div>
                        <h3 class="text-white fw-bold mb-0">99.9%</h3>
                        <p class="text-white-50 small mb-0 text-uppercase tracking-wider">API Uptime</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="glass-card p-4 text-center text-lg-start d-flex flex-column flex-lg-row align-items-center gap-3">
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
                        
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <a href="{{ route('agent.register', ['type' => 'new']) }}" class="btn btn-light text-primary btn-lg rounded-pill px-5 py-3 fw-bold hover-translate-up shadow">
                                <i class="fa-solid fa-user-plus me-2"></i>Create Agent Account
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg rounded-pill px-5 py-3 fw-bold hover-translate-up">
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
