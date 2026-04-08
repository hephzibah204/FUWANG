@extends('layouts.nexus')

@section('title', 'Dashboard | ' . config('app.name'))

@section('content')
@include('dashboard_styles')
<div class="nexus-dashboard">
    <!-- Premium Welcome Hero -->
    <div class="welcome-hero mb-5" data-step="1" data-intro="Welcome to your Dashboard! This is your main hub for all activities.">
        <div class="hero-bg-accent"></div>
        <div class="row align-items-center position-relative">
            <div class="col-lg-7 mb-4 mb-lg-0">
                <div class="hero-welcome-text">
                    <span class="badge badge-primary px-3 py-2 mb-3" style="border-radius: 10px; background: rgba(59, 130, 246, 0.15);">
                        <i class="fa-solid fa-crown mr-1"></i> Premium Account
                    </span>
                    <h1 class="display-4 font-weight-bold mb-2">Hello, {{ explode(' ', Auth::user()->fullname)[0] ?? Auth::user()->username }}!</h1>
                    <p class="text-white-50 lead mb-4">Manage your identity verifications and digital services from your secure command center.</p>
                    
                    <div class="hero-stats-row d-flex gap-4 fade-up stagger-1">
                        <x-nexus.stat-card label="Total Verifications" :value="number_format($verificationCount ?? 0)" />
                        <x-nexus.stat-card label="Member Since" :value="Auth::user()->created_at->format('M Y')" />
                        <x-nexus.stat-card label="Account ID" :value="Auth::user()->referral_id" />
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="hero-wallet-card" data-step="2" data-intro="This is your wallet. You can fund it to start using our services.">
                    <div class="hw-glow"></div>
                    <div class="hw-content">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="hw-label text-white-50">Total Balance</div>
                            <i class="fa-solid fa-wallet text-white-50"></i>
                        </div>
                        <div class="hw-amount mb-4">₦{{ number_format($balance, 2) }}</div>
                        <div class="hw-actions d-flex gap-2">
                            <button class="btn btn-primary flex-grow-1" data-toggle="modal" data-target="#fundWalletModal">
                                <i class="fa-solid fa-plus-circle mr-2"></i> Fund Wallet
                            </button>
                            <button class="btn btn-glass" style="width: 50px;" title="Transfer">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-section mb-5" data-step="3" data-intro="Here you can see your virtual accounts for easy wallet funding.">
        <div class="section-hdr mb-4">
            <div class="d-flex align-items-center">
                <div class="section-icon lifestyle"><i class="fa-solid fa-building-columns"></i></div>
                <div>
                    <h2 class="h5 font-weight-bold m-0">Virtual Accounts</h2>
                    <p class="small text-muted m-0">Fund your wallet via bank transfer</p>
                </div>
            </div>
            <a href="{{ route('wallet.fund') }}" class="small text-primary font-weight-bold">View all</a>
        </div>
        <div id="dashboardVirtualAccounts">
            <div class="text-center p-3 opacity-75">
                <i class="fa fa-spinner fa-spin"></i> Loading accounts...
            </div>
        </div>
    </div>

    <!-- Quick Services: Identity Proofing -->
    <div class="dashboard-section mb-5 fade-up stagger-2" data-step="4" data-intro="This section provides quick access to our identity verification services.">
        <div class="section-hdr mb-4">
            <div class="d-flex align-items-center">
                <div class="section-icon identity"><i class="fa-solid fa-id-card"></i></div>
                <div>
                    <h2 class="h5 font-weight-bold m-0">Identity Proofing</h2>
                    <p class="small text-muted m-0">Core government ID verification services</p>
                </div>
            </div>
        </div>
        <div class="quick-grid">
            @if(\App\Models\SystemSetting::get('nin_service_enabled', 'true') === 'true')
                <x-nexus.service-card title="NIN Suite" :href="route('services.nin.suite')" icon="fa-id-card-clip" iconVariant="solid" style="border: 2px solid var(--clr-primary);" />
            @endif

            @if(\App\Models\SystemSetting::get('bvn_service_enabled', 'true') === 'true')
                <x-nexus.service-card title="BVN Suite" :href="route('services.bvn')" icon="fa-building-columns" iconVariant="solid" />
                <x-nexus.service-card title="Print BVN" :href="route('services.bvn')" icon="fa-print" iconVariant="solid" />
            @endif
        </div>
    </div>

    <!-- Quick Services: Verification Hub -->
    <div class="dashboard-section mb-5 fade-up stagger-3">
        <div class="section-hdr mb-4">
            <div class="d-flex align-items-center">
                <div class="section-icon verification"><i class="fa-solid fa-shield-halved"></i></div>
                <div>
                    <h2 class="h5 font-weight-bold m-0">Verification Hub</h2>
                    <p class="small text-muted m-0">Corporate, travel and document authentication</p>
                </div>
            </div>
        </div>
        <div class="quick-grid">
            <x-nexus.service-card title="DL Verify" :href="route('services.drivers_license')" icon="fa-car" iconVariant="solid" />
            <x-nexus.service-card title="Bio Verify" :href="route('services.biometric_verify')" icon="fa-fingerprint" iconVariant="solid" />
            <x-nexus.service-card title="CAC Verify" :href="route('services.cac_verify')" icon="fa-briefcase" iconVariant="solid" />
            <x-nexus.service-card title="TIN Verify" :href="route('services.tin_verify')" icon="fa-percent" iconVariant="solid" />
            <x-nexus.service-card title="Passport" :href="route('services.passport')" icon="fa-passport" iconVariant="solid" />
            <x-nexus.service-card title="Voters Card" :href="route('services.voters_card')" icon="fa-box-archive" iconVariant="solid" />
            <x-nexus.service-card title="Address" :href="route('services.address_verify')" icon="fa-location-dot" iconVariant="solid" />
            <x-nexus.service-card title="Plate No." :href="route('services.plate_number')" icon="fa-car-rear" iconVariant="solid" />
        </div>
    </div>

    <!-- Quick Services: Ecosystem & Lifestyle -->
    <div class="dashboard-section mb-5 fade-up stagger-4">
        <div class="section-hdr mb-4">
            <div class="d-flex align-items-center">
                <div class="section-icon lifestyle"><i class="fa-solid fa-bolt"></i></div>
                <div>
                    <h2 class="h5 font-weight-bold m-0">Ecosystem & Lifestyle</h2>
                    <p class="small text-muted m-0">Utilities, business and extra services</p>
                </div>
            </div>
            <a href="{{ route('services.price_list') }}" class="small text-primary font-weight-bold">View Prices</a>
        </div>
        <div class="quick-grid">
            @if(\App\Models\SystemSetting::get('airtime_data_enabled', 'true') === 'true')
                <x-nexus.service-card title="VTU Hub" :href="route('services.vtu.hub')" icon="fa-mobile-screen-button" iconVariant="solid" badge="VIVA" badgeColor="var(--clr-accent-2)" />
            @endif

            @if(\App\Models\SystemSetting::get('legal_service_enabled', 'true') === 'true')
                <x-nexus.service-card title="Legal Hub" :href="route('services.legal-hub')" icon="fa-gavel" iconVariant="solid" />
                <x-nexus.service-card title="Notary" :href="route('services.notary')" icon="fa-file-signature" iconVariant="solid" />
            @endif

            <x-nexus.service-card title="Agency" :href="route('services.agency')" icon="fa-shop" iconVariant="solid" />
            <x-nexus.service-card title="Cards" :href="route('services.virtual_card')" icon="fa-credit-card" iconVariant="solid" />
            <x-nexus.service-card title="Tickets" :href="route('services.ticketing')" icon="fa-ticket" iconVariant="solid" />
            
            @if(\App\Models\SystemSetting::get('auction_service_enabled', 'true') === 'true')
                @php $aucHref = \Illuminate\Support\Facades\Route::has('auctions.dashboard') ? route('auctions.dashboard') : route('public.auctions.index'); @endphp
                <x-nexus.service-card title="Auctions" :href="$aucHref" icon="fa-gavel" iconVariant="solid" />
            @endif

            @php $logHref = \Illuminate\Support\Facades\Route::has('user.logistics.dashboard') ? route('user.logistics.dashboard') : route('public.logistics.index'); @endphp
            <x-nexus.service-card title="Logistics" :href="$logHref" icon="fa-truck-fast" iconVariant="solid" />
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <!-- Recent Transactions Panel -->
            <div class="panel-card h-100" data-step="5" data-intro="Track your recent operations and transactions here.">
                <div class="panel-hdr">
                    <h3 class="h6 font-weight-bold m-0 text-white"><i class="fa-solid fa-receipt mr-2 text-primary"></i> Recent Operations</h3>
                    <a href="{{ route('history') }}" class="small text-muted">View Ledger</a>
                </div>
                <div id="recentActivity" style="min-height: 300px;">
                    <!-- Transactions will be loaded here via AJAX -->
                    <div class="txn-row justify-content-center p-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <!-- System Stats & Referrals -->
            <div class="d-flex flex-column gap-4 h-100">
                <div class="panel-card p-4">
                    <div class="panel-hdr mb-3">
                        <h3 class="h6 font-weight-bold m-0 text-white">Engagement</h3>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        <div class="mini-stat-item">
                            <i class="fa-solid fa-coins text-warning"></i>
                            <div>
                                <div class="small text-muted">Referral Earnings</div>
                                <div class="font-weight-bold">₦{{ number_format($referralStats['earnings'] ?? 0, 2) }}</div>
                            </div>
                            <div class="ml-auto small text-success">Paid</div>
                        </div>
                        <div class="mini-stat-item">
                            <i class="fa-solid fa-users text-primary"></i>
                            <div>
                                <div class="small text-muted">Active Referrals</div>
                                <div class="font-weight-bold">{{ number_format($referralStats['funded'] ?? 0) }} Users</div>
                            </div>
                            <div class="ml-auto small text-primary"><a href="{{ route('referrals.index') }}">View</a></div>
                        </div>
                    </div>
                </div>

                <div class="panel-card p-4 flex-grow-1">
                    <div class="panel-hdr mb-3">
                        <h3 class="h6 font-weight-bold m-0 text-white">Announcements</h3>
                    </div>
                    @if($notification)
                        <div class="broadcast-banner p-3 rounded-lg mb-4" style="background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.1);">
                            <div class="d-flex gap-3">
                                <i class="fa-solid fa-bullhorn text-primary mt-1"></i>
                                <div>
                                    <div class="small font-weight-bold text-white mb-1">Official News</div>
                                    <p class="x-small text-muted mb-0">{{ $notification }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 opacity-50">
                            <i class="fa-solid fa-inbox mb-2" style="font-size: 1.5rem;"></i>
                            <p class="x-small m-0">No new announcements</p>
                        </div>
                    @endif
                    
                    <div class="ref-card-modern p-3 rounded-lg mt-auto" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(59, 130, 246, 0.05)); border: 1px solid rgba(139, 92, 246, 0.2);">
                        <div class="small text-muted mb-2">Invite and Earn</div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <code class="text-primary font-weight-bold" style="font-size: 1.1rem;">{{ Auth::user()->referral_id }}</code>
                            <button class="btn btn-sm btn-primary py-1 px-3 ref-copy" style="border-radius: 8px;">Copy Code</button>
                        </div>
                        <a href="{{ route('referrals.index') }}" class="btn btn-sm btn-block btn-glass py-2" style="border-radius: 8px; font-size: 0.75rem; letter-spacing: 0.5px; border: 1px solid rgba(255,255,255,0.05);">
                            <i class="fa-solid fa-chart-line mr-1"></i> VIEW ANALYTICS
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
        <div class="modal-content service-card border-0" style="background: rgba(15, 23, 42, 0.98); border: 1px solid rgba(255,255,255,0.1) !important;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold">Fund Wallet</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                
                <!-- Step 1: Amount Entry -->
                <div id="fundingStepDirect">
                    <p class="text-white-50 small mb-4">Enter the amount you wish to fund. Minimum ₦100.</p>
                    <div class="form-group mb-4">
                        <label class="small text-muted">Amount (₦)</label>
                        <input type="number" id="dc-amount" class="form-control" placeholder="1000" min="100" value="1000">
                    </div>
                    <button type="button" class="btn btn-primary w-100 py-3 mb-3" onclick="initiateDirectCheckout()">
                        <i class="fa-solid fa-arrow-right mr-2"></i> Continue to Payment Methods
                    </button>
                    
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <button class="btn btn-link text-white-50 p-0 small" onclick="showOtherFundingOptions()">
                            Other funding options
                        </button>
                    </div>
                </div>

                <!-- Step 2: Other Options (Selection) -->
                <div id="fundingStep1" style="display: none;">
                    <button class="btn btn-sm btn-link text-white-50 p-0 mb-3" onclick="backToAmountEntry()">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </button>
                    <p class="text-muted small mb-4">Choose an alternative funding method.</p>
                    <div class="d-grid gap-3">
                        <button class="btn btn-outline w-100 py-3 mb-2" onclick="showAutoFundingAccounts()">
                            <i class="fa-solid fa-robot mr-2"></i> Instant Auto Funding
                        </button>
                        <button class="btn btn-outline w-100 py-3" onclick="showBankDetails()">
                            <i class="fa-solid fa-university mr-2"></i> Manual Bank Transfer
                        </button>
                    </div>
                </div>

                <!-- Step 2: Instant Auto Funding -->
                <div id="fundingStepAuto" style="display: none;">
                    <button class="btn btn-sm btn-link text-white-50 p-0 mb-3" onclick="backToFundingStep1()">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </button>

                    <div class="alert alert-info" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); color: #93c5fd;">
                        <div class="small">Transfer to any of the accounts below. Wallet credits automatically after confirmation.</div>
                    </div>

                    @php $canRegen = (Auth::user()->role ?? 'user') === 'admin'; @endphp
                    @if($canRegen)
                        <button type="button" class="btn btn-outline-light w-100 py-2 mb-3" onclick="regenerateAutoFundingAccounts()">
                            <i class="fa-solid fa-rotate mr-2"></i> Regenerate Accounts (Admin)
                        </button>
                    @endif

                    <div id="autoFundingAccounts" class="d-grid gap-2">
                        <div class="text-center p-3 opacity-75">
                            <i class="fa fa-spinner fa-spin"></i> Loading accounts...
                        </div>
                    </div>
                </div>

                <!-- Step 2: Manual Bank Transfer Form -->
                <div id="fundingStep2" style="display: none;">
                    <button class="btn btn-sm btn-link text-white-50 p-0 mb-3" onclick="backToFundingStep1()">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </button>
                    
                    <div class="alert alert-info" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); color: #93c5fd;">
                        <span class="small d-block mb-1">Transfer to:</span>
                        <h5 id="mf-bank" class="mb-1 text-white"><i class="fa fa-spinner fa-spin"></i></h5>
                        <h3 id="mf-acc" class="font-weight-bold text-white mb-1"><i class="fa fa-spinner fa-spin"></i></h3>
                        <div id="mf-name" class="small text-uppercase mt-2"><i class="fa fa-spinner fa-spin"></i></div>
                    </div>

                    <form id="mfForm" onsubmit="submitManualFunding(event)">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="small text-muted">Amount Sent (₦)</label>
                            <input type="number" name="amount" id="mf-amount" class="form-control" required min="100">
                        </div>
                        <div class="form-group mb-4">
                            <label class="small text-muted">Sender Name or Reference</label>
                            <input type="text" name="reference" id="mf-ref" class="form-control" required placeholder="e.g. John Doe Transfer">
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="mf-submit-btn">I Have Sent The Money</button>
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
                    element: document.querySelector('.dashboard-section .quick-grid'),
                    intro: "This section provides quick access to our identity verification services."
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

    $(document).ready(function() {
        // Load recent transactions via JSON API
        $.getJSON("{{ route('history.json') }}", function(data) {
            const txns = data.transactions;
            if (txns && txns.length > 0) {
                let html = '';
                txns.forEach(t => {
                    const isSuccess = t.status === 'success';
                    const icon = isSuccess ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
                    html += `
                        <div class="txn-row px-4 py-3 d-flex align-items-center mb-2 rounded-lg" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.background='rgba(255,255,255,0.05)'; this.style.transform='translateX(4px)';" onmouseout="this.style.background='rgba(255,255,255,0.02)'; this.style.transform='none';">
                            <i class="fa-solid ${icon} mr-3" style="font-size: 1.2rem;"></i>
                            <div class="flex-grow-1">
                                <div class="small font-weight-bold" style="color: #f8fafc; font-size: 0.9rem;">${t.order_type}</div>
                                <div class="x-small text-muted" style="letter-spacing: 0.5px;">${t.created_at}</div>
                            </div>
                            <div class="text-right small">
                                <div class="font-weight-bold" style="color: #f8fafc; font-size: 0.95rem;">₦${t.balance_after}</div>
                                <span class="badge ${isSuccess ? 'badge-success' : 'badge-danger'} px-2 py-1 mt-1" style="border-radius: 6px;">${t.status}</span>
                            </div>
                        </div>`;
                });
                $('#recentActivity').html(html);
            } else {
                $('#recentActivity').html('<div class="p-5 text-center opacity-50">No recent transactions</div>');
            }
        }).fail(function() {
            $('#recentActivity').html('<div class="p-4 text-center text-muted small">Could not load transactions.</div>');
        });

        // Copy Referral
        $('.ref-copy').click(function() {
            let code = $(this).prev().text();
            copyToClipboard(code);
            $(this).text('Copied!');
            setTimeout(() => $(this).text('Copy Code'), 2000);
        });

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
                    $('#dashboardVirtualAccounts').html('<div class="text-center p-3 text-muted small">No virtual accounts yet. Use “Fund Wallet” to generate.</div>');
                }
            })
            .fail(function() {
                $('#dashboardVirtualAccounts').html('<div class="text-center p-3 text-muted small">Could not load virtual accounts.</div>');
            });
    });

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

    function copyToClipboard(text) {
        const temp = document.createElement('input');
        document.body.appendChild(temp);
        temp.value = text;
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
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
        
        // Use the shared payment modal
        if (typeof openPayModal === 'function') {
            openPayModal('Wallet Funding', amount, 'Credit your Fuwa.NG wallet');
        } else {
            Swal.fire('Error', 'Payment system is still loading. Please try again in a moment.', 'error');
        }
    }

    // Manual Funding Flow
    function renderGroupedAutoFundingAccounts(accounts) {
        const groups = {};
        accounts.forEach(a => {
            const gKey = a.provider_group || 'other';
            const gLabel = a.provider_group_label || 'Other';
            if (!groups[gKey]) groups[gKey] = { label: gLabel, items: [] };
            groups[gKey].items.push(a);
        });

        const order = ['monnify', 'payvessel', 'palmpay', 'paystack', 'flutterwave', 'other'];
        let html = '';

        order.forEach(k => {
            if (!groups[k] || groups[k].items.length === 0) return;
            html += `<div class="small text-uppercase text-white-50 mt-2 mb-2" style="letter-spacing:1px;">${groups[k].label}</div>`;
            groups[k].items.forEach(a => {
                const acct = (a.accountNumber || '').toString();
                const bank = (a.bank || 'Bank').toString();
                const name = (a.accountName || '').toString();
                const st = (a.status || '').toString();
                const badge = st ? `<span class="badge badge-pill ml-2" style="background: rgba(148, 163, 184, 0.15); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.25);">${st}</span>` : ``;
                html += `
                    <div class="alert alert-info mb-2" style="background: rgba(59, 130, 246, 0.06); border: 1px solid rgba(59, 130, 246, 0.18); color: #cbd5e1;">
                        <div class="small text-uppercase mb-1">${bank}${badge}</div>
                        <div class="d-flex align-items-center justify-content-between" style="gap:10px;">
                            <div>
                                <div class="h5 mb-0 text-white">${acct}</div>
                                ${name ? `<div class="small text-white-50">${name}</div>` : ``}
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-light" onclick="copyToClipboard('${acct.replace(/'/g, "\\'")}')">Copy</button>
                        </div>
                    </div>
                `;
            });
        });

        return html;
    }

    function showAutoFundingAccounts() {
        $('#fundingStep1').hide();
        $('#fundingStep2').hide();
        $('#fundingStepAuto').show();

        $('#autoFundingAccounts').html('<div class="text-center p-3 opacity-75"><i class="fa fa-spinner fa-spin"></i> Loading accounts...</div>');

        $.post("{{ route('payment.auto_funding.ensure') }}", {_token: "{{ csrf_token() }}"})
            .done(function(res) {
                if(res..status && res.accounts && res.accounts.length > 0) {
                    $('#autoFundingAccounts').html(renderGroupedAutoFundingAccounts(res.accounts));

                    const pending = res.accounts.some(a => (a.status || '') === 'pending');
                    if (pending) {
                        startAutoFundingPoll();
                    }
                } else {
                    const msg = res.message || 'Auto funding is not available yet. Please use Manual Bank Transfer.';
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
