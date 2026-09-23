@extends('layouts.nexus')

@section('title', 'Dashboard | ' . config('app.name'))

@section('content')
@include('dashboard_styles')
<div class="nexus-dashboard">
    <!-- Premium Welcome Hero -->
    <div class="welcome-hero mb-4" data-step="1" data-intro="Welcome to your Dashboard! This is your main hub for all activities.">
        <div class="hero-bg-accent"></div>
        <div class="row align-items-center position-relative">
            <div class="col-lg-7 mb-4 mb-lg-0">
                <div class="hero-welcome-text">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge badge-primary px-3 py-2" style="border-radius: 10px; background: rgba(59, 130, 246, 0.15); font-weight: 600;">
                            <i class="fa-solid fa-crown mr-1 text-warning"></i> {{ (Auth::user()->role ?? 'user') === 'admin' ? 'Administrator' : 'Standard Member' }}
                        </span>
                        @if(!empty($hasTier2Kyc))
                            <span class="kyc-status-badge kyc-verified">
                                <i class="fa-solid fa-circle-check"></i> Tier 2 Verified
                            </span>
                        @else
                            <a href="{{ route('account.kyc.nin') }}" class="kyc-status-badge kyc-unverified text-decoration-none">
                                <i class="fa-solid fa-triangle-exclamation"></i> Tier 1 (Verify KYC)
                            </a>
                        @endif
                    </div>
                    
                    <h1 class="display-4 font-weight-bold mb-2">Hello, {{ explode(' ', Auth::user()->fullname)[0] ?? Auth::user()->username }}!</h1>
                    <p class="text-white-50 lead mb-4" style="font-size: 1.05rem;">Manage your identity verifications and digital services from your secure command center.</p>
                    
                    <div class="hero-stats-row">
                        <x-nexus.stat-card label="Total Verifications" :value="number_format($verificationCount ?? 0)" icon="fa-fingerprint" />
                        <x-nexus.stat-card label="Member Since" :value="Auth::user()->created_at ? Auth::user()->created_at->format('M Y') : 'Recent'" icon="fa-calendar-check" />
                        <x-nexus.stat-card label="Referral ID" :value="Auth::user()->referral_id" icon="fa-id-badge" />
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="hero-wallet-card" data-step="2" data-intro="This is your wallet. You can fund it to start using our services.">
                    <div class="hw-glow"></div>
                    <div class="hw-content">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <span class="text-white-50 small font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Wallet Balance</span>
                                <button type="button" class="balance-toggle-btn" id="balanceToggleBtn" title="Toggle balance visibility" onclick="toggleBalanceVisibility()">
                                    <i class="fa-solid fa-eye" id="balanceToggleIcon"></i>
                                </button>
                            </div>
                            <i class="fa-solid fa-wallet text-white-50" style="font-size: 1.25rem;"></i>
                        </div>
                        <div class="hw-amount mb-4" id="walletBalanceDisplay" data-actual-balance="₦{{ number_format($balance, 2) }}">
                            ₦{{ number_format($balance, 2) }}
                        </div>
                        <div class="hw-actions d-flex gap-2">
                            <button class="btn btn-primary flex-grow-1 font-weight-bold py-2" data-toggle="modal" data-target="#fundWalletModal" style="border-radius: 12px; background: #ffffff; color: #1e40af; border: none;">
                                <i class="fa-solid fa-plus-circle mr-1"></i> Fund Wallet
                            </button>
                            <a href="{{ route('wallet.fund') }}" class="btn btn-glass px-3 d-flex align-items-center justify-content-center" title="Virtual Accounts & Transfers">
                                <i class="fa-solid fa-building-columns mr-1"></i>
                                <span class="small font-weight-bold">Accounts</span>
                            </a>
                            <a href="{{ route('history') }}" class="btn btn-glass px-3 d-flex align-items-center justify-content-center" title="History & Ledger">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Virtual Accounts Live Section -->
    <div class="dashboard-section mb-4" data-step="3" data-intro="Here you can see your virtual accounts for easy wallet funding.">
        <div class="panel-card">
            <div class="panel-hdr mb-3">
                <div class="d-flex align-items-center">
                    <div class="section-icon lifestyle mr-3" style="width: 40px; height: 40px; border-radius: 10px; background: rgba(139, 92, 246, 0.15); color: #a78bfa; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <div>
                        <h2 class="h6 font-weight-bold m-0 text-white">Dedicated Virtual Accounts</h2>
                        <p class="small text-muted m-0">Transfer funds to any assigned account below for instant wallet crediting</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light" onclick="refreshVirtualAccounts()" title="Refresh Accounts">
                        <i class="fa-solid fa-rotate mr-1"></i> Refresh
                    </button>
                    <a href="{{ route('wallet.fund') }}" class="small text-primary font-weight-bold ml-2">Manage All</a>
                </div>
            </div>
            
            <div id="dashboardVirtualAccounts">
                <x-nexus.skeleton-loader count="2" type="card" />
            </div>
        </div>
    </div>

    <!-- Search & Category Filter Navigation Bar -->
    <div class="service-filter-bar mb-3">
        <div class="service-search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="serviceSearchInput" placeholder="Search services (e.g., NIN, BVN, Airtime, CAC)..." onkeyup="filterServices()">
        </div>
        <div class="category-pills">
            <div class="cat-pill active" data-filter="all" onclick="selectServiceCategory('all', this)">
                <i class="fa-solid fa-grid-2 mr-1"></i> All Services
            </div>
            <div class="cat-pill" data-filter="identity" onclick="selectServiceCategory('identity', this)">
                <i class="fa-solid fa-id-card mr-1"></i> Identity & KYC
            </div>
            <div class="cat-pill" data-filter="verification" onclick="selectServiceCategory('verification', this)">
                <i class="fa-solid fa-shield-halved mr-1"></i> Corporate & Verification
            </div>
            <div class="cat-pill" data-filter="lifestyle" onclick="selectServiceCategory('lifestyle', this)">
                <i class="fa-solid fa-bolt mr-1"></i> Ecosystem & Lifestyle
            </div>
        </div>
    </div>

    <!-- Quick Services: Identity Proofing -->
    <div class="dashboard-section service-group mb-4" data-category="identity" data-step="4" data-intro="This section provides quick access to our identity verification services.">
        <div class="section-hdr mb-3">
            <div class="d-flex align-items-center">
                <div class="section-icon identity mr-3" style="width: 36px; height: 36px; border-radius: 10px; background: rgba(16, 185, 129, 0.15); color: #34d399; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <div>
                    <h2 class="h6 font-weight-bold m-0 text-white">Identity Proofing</h2>
                    <p class="small text-muted m-0">Core government & national ID verification suites</p>
                </div>
            </div>
        </div>
        <div class="quick-grid">
            @if(\App\Models\SystemSetting::get('nin_service_enabled', 'true') === 'true')
                <x-nexus.service-card title="NIN Suite" :href="route('services.nin.suite')" icon="fa-id-card-clip" iconVariant="solid" style="border: 1px solid rgba(59, 130, 246, 0.4);" />
            @endif

            @if(\App\Models\SystemSetting::get('bvn_service_enabled', 'true') === 'true')
                <x-nexus.service-card title="BVN Suite" :href="route('services.bvn')" icon="fa-building-columns" iconVariant="solid" />
                <x-nexus.service-card title="Print BVN" :href="route('services.bvn')" icon="fa-print" iconVariant="solid" />
            @endif

            <x-nexus.service-card title="Voters Card" :href="route('services.voters_card')" icon="fa-box-archive" iconVariant="solid" />
            <x-nexus.service-card title="Passport" :href="route('services.passport')" icon="fa-passport" iconVariant="solid" />
            <x-nexus.service-card title="DL Verify" :href="route('services.drivers_license')" icon="fa-car" iconVariant="solid" />
        </div>
    </div>

    <!-- Quick Services: Corporate & Verification Hub -->
    <div class="dashboard-section service-group mb-4" data-category="verification">
        <div class="section-hdr mb-3">
            <div class="d-flex align-items-center">
                <div class="section-icon verification mr-3" style="width: 36px; height: 36px; border-radius: 10px; background: rgba(59, 130, 246, 0.15); color: #60a5fa; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h2 class="h6 font-weight-bold m-0 text-white">Corporate & Verification Hub</h2>
                    <p class="small text-muted m-0">Enterprise validation, biometrics and address authentication</p>
                </div>
            </div>
        </div>
        <div class="quick-grid">
            <x-nexus.service-card title="CAC Verify" :href="route('services.cac_verify')" icon="fa-briefcase" iconVariant="solid" />
            <x-nexus.service-card title="TIN Verify" :href="route('services.tin_verify')" icon="fa-percent" iconVariant="solid" />
            <x-nexus.service-card title="Bio Verify" :href="route('services.biometric_verify')" icon="fa-fingerprint" iconVariant="solid" />
            <x-nexus.service-card title="Address" :href="route('services.address_verify')" icon="fa-location-dot" iconVariant="solid" />
            <x-nexus.service-card title="Plate No." :href="route('services.plate_number')" icon="fa-car-rear" iconVariant="solid" />
        </div>
    </div>

    <!-- Quick Services: Ecosystem & Lifestyle -->
    <div class="dashboard-section service-group mb-4" data-category="lifestyle">
        <div class="section-hdr mb-3">
            <div class="d-flex align-items-center">
                <div class="section-icon lifestyle mr-3" style="width: 36px; height: 36px; border-radius: 10px; background: rgba(139, 92, 246, 0.15); color: #a78bfa; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <div>
                    <h2 class="h6 font-weight-bold m-0 text-white">Ecosystem & Lifestyle</h2>
                    <p class="small text-muted m-0">Utilities, business management and lifestyle services</p>
                </div>
            </div>
            <a href="{{ route('services.price_list') }}" class="small text-primary font-weight-bold">View Prices</a>
        </div>
        <div class="quick-grid">
            @if(\App\Models\SystemSetting::get('airtime_data_enabled', 'true') === 'true')
                <x-nexus.service-card title="VTU Hub" :href="route('services.vtu.hub')" icon="fa-mobile-screen-button" iconVariant="solid" badge="TOP-UP" badgeColor="var(--clr-accent-2)" />
            @endif

            @if(\App\Models\SystemSetting::get('legal_service_enabled', 'true') === 'true')
                <x-nexus.service-card title="Legal Hub" :href="route('services.legal-hub')" icon="fa-gavel" iconVariant="solid" />
                <x-nexus.service-card title="Notary" :href="route('services.notary')" icon="fa-file-signature" iconVariant="solid" />
            @endif

            <x-nexus.service-card title="Agency" :href="route('services.agency')" icon="fa-shop" iconVariant="solid" />
            <x-nexus.service-card title="Cards" :href="route('services.virtual_card')" icon="fa-credit-card" iconVariant="solid" />
            <x-nexus.service-card title="Tickets" :href="route('services.ticketing')" icon="fa-ticket" iconVariant="solid" />
            
            @if(\App\Models\SystemSetting::get('auction_service_enabled', 'true') === 'true')
                @php
                    $auctionRoute = \Illuminate\Support\Facades\Route::has('services.auctions.dashboard')
                        ? 'services.auctions.dashboard'
                        : (\Illuminate\Support\Facades\Route::has('auctions.dashboard') ? 'auctions.dashboard' : null);
                    $aucHref = $auctionRoute ? route($auctionRoute) : route('public.auctions.index');
                @endphp
                <x-nexus.service-card title="Auctions" :href="$aucHref" icon="fa-gavel" iconVariant="solid" />
            @endif

            @php
                $logisticsRoute = \Illuminate\Support\Facades\Route::has('services.user.logistics.dashboard')
                    ? 'services.user.logistics.dashboard'
                    : (\Illuminate\Support\Facades\Route::has('user.logistics.dashboard') ? 'user.logistics.dashboard' : null);
                $logHref = $logisticsRoute ? route($logisticsRoute) : route('logistics.home');
            @endphp
            <x-nexus.service-card title="Logistics" :href="$logHref" icon="fa-truck-fast" iconVariant="solid" />
        </div>
    </div>

    <!-- Bottom Layout: Recent Operations & Engagement Sidebar -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <!-- Recent Transactions Panel -->
            <div class="panel-card h-100" data-step="5" data-intro="Track your recent operations and transactions here.">
                <div class="panel-hdr">
                    <div>
                        <h3 class="h6 font-weight-bold m-0 text-white"><i class="fa-solid fa-receipt mr-2 text-primary"></i> Recent Operations</h3>
                        <p class="small text-muted m-0">Live log of your recent transactions and service orders</p>
                    </div>
                    <a href="{{ route('history') }}" class="small text-primary font-weight-bold">View Ledger <i class="fa-solid fa-arrow-right ml-1"></i></a>
                </div>
                <div id="recentActivity" style="min-height: 280px;">
                    <!-- Transactions loaded via AJAX -->
                    <div class="d-flex justify-content-center align-items-center p-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <!-- System Stats & Referrals -->
            <div class="d-flex flex-column gap-3 h-100">
                <div class="panel-card p-3">
                    <div class="panel-hdr mb-3">
                        <h3 class="h6 font-weight-bold m-0 text-white"><i class="fa-solid fa-chart-pie mr-2 text-warning"></i> Engagement</h3>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <div class="mini-stat-item">
                            <i class="fa-solid fa-coins text-warning"></i>
                            <div class="flex-grow-1">
                                <div class="small text-muted" style="font-size: 0.75rem;">Referral Earnings</div>
                                <div class="font-weight-bold" style="color: #f8fafc;">₦{{ number_format($referralStats['earnings'] ?? 0, 2) }}</div>
                            </div>
                            <div class="small text-success font-weight-bold">Paid</div>
                        </div>
                        <div class="mini-stat-item">
                            <i class="fa-solid fa-users text-primary"></i>
                            <div class="flex-grow-1">
                                <div class="small text-muted" style="font-size: 0.75rem;">Active Referrals</div>
                                <div class="font-weight-bold" style="color: #f8fafc;">{{ number_format($referralStats['funded'] ?? 0) }} Users</div>
                            </div>
                            <div class="small"><a href="{{ route('referrals.index') }}" class="text-primary font-weight-bold">View</a></div>
                        </div>
                    </div>
                </div>

                <div class="panel-card p-3 flex-grow-1 d-flex flex-column">
                    <div class="panel-hdr mb-2">
                        <h3 class="h6 font-weight-bold m-0 text-white"><i class="fa-solid fa-bullhorn mr-2 text-info"></i> Notice Board</h3>
                    </div>
                    @if($notification)
                        <div class="broadcast-banner p-3 rounded-lg mb-3" style="background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 12px;">
                            <div class="d-flex gap-2">
                                <i class="fa-solid fa-circle-info text-primary mt-1"></i>
                                <div>
                                    <div class="small font-weight-bold text-white mb-1">Platform Notice</div>
                                    <p class="small text-white-50 mb-0" style="line-height: 1.4;">{{ $notification }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-3 opacity-50">
                            <i class="fa-solid fa-inbox mb-1" style="font-size: 1.3rem;"></i>
                            <p class="small m-0 text-muted">No new announcements</p>
                        </div>
                    @endif
                    
                    <div class="p-3 rounded-lg mt-auto" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.12), rgba(59, 130, 246, 0.08)); border: 1px solid rgba(139, 92, 246, 0.25); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small font-weight-bold text-white">Invite & Earn 10%</span>
                            <span class="badge badge-pill badge-primary px-2" style="font-size: 0.65rem;">Reward</span>
                        </div>
                        <p class="small text-muted mb-2" style="font-size: 0.75rem;">Share your unique link and earn commissions on every verification funded.</p>
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <code class="text-primary font-weight-bold px-2 py-1 rounded" style="font-size: 0.95rem; background: rgba(0,0,0,0.3);">{{ Auth::user()->referral_id }}</code>
                            <button class="btn btn-sm btn-primary py-1 px-3 ref-copy" data-referral-url="{{ url('/register?ref=' . urlencode((string) Auth::user()->referral_id)) }}" style="border-radius: 8px; font-weight: 600;">
                                <i class="fa-solid fa-copy mr-1"></i> Copy
                            </button>
                        </div>
                        <a href="{{ route('referrals.index') }}" class="btn btn-sm btn-block btn-glass py-2" style="border-radius: 8px; font-size: 0.75rem; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-chart-line mr-1"></i> VIEW DETAILED ANALYTICS
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fund Wallet Modal -->
<div class="modal fade" id="fundWalletModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="background: rgba(15, 23, 42, 0.98); border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center">
                    <div class="section-icon mr-3" style="width: 36px; height: 36px; border-radius: 10px; background: rgba(59, 130, 246, 0.2); color: #60a5fa; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold text-white mb-0">Fund Your Wallet</h5>
                        <p class="small text-muted m-0">Instant & secure account crediting</p>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                
                <!-- Step 1: Direct Amount Entry -->
                <div id="fundingStepDirect">
                    <p class="text-white-50 small mb-3">Enter the amount you wish to add to your wallet balance (Minimum ₦100).</p>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-white-50">Amount (₦)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-dark border-0 text-white font-weight-bold">₦</span>
                            </div>
                            <input type="number" id="dc-amount" class="form-control text-white font-weight-bold" placeholder="1000" min="100" value="1000" style="font-size: 1.15rem; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);">
                        </div>
                    </div>
                    
                    <!-- Quick Amount Pills -->
                    <div class="d-flex gap-2 mb-4 flex-wrap">
                        <button type="button" class="btn btn-sm btn-glass py-1 px-3" onclick="$('#dc-amount').val(1000)">₦1,000</button>
                        <button type="button" class="btn btn-sm btn-glass py-1 px-3" onclick="$('#dc-amount').val(2500)">₦2,500</button>
                        <button type="button" class="btn btn-sm btn-glass py-1 px-3" onclick="$('#dc-amount').val(5000)">₦5,000</button>
                        <button type="button" class="btn btn-sm btn-glass py-1 px-3" onclick="$('#dc-amount').val(10000)">₦10,000</button>
                    </div>

                    <button type="button" class="btn btn-primary w-100 py-3 mb-3 font-weight-bold" onclick="initiateDirectCheckout()" style="border-radius: 12px; font-size: 1rem;">
                        <i class="fa-solid fa-lock mr-2"></i> Pay with Card / USSD / Transfer
                    </button>
                    
                    <div class="text-center">
                        <button class="btn btn-link text-white-50 p-0 small" onclick="showOtherFundingOptions()" style="text-decoration: underline;">
                            <i class="fa-solid fa-building-columns mr-1"></i> Use Dedicated Virtual Accounts or Manual Bank Transfer
                        </button>
                    </div>
                </div>

                <!-- Step 2: Alternative Options Selection -->
                <div id="fundingStep1" style="display: none;">
                    <button class="btn btn-sm btn-link text-white-50 p-0 mb-3" onclick="backToAmountEntry()">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Back to Card / Direct Pay
                    </button>
                    <p class="text-muted small mb-3">Choose an alternative funding method below:</p>
                    <div class="d-grid gap-3">
                        <button class="btn btn-outline-primary w-100 py-3 mb-2 text-left d-flex align-items-center" onclick="showAutoFundingAccounts()" style="border-radius: 12px; background: rgba(59, 130, 246, 0.05);">
                            <i class="fa-solid fa-robot mr-3 text-primary" style="font-size: 1.4rem;"></i>
                            <div>
                                <div class="font-weight-bold text-white">Instant Auto Dedicated Accounts</div>
                                <div class="small text-muted">Wema, PalmPay, Sterling bank transfers</div>
                            </div>
                        </button>
                        <button class="btn btn-outline-light w-100 py-3 text-left d-flex align-items-center" onclick="showBankDetails()" style="border-radius: 12px; background: rgba(255, 255, 255, 0.03);">
                            <i class="fa-solid fa-university mr-3 text-white-50" style="font-size: 1.4rem;"></i>
                            <div>
                                <div class="font-weight-bold text-white">Manual Bank Transfer</div>
                                <div class="small text-muted">Upload proof of payment for manual approval</div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Step 3: Instant Dedicated Accounts -->
                <div id="fundingStepAuto" style="display: none;">
                    <button class="btn btn-sm btn-link text-white-50 p-0 mb-3" onclick="backToFundingStep1()">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Back
                    </button>

                    <div class="alert alert-info py-2 px-3 mb-3" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); color: #93c5fd; border-radius: 10px;">
                        <div class="small"><i class="fa-solid fa-circle-info mr-1"></i> Transfer funds to any of the accounts below. Your wallet will credit automatically within seconds.</div>
                    </div>

                    @php
                        $hasTier2IdentityForFunding = \App\Support\UserKycIdentifiers::preferredPaymentIdentity(auth()->user()) !== null;
                    @endphp
                    @if (! $hasTier2IdentityForFunding)
                        <div class="alert py-2 px-3 small mb-3" role="note" style="background: rgba(234, 179, 8, 0.08); border: 1px solid rgba(234, 179, 8, 0.28); color: #fde68a; border-radius: 10px;">
                            <i class="fa-solid fa-id-card mr-1"></i>
                            <strong>Regulatory Notice:</strong> Dedicated virtual accounts require a <strong>verified account KYC BVN or NIN</strong>.
                            <div class="mt-2">
                                <a href="{{ route('account.kyc.nin') }}" class="btn btn-sm btn-warning text-dark font-weight-bold py-1 px-2" style="font-size: 0.75rem;">Verify KYC NIN</a>
                                <a href="{{ route('account.kyc.bvn') }}" class="btn btn-sm btn-outline-warning font-weight-bold py-1 px-2 ml-1" style="font-size: 0.75rem;">Verify BVN</a>
                            </div>
                        </div>
                    @endif

                    @php $canRegen = (Auth::user()->role ?? 'user') === 'admin'; @endphp
                    @if($canRegen)
                        <button type="button" class="btn btn-outline-light btn-sm w-100 py-2 mb-3" onclick="regenerateAutoFundingAccounts()">
                            <i class="fa-solid fa-rotate mr-2"></i> Regenerate Accounts (Admin)
                        </button>
                    @endif

                    <div id="autoFundingAccounts" class="d-grid gap-2">
                        <div class="text-center p-3 opacity-75">
                            <i class="fa fa-spinner fa-spin"></i> Loading accounts...
                        </div>
                    </div>
                </div>

                <!-- Step 4: Manual Bank Transfer Form -->
                <div id="fundingStep2" style="display: none;">
                    <button class="btn btn-sm btn-link text-white-50 p-0 mb-3" onclick="backToFundingStep1()">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Back
                    </button>
                    
                    <div class="alert alert-info p-3 mb-3" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); color: #93c5fd; border-radius: 12px;">
                        <span class="small d-block text-white-50 mb-1">Company Bank Details:</span>
                        <h5 id="mf-bank" class="mb-1 text-white font-weight-bold"><i class="fa fa-spinner fa-spin"></i></h5>
                        <h3 id="mf-acc" class="font-weight-bold text-white mb-1" style="letter-spacing: 1px;"><i class="fa fa-spinner fa-spin"></i></h3>
                        <div id="mf-name" class="small text-uppercase mt-2 text-white-50"><i class="fa fa-spinner fa-spin"></i></div>
                    </div>

                    <form id="mfForm" onsubmit="submitManualFunding(event)">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="small text-muted font-weight-bold">Amount Sent (₦)</label>
                            <input type="number" name="amount" id="mf-amount" class="form-control text-white" required min="100" placeholder="e.g. 5000">
                        </div>
                        <div class="form-group mb-4">
                            <label class="small text-muted font-weight-bold">Sender Name or Transaction Reference</label>
                            <input type="text" name="reference" id="mf-ref" class="form-control text-white" required placeholder="e.g. John Doe / Bank Ref">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 font-weight-bold" id="mf-submit-btn" style="border-radius: 12px;">I Have Sent The Money</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(session('start_tour'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.introJs !== 'function') {
            console.warn('introJs is not available; skipping dashboard tour.');
            return;
        }
        var intro = introJs();
        intro.setOptions({
            steps: [
                { 
                    element: document.querySelector('.welcome-hero'),
                    intro: "Welcome to your Dashboard! This is your main hub for all activities."
                },
                {
                    element: document.querySelector('.hero-wallet-card'),
                    intro: "This is your wallet. You can fund it to start using our services."
                },
                {
                    element: document.querySelector('#dashboardVirtualAccounts'),
                    intro: "Here you can see your virtual accounts for easy wallet funding."
                },
                {
                    element: document.querySelector('.service-filter-bar'),
                    intro: "Quickly search or filter all verification and digital services."
                },
                {
                    element: document.querySelector('#recentActivity'),
                    intro: "Track your recent operations and transactions here."
                }
            ]
        });

        intro.oncomplete(function() {
            fetch("{{ route('tour.complete') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ tour: '{{ session("start_tour") }}' })
            });
        });

        intro.start();
    });
</script>
@endif
<script>
    window.authUserEmail = "{{ Auth::user()->email }}";
    let isBalanceHidden = localStorage.getItem('hide_balance') === 'true';

    function updateBalanceDisplay() {
        const el = document.getElementById('walletBalanceDisplay');
        const icon = document.getElementById('balanceToggleIcon');
        if (!el) return;
        
        if (isBalanceHidden) {
            el.textContent = '₦••••••••';
            if (icon) {
                icon.className = 'fa-solid fa-eye-slash';
            }
        } else {
            el.textContent = el.getAttribute('data-actual-balance') || '₦0.00';
            if (icon) {
                icon.className = 'fa-solid fa-eye';
            }
        }
    }

    function toggleBalanceVisibility() {
        isBalanceHidden = !isBalanceHidden;
        localStorage.setItem('hide_balance', isBalanceHidden);
        updateBalanceDisplay();
    }

    function selectServiceCategory(cat, btn) {
        document.querySelectorAll('.cat-pill').forEach(el => el.classList.remove('active'));
        if (btn) btn.classList.add('active');

        const searchVal = ($('#serviceSearchInput').val() || '').toLowerCase().trim();

        document.querySelectorAll('.service-group').forEach(group => {
            const groupCat = group.getAttribute('data-category');
            if (cat === 'all' || groupCat === cat) {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
            }
        });

        if (searchVal) {
            filterServices();
        }
    }

    function filterServices() {
        const query = ($('#serviceSearchInput').val() || '').toLowerCase().trim();
        const activeCat = $('.cat-pill.active').data('filter') || 'all';

        document.querySelectorAll('.service-group').forEach(group => {
            const groupCat = group.getAttribute('data-category');
            if (activeCat !== 'all' && groupCat !== activeCat) {
                group.style.display = 'none';
                return;
            }

            let groupHasVisibleCards = false;
            group.querySelectorAll('.qa-card').forEach(card => {
                const title = (card.querySelector('.qa-label')?.textContent || '').toLowerCase();
                if (!query || title.includes(query)) {
                    card.style.display = 'flex';
                    groupHasVisibleCards = true;
                } else {
                    card.style.display = 'none';
                }
            });

            group.style.display = groupHasVisibleCards ? 'block' : 'none';
        });
    }

    (function bootDashboardScripts() {
        if (typeof window.jQuery === 'undefined') {
            window.setTimeout(bootDashboardScripts, 50);
            return;
        }
        var $ = window.jQuery;
        $(document).ready(function() {
            updateBalanceDisplay();

            // Load recent transactions via JSON API
            $.getJSON("{{ route('history.json') }}", function(data) {
                const txns = data.transactions;
                if (txns && txns.length > 0) {
                    let html = '';
                    txns.forEach(t => {
                        const isSuccess = t.status === 'success';
                        const isPending = t.status === 'pending';
                        const iconClass = isSuccess ? 'success fa-check' : (isPending ? 'pending fa-clock' : 'failed fa-xmark');
                        const statusBadgeClass = isSuccess ? 'badge-success' : (isPending ? 'badge-warning' : 'badge-danger');
                        const txUrl = "{{ url('/history') }}/" + encodeURIComponent(t.transaction_id || t.id);

                        html += `
                            <a href="${txUrl}" class="txn-row-item">
                                <div class="txn-icon ${isSuccess ? 'success' : (isPending ? 'pending' : 'failed')}">
                                    <i class="fa-solid ${iconClass}"></i>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="small font-weight-bold text-truncate" style="color: #f8fafc; font-size: 0.875rem;">${t.order_type}</div>
                                    <div class="x-small text-muted" style="letter-spacing: 0.3px;">${t.created_at || 'Recently'}</div>
                                </div>
                                <div class="text-right small flex-shrink-0">
                                    <div class="font-weight-bold" style="color: #f8fafc; font-size: 0.9rem;">₦${t.balance_after}</div>
                                    <span class="badge ${statusBadgeClass} px-2 py-0.5 mt-1" style="border-radius: 6px; font-size: 0.65rem;">${t.status}</span>
                                </div>
                            </a>`;
                    });
                    $('#recentActivity').html(html);
                } else {
                    $('#recentActivity').html(`
                        <div class="p-5 text-center opacity-60">
                            <i class="fa-solid fa-receipt mb-2 text-white-50" style="font-size: 2rem;"></i>
                            <div class="text-white font-weight-bold small">No Recent Operations</div>
                            <div class="text-muted small">Your recent orders and verifications will appear here.</div>
                        </div>`);
                }
            }).fail(function() {
                $('#recentActivity').html('<div class="p-4 text-center text-muted small">Could not load transactions.</div>');
            });

            // Copy Referral
            $('.ref-copy').click(function() {
                let url = $(this).data('referral-url') || '';
                if (!url) return;
                copyToClipboard(url);
                const btn = $(this);
                const orig = btn.html();
                btn.html('<i class="fa-solid fa-check mr-1"></i> Copied!');
                setTimeout(() => btn.html(orig), 2000);
            });

            loadVirtualAccounts();
        });
    })();

    function loadVirtualAccounts() {
        $.get("{{ route('payment.virtual_accounts.list') }}")
            .done(function(res) {
                if (res.status && res.accounts && res.accounts.length > 0) {
                    const html = renderGroupedAutoFundingAccounts(res.accounts);
                    $('#dashboardVirtualAccounts').html(html);

                    const pending = res.accounts.some(a => (a.status || '') === 'pending');
                    if (pending) {
                        startDashboardVaPoll();
                    }
                } else {
                    $('#dashboardVirtualAccounts').html(`
                        <div class="text-center p-4 rounded-lg" style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.08);">
                            <i class="fa-solid fa-building-columns text-white-50 mb-2" style="font-size: 1.5rem;"></i>
                            <div class="text-white font-weight-bold small mb-1">No Dedicated Accounts Active Yet</div>
                            <p class="small text-muted mb-3">Generate bank transfer accounts for instant 24/7 wallet funding.</p>
                            <button type="button" class="btn btn-sm btn-primary" onclick="showAutoFundingAccountsModal()">
                                <i class="fa-solid fa-plus-circle mr-1"></i> Generate Dedicated Account
                            </button>
                        </div>`);
                }
            })
            .fail(function() {
                $('#dashboardVirtualAccounts').html('<div class="text-center p-3 text-muted small">Could not load virtual accounts.</div>');
            });
    }

    function refreshVirtualAccounts() {
        $('#dashboardVirtualAccounts').html('<div class="text-center p-4 opacity-75"><i class="fa fa-spinner fa-spin mr-2"></i> Refreshing accounts...</div>');
        loadVirtualAccounts();
    }

    function showAutoFundingAccountsModal() {
        $('#fundWalletModal').modal('show');
        showAutoFundingAccounts();
    }

    let dashboardVaPollTimer = null;
    function startDashboardVaPoll() {
        if (dashboardVaPollTimer) return;
        dashboardVaPollTimer = setInterval(function() {
            $.get("{{ route('payment.virtual_accounts.list') }}")
                .done(function(res) {
                    if (res.status && res.accounts && res.accounts.length > 0) {
                        $('#dashboardVirtualAccounts').html(renderGroupedAutoFundingAccounts(res.accounts));
                        const pending = res.accounts.some(a => (a.status || '') === 'pending');
                        if (!pending) {
                            clearInterval(dashboardVaPollTimer);
                            dashboardVaPollTimer = null;
                        }
                    }
                });
        }, 5000);
    }

    function copyToClipboard(text, btnElement) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(showFeedback).catch(fallbackCopy);
        } else {
            fallbackCopy();
        }

        function fallbackCopy() {
            const temp = document.createElement('input');
            document.body.appendChild(temp);
            temp.value = text;
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
            showFeedback();
        }

        function showFeedback() {
            if (btnElement) {
                const orig = btnElement.innerHTML;
                btnElement.innerHTML = '<i class="fa-solid fa-check"></i> Copied';
                setTimeout(() => { btnElement.innerHTML = orig; }, 2000);
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Copied to clipboard!',
                    showConfirmButton: false,
                    timer: 1500,
                    background: '#0f172a',
                    color: '#fff'
                });
            }
        }
    }

    function showOtherFundingOptions() {
        $('#fundingStepDirect').hide();
        $('#fundingStep1').fadeIn();
    }

    function backToAmountEntry() {
        $('#fundingStep1').hide();
        $('#fundingStepDirect').fadeIn();
    }

    function initiateDirectCheckout() {
        const amount = $('#dc-amount').val();
        if (!amount || amount < 100) {
            Swal.fire('Invalid Amount', 'Please enter a minimum of ₦100', 'warning');
            return;
        }

        $('#fundWalletModal').modal('hide');
        
        // Use shared payment modal
        if (typeof openPayModal === 'function') {
            openPayModal('Wallet Funding', amount, 'Credit your Fuwa.NG wallet');
        } else {
            Swal.fire('Error', 'Payment system is still loading. Please try again in a moment.', 'error');
        }
    }

    function renderGroupedAutoFundingAccounts(accounts) {
        const groups = {};
        accounts.forEach(a => {
            const gKey = a.provider_group || 'other';
            const gLabel = a.provider_group_label || 'Other';
            if (!groups[gKey]) groups[gKey] = { label: gLabel, items: [] };
            groups[gKey].items.push(a);
        });

        const order = ['monnify', 'payvessel', 'palmpay', 'paystack', 'flutterwave', 'other'];
        let html = '<div class="row">';

        order.forEach(k => {
            if (!groups[k] || groups[k].items.length === 0) return;
            groups[k].items.forEach(a => {
                const acct = (a.accountNumber || '').toString();
                const bank = (a.bank || 'Bank').toString();
                const name = (a.accountName || '').toString();
                const st = (a.status || '').toString();
                const badge = st === 'pending' ? `<span class="badge badge-warning ml-2">Generating</span>` : ``;

                html += `
                    <div class="col-md-6 mb-3">
                        <div class="va-highlight-card h-100 d-flex flex-column justify-content-between">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="va-bank-badge">${bank}</span>
                                ${badge}
                            </div>
                            <div class="mb-3">
                                <div class="small text-muted mb-1" style="font-size: 0.75rem;">Account Number</div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="va-num-display">${acct}</div>
                                    <button type="button" class="va-copy-btn" onclick="copyToClipboard('${acct.replace(/'/g, "\\'")}', this)">
                                        <i class="fa-solid fa-copy mr-1"></i> Copy
                                    </button>
                                </div>
                                ${name ? `<div class="small text-white-50 text-uppercase font-weight-bold mt-1" style="font-size: 0.75rem;">${name}</div>` : ``}
                            </div>
                            <div class="x-small text-muted border-top border-secondary pt-2" style="font-size: 0.7rem;">
                                <i class="fa-solid fa-bolt mr-1 text-warning"></i> Automated instant credit on transfer
                            </div>
                        </div>
                    </div>
                `;
            });
        });

        html += '</div>';
        return html;
    }

    function showAutoFundingAccounts() {
        $('#fundingStep1').hide();
        $('#fundingStep2').hide();
        $('#fundingStepAuto').show();

        $('#autoFundingAccounts').html('<div class="text-center p-3 opacity-75"><i class="fa fa-spinner fa-spin"></i> Loading accounts...</div>');

        $.post("{{ route('payment.auto_funding.ensure') }}", {_token: "{{ csrf_token() }}"})
            .done(function(res) {
                if(res.status && res.accounts && res.accounts.length > 0) {
                    $('#autoFundingAccounts').html(renderGroupedAutoFundingAccounts(res.accounts));

                    const pending = res.accounts.some(a => (a.status || '') === 'pending');
                    if (pending) {
                        startAutoFundingPoll();
                    }
                } else {
                    const msg = res.message || 'Auto funding accounts are not ready yet. Please use Manual Bank Transfer or Card checkout.';
                    $('#autoFundingAccounts').html(`<div class="text-center p-3 text-muted small">${msg}</div>`);
                }
            })
            .fail(function(xhr) {
                $('#autoFundingAccounts').html(`<div class="text-center p-3 text-muted small">${xhr.responseJSON?.message || 'Could not load accounts.'}</div>`);
            });
    }

    let autoFundingPollTimer = null;
    function startAutoFundingPoll() {
        if (autoFundingPollTimer) return;
        autoFundingPollTimer = setInterval(function() {
            $.get("{{ route('payment.virtual_accounts.list') }}")
                .done(function(res) {
                    if (res.status && res.accounts && res.accounts.length > 0) {
                        $('#autoFundingAccounts').html(renderGroupedAutoFundingAccounts(res.accounts));
                        const pending = res.accounts.some(a => (a.status || '') === 'pending');
                        if (!pending) {
                            clearInterval(autoFundingPollTimer);
                            autoFundingPollTimer = null;
                        }
                    }
                });
        }, 5000);
    }

    function regenerateAutoFundingAccounts() {
        Swal.fire({
            title: 'Regenerate accounts?',
            text: 'This will request new reserved accounts from providers.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Regenerate',
            background: '#0f172a',
            color: '#fff',
            confirmButtonColor: '#3b82f6'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $('#autoFundingAccounts').html('<div class="text-center p-3 opacity-75"><i class="fa fa-spinner fa-spin"></i> Regenerating...</div>');
            $.post("{{ route('payment.auto_funding.regenerate') }}", {_token: "{{ csrf_token() }}"})
                .done(function(res) {
                    if (res.status && res.accounts && res.accounts.length > 0) {
                        $('#autoFundingAccounts').html(renderGroupedAutoFundingAccounts(res.accounts));
                        Swal.fire({
                            title: 'Done',
                            text: 'Accounts regenerated successfully.',
                            icon: 'success',
                            background: '#0f172a',
                            color: '#fff',
                            confirmButtonColor: '#3b82f6'
                        });
                    } else {
                        $('#autoFundingAccounts').html(`<div class="text-center p-3 text-muted small">${res.message || 'Could not regenerate accounts.'}</div>`);
                    }
                })
                .fail(function(xhr) {
                    $('#autoFundingAccounts').html(`<div class="text-center p-3 text-muted small">${xhr.responseJSON?.message || 'Could not regenerate accounts.'}</div>`);
                });
        });
    }

    function showBankDetails() {
        $('#fundingStep1').hide();
        $('#fundingStepAuto').hide();
        $('#fundingStep2').show();
        
        $.get("{{ route('funding.bank') }}", function(res) {
            if(res.status && res.details) {
                $('#mf-bank').text(res.details.bank_name);
                $('#mf-acc').text(res.details.account_number);
                $('#mf-name').text(res.details.account_name);
            } else {
                $('#mf-bank, #mf-acc, #mf-name').text('Unavailable');
            }
        });
    }

    function backToFundingStep1() {
        $('#fundingStepAuto, #fundingStep2').hide();
        $('#fundingStep1').fadeIn();
        $('#mfForm')[0].reset();
    }

    function submitManualFunding(e) {
        e.preventDefault();
        let btn = $('#mf-submit-btn');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Submitting...');

        $.post("{{ route('funding.submit') }}", $('#mfForm').serialize())
            .done(function(res) {
                if(res.status) {
                    $('#fundWalletModal').modal('hide');
                    Swal.fire({
                        title: 'Request Received',
                        text: res.message,
                        icon: 'success',
                        background: '#0f172a',
                        color: '#fff',
                        confirmButtonColor: '#3b82f6'
                    });
                    backToFundingStep1();
                } else {
                    Swal.fire('Error', res.message || 'Submission failed.', 'error');
                }
            })
            .fail(function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'A network error occurred.', 'error');
            })
            .always(function() {
                btn.prop('disabled', false).text('I Have Sent The Money');
            });
    }
</script>
@endpush
