@extends('layouts.nexus')

@section('title', 'Become a Licensed NIN Registration Agent in Nigeria | Complete Guide & Portal - ' . config('app.name'))
@section('meta_description', 'Learn how to become an accredited NIN Enrollment Agent in Nigeria with Fuwa.NG. Discover hardware requirements, registration steps, step-by-step onboarding, and high commission payouts.')
@section('meta_keywords', 'become nin registration agent nigeria, nin enrollment agent, nimc agent registration, start nin registration business, nin agent hardware, nimc license agent, fuwa ng agents')
@section('canonical', route('agent.landing'))

@section('og_title', 'Become a Licensed NIN Registration Agent in Nigeria - Fuwa.NG Agent Portal')
@section('og_description', 'Start your own profitable NIN registration agency in Nigeria. Easy onboarding, verified hardware support, fast payout, and NIMC compliance.')
@section('og_type', 'website')

@section('content')
<div class="container-fluid py-4 px-lg-5">
    
    <!-- Hero Section -->
    <div class="row align-items-center mb-5 py-lg-4 g-4">
        <div class="col-lg-7">
            <div class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill mb-3">
                <i class="fa-solid fa-id-card me-2"></i>NIMC Accredited Ecosystem Partner
            </div>
            <h1 class="display-4 text-white fw-extrabold mb-3 lh-sm">
                Become a Licensed <span class="text-primary">NIN Registration Agent</span> in Nigeria
            </h1>
            <p class="lead text-white-50 mb-4 me-lg-4">
                Launch and expand your National Identification Number (NIN) enrollment center. Join thousands of verified agents nationwide powering digital identity captured with high-speed synchronization, guaranteed commission payouts, and 24/7 dedicated support.
            </p>
            
            <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                @auth
                    @if($agent && $agent->isApproved())
                        <a href="{{ route('agent.dashboard') }}" class="btn btn-primary btn-lg rounded-pill px-4 py-3 fw-bold">
                            <i class="fa-solid fa-gauge-high me-2"></i>Go to Agent Dashboard
                        </a>
                    @elseif($agent)
                        <a href="{{ route('agent.onboarding.index') }}" class="btn btn-primary btn-lg rounded-pill px-4 py-3 fw-bold">
                            <i class="fa-solid fa-list-check me-2"></i>Complete Onboarding KYC
                        </a>
                    @else
                        <a href="{{ route('agent.register', ['type' => 'new']) }}" class="btn btn-primary btn-lg rounded-pill px-4 py-3 fw-bold">
                            <i class="fa-solid fa-user-plus me-2"></i>Register as New Agent
                        </a>
                        <a href="{{ route('agent.register', ['type' => 'existing']) }}" class="btn btn-outline-warning btn-lg rounded-pill px-4 py-3 fw-bold">
                            <i class="fa-solid fa-building-user me-2"></i>Register as Existing Agent
                        </a>
                    @endif
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg rounded-pill px-4 py-3">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Agent Login
                    </a>
                @else
                    <a href="{{ route('agent.register', ['type' => 'new']) }}" class="btn btn-primary btn-lg rounded-pill px-4 py-3 fw-bold shadow-lg">
                        <i class="fa-solid fa-user-plus me-2"></i>Register as New Agent
                    </a>
                    <a href="{{ route('agent.register', ['type' => 'existing']) }}" class="btn btn-outline-warning btn-lg rounded-pill px-4 py-3 fw-bold">
                        <i class="fa-solid fa-building-user me-2"></i>Register as Existing Agent
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg rounded-pill px-4 py-3">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Agent Login
                    </a>
                @endauth
            </div>

            <div class="d-flex align-items-center gap-4 text-white-50 pt-3 border-top border-secondary border-opacity-25">
                <div><i class="fa-solid fa-check-circle text-primary me-2"></i>Fast Approval</div>
                <div><i class="fa-solid fa-check-circle text-primary me-2"></i>Direct NIMC Sync</div>
                <div><i class="fa-solid fa-check-circle text-primary me-2"></i>Existing Machine Support</div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 rounded-4 p-4 shadow-lg" style="background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.1) !important;">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar bg-primary text-white rounded-3 p-3">
                                <i class="fa-solid fa-shield-halved fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="text-white mb-0 fw-bold">Enrollment Network</h5>
                                <small class="text-white-50">Operational Highlights</small>
                            </div>
                        </div>
                        <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-3 py-2">Active Network</span>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                                <small class="text-white-50 d-block mb-1">Instant Payouts</small>
                                <h4 class="text-white fw-bold mb-0">₦500+ <small class="fs-6 text-primary">/enrollment</small></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                                <small class="text-white-50 d-block mb-1">Network Status</small>
                                <h4 class="text-success fw-bold mb-0">99.9% <small class="fs-6 text-white-50">Uptime</small></h4>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 rounded-3 mb-3" style="background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.2);">
                        <div class="d-flex align-items-start gap-3">
                            <i class="fa-solid fa-bolt text-primary fa-lg mt-1"></i>
                            <div>
                                <h6 class="text-white mb-1 fw-bold">Fast-Track Existing Agents</h6>
                                <p class="text-white-50 small mb-0">Already have an agent code or biometric enrollment kit? Onboard in less than 5 minutes!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Stats Banner -->
    <div class="row g-4 mb-5">
        <div class="col-md-3 col-6">
            <div class="card border-0 rounded-4 p-4 text-center" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
                <h2 class="display-6 text-primary fw-bold mb-1">36+</h2>
                <p class="text-white-50 mb-0 small text-uppercase tracking-wider">States Covered</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 rounded-4 p-4 text-center" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
                <h2 class="display-6 text-primary fw-bold mb-1">500k+</h2>
                <p class="text-white-50 mb-0 small text-uppercase tracking-wider">NINs Generated</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 rounded-4 p-4 text-center" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
                <h2 class="display-6 text-primary fw-bold mb-1">24 Hours</h2>
                <p class="text-white-50 mb-0 small text-uppercase tracking-wider">Fast Accreditation</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 rounded-4 p-4 text-center" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
                <h2 class="display-6 text-primary fw-bold mb-1">100%</h2>
                <p class="text-white-50 mb-0 small text-uppercase tracking-wider">NIMC Compliant</p>
            </div>
        </div>
    </div>

    <!-- Requirements Section -->
    <div class="row mb-5 py-4 align-items-center g-4">
        <div class="col-lg-5">
            <span class="text-primary fw-bold text-uppercase tracking-wider">Prerequisites & Eligibility</span>
            <h2 class="text-white fw-bold display-6 mt-2 mb-3">Requirements to Become an NIN Agent in Nigeria</h2>
            <p class="text-white-50 mb-4">
                Starting a National Identification Number enrollment business requires minimal setup. Whether you are an existing business owner, Cyber Café operator, or entrepreneur, here is what you need:
            </p>

            <div class="d-flex flex-column gap-3">
                <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: rgba(255,255,255,0.02);">
                    <div class="bg-primary bg-opacity-25 text-primary rounded-circle p-2 mt-1">
                        <i class="fa-solid fa-address-card fa-fw"></i>
                    </div>
                    <div>
                        <h6 class="text-white mb-1 fw-bold">1. Personal Identity & KYC Documents</h6>
                        <p class="text-white-50 small mb-0">Valid 11-digit NIN, BVN, proof of address (Utility Bill), and a passport photograph.</p>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: rgba(255,255,255,0.02);">
                    <div class="bg-primary bg-opacity-25 text-primary rounded-circle p-2 mt-1">
                        <i class="fa-solid fa-laptop-code fa-fw"></i>
                    </div>
                    <div>
                        <h6 class="text-white mb-1 fw-bold">2. Biometric Hardware / Machine</h6>
                        <p class="text-white-50 small mb-0">A Windows Laptop/PC, NIMC-compliant Biometric Fingerprint Scanner (e.g. Digital Persona / Mantra), and ICAO compliant Web Camera.</p>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: rgba(255,255,255,0.02);">
                    <div class="bg-primary bg-opacity-25 text-primary rounded-circle p-2 mt-1">
                        <i class="fa-solid fa-building fa-fw"></i>
                    </div>
                    <div>
                        <h6 class="text-white mb-1 fw-bold">3. Physical Office / Shop Location</h6>
                        <p class="text-white-50 small mb-0">A clean, accessible physical space or shop location to attend to applicants in your area.</p>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: rgba(255,255,255,0.02);">
                    <div class="bg-primary bg-opacity-25 text-primary rounded-circle p-2 mt-1">
                        <i class="fa-solid fa-wifi fa-fw"></i>
                    </div>
                    <div>
                        <h6 class="text-white mb-1 fw-bold">4. Reliable Internet Connectivity</h6>
                        <p class="text-white-50 small mb-0">Stable internet connection for real-time biometric packet uploads and verification checks.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 rounded-4 p-4 p-lg-5" style="background: linear-gradient(145deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9)); border: 1px solid rgba(255,255,255,0.1) !important;">
                <h3 class="text-white fw-bold mb-4">Step-by-Step Onboarding Process</h3>

                <div class="timeline position-relative ps-4">
                    <div class="timeline-item mb-4 position-relative">
                        <span class="badge bg-primary rounded-circle position-absolute start-0 top-0 translate-middle-x p-2" style="margin-left: -24px;">1</span>
                        <h5 class="text-white fw-bold mb-1">Create Your Fuwa.NG Account</h5>
                        <p class="text-white-50 small mb-0">Register with your full name, phone number, and email address to open your agent workspace.</p>
                    </div>

                    <div class="timeline-item mb-4 position-relative">
                        <span class="badge bg-primary rounded-circle position-absolute start-0 top-0 translate-middle-x p-2" style="margin-left: -24px;">2</span>
                        <h5 class="text-white fw-bold mb-1">Submit Basic Agent Details</h5>
                        <p class="text-white-50 small mb-0">Provide your BVN, NIN, machine details (IMEI / serial), and location address for instant verification.</p>
                    </div>

                    <div class="timeline-item mb-4 position-relative">
                        <span class="badge bg-primary rounded-circle position-absolute start-0 top-0 translate-middle-x p-2" style="margin-left: -24px;">3</span>
                        <h5 class="text-white fw-bold mb-1">Upload Agency KYC Documents</h5>
                        <p class="text-white-50 small mb-0">Upload a recent utility bill, passport photo, and optional CAC business registration document.</p>
                    </div>

                    <div class="timeline-item position-relative">
                        <span class="badge bg-success rounded-circle position-absolute start-0 top-0 translate-middle-x p-2" style="margin-left: -24px;">4</span>
                        <h5 class="text-white fw-bold mb-1">Get Approved & Start Enrolling</h5>
                        <p class="text-white-50 small mb-0">Once verified, access the agent portal, generate NIN slips, execute modifications, and earn daily commissions.</p>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 text-center">
                    <a href="{{ route('agent.register') }}" class="btn btn-primary rounded-pill px-5 py-3 fw-bold">
                        Start Your Registration Now <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- SEO Content & Comprehensive Guide Section -->
    <div class="row justify-content-center mb-5">
        <div class="col-lg-10">
            <div class="card border-0 rounded-4 p-4 p-lg-5" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
                
                <article class="text-white-50 lh-lg">
                    <h2 class="text-white fw-bold h3 mb-4">Why Start an NIN Registration Business in Nigeria?</h2>
                    
                    <p>
                        The demand for National Identification Number (NIN) registration and modification services across Nigeria has reached an all-time high. With mandatory NIN-SIM linking, bank account verification (BVN-NIN integration), passport applications, and voter registration, millions of Nigerians require identity enrollment services daily.
                    </p>

                    <p>
                        Becoming a recognized <strong>Enrollment Agent in Nigeria</strong> through Fuwa.NG gives you a lucrative opportunity to serve your community while building a sustainable income stream. As a licensed agent, you can perform new NIN enrollments, NIN modifications (date of birth, change of name, phone number update), and instant NIN slip prints.
                    </p>

                    <h3 class="text-white fw-bold h4 mt-5 mb-3">Key Benefits of Joining the Fuwa.NG Agent Network</h3>

                    <div class="row g-4 my-2">
                        <div class="col-md-6">
                            <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                                <h5 class="text-white fw-bold"><i class="fa-solid fa-sack-dollar text-primary me-2"></i>High Profit Margins</h5>
                                <p class="small mb-0">Earn generous commission payouts per successful enrollment and instant slip print service.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                                <h5 class="text-white fw-bold"><i class="fa-solid fa-headset text-primary me-2"></i>Dedicated Support Team</h5>
                                <p class="small mb-0">Get direct access to our 24/7 technical team to resolve ticket issues or server sync delays.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                                <h5 class="text-white fw-bold"><i class="fa-solid fa-trophy text-primary me-2"></i>Monthly Agent Rewards</h5>
                                <p class="small mb-0">Compete in our Most Valuable Agent (MVA) monthly leaderboard for cash bonuses and free hardware upgrades.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                                <h5 class="text-white fw-bold"><i class="fa-solid fa-shield-cat text-primary me-2"></i>Automated Non-Blocking Verification</h5>
                                <p class="small mb-0">Our smart gateway guarantees non-blocking NIN checks even during central NIMC maintenance windows.</p>
                            </div>
                        </div>
                    </div>

                    <h3 class="text-white fw-bold h4 mt-5 mb-3">Frequently Asked Questions (FAQ)</h3>

                    <div class="accordion accordion-flush" id="agentFaqAccordion">
                        <div class="accordion-item bg-transparent border-secondary border-opacity-25 mb-2 rounded-3" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08) !important;">
                            <h2 class="accordion-header mb-0">
                                <button class="accordion-button collapsed bg-transparent text-white fw-bold w-100 text-start p-3 d-flex justify-content-between align-items-center" type="button" data-toggle="collapse" data-bs-toggle="collapse" data-target="#faq1" data-bs-target="#faq1" style="border: 0; outline: none; box-shadow: none;">
                                    <span>How long does the agent approval process take?</span>
                                    <i class="fa-solid fa-chevron-down ms-2 small opacity-75"></i>
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-parent="#agentFaqAccordion" data-bs-parent="#agentFaqAccordion">
                                <div class="accordion-body text-white-50 px-3 pb-3">
                                    Agent applications are reviewed within 24 to 48 hours. If you are an existing agent with a registered company agent code, your account is fast-tracked for instant access!
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item bg-transparent border-secondary border-opacity-25 mb-2 rounded-3" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08) !important;">
                            <h2 class="accordion-header mb-0">
                                <button class="accordion-button collapsed bg-transparent text-white fw-bold w-100 text-start p-3 d-flex justify-content-between align-items-center" type="button" data-toggle="collapse" data-bs-toggle="collapse" data-target="#faq2" data-bs-target="#faq2" style="border: 0; outline: none; box-shadow: none;">
                                    <span>Can I use my existing biometric laptop or machine?</span>
                                    <i class="fa-solid fa-chevron-down ms-2 small opacity-75"></i>
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-parent="#agentFaqAccordion" data-bs-parent="#agentFaqAccordion">
                                <div class="accordion-body text-white-50 px-3 pb-3">
                                    Yes! You can connect your existing NIMC-compliant biometric scanner and laptop. Simply input your machine IMEI or serial number during registration.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item bg-transparent border-secondary border-opacity-25 mb-2 rounded-3" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08) !important;">
                            <h2 class="accordion-header mb-0">
                                <button class="accordion-button collapsed bg-transparent text-white fw-bold w-100 text-start p-3 d-flex justify-content-between align-items-center" type="button" data-toggle="collapse" data-bs-toggle="collapse" data-target="#faq3" data-bs-target="#faq3" style="border: 0; outline: none; box-shadow: none;">
                                    <span>Is there a fee to register as a Fuwa.NG Enrollment Agent?</span>
                                    <i class="fa-solid fa-chevron-down ms-2 small opacity-75"></i>
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-parent="#agentFaqAccordion" data-bs-parent="#agentFaqAccordion">
                                <div class="accordion-body text-white-50 px-3 pb-3">
                                    Registration is free to get started. You only top up your wallet balance to cover processing fees per enrollment or modification request.
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

            </div>
        </div>
    </div>

    <!-- Final Call to Action CTA -->
    <div class="row justify-content-center text-center py-4">
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 p-5 shadow-lg position-relative overflow-hidden" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                <div class="position-relative z-1">
                    <h2 class="text-white fw-extrabold display-6 mb-3">Ready to Start Your NIN Registration Center?</h2>
                    <p class="text-white-50 lead mb-4">Join Nigeria's fastest growing identity verification network today.</p>
                    
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="{{ route('agent.register', ['type' => 'new']) }}" class="btn btn-light text-primary btn-lg rounded-pill px-4 py-3 fw-bold">
                            <i class="fa-solid fa-user-plus me-2"></i>Register as New Agent
                        </a>
                        <a href="{{ route('agent.register', ['type' => 'existing']) }}" class="btn btn-outline-light btn-lg rounded-pill px-4 py-3 fw-bold">
                            <i class="fa-solid fa-building-user me-2"></i>Register as Existing Agent
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg rounded-pill px-4 py-3">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>Sign In to Portal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
