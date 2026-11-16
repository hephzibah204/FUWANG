@extends('layouts.nexus')

@section('title', 'VTU & Bill Payments Hub | ' . config('app.name'))

@section('content')
@php
    $user = auth()->user();
@endphp

<div class="service-page fade-in">
    <!-- Header -->
    <x-nexus.service-header
        title="VTU & Bill Payments Hub"
        subtitle="Instant airtime, high-speed data, cable TV subscriptions, electricity tokens, and education PINs."
        icon="fa-solid fa-layer-group"
        icon-class="vtu-hub-bg"
    >
        <x-slot name="badges">
            <span class="badge-accent"><i class="fa-solid fa-bolt"></i> Instant Fulfillment</span>
            <span class="badge-accent"><i class="fa-solid fa-shield-halved"></i> Automated Gateway</span>
        </x-slot>
    </x-nexus.service-header>

    <!-- Unified Wallet & Live Stats Banner -->
    <div class="panel-card p-4 mb-4 vtu-hero-card">
        <div class="row align-items-center">
            <div class="col-lg-5 col-md-6 mb-3 mb-md-0 border-lg-right">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted small font-weight-600 text-uppercase tracking-wider">Available VTU Balance</span>
                    <span class="vtu-live-badge"><span class="vtu-pulse-dot"></span> Gateway Active</span>
                </div>
                <div class="vtu-balance-display mb-3">₦{{ number_format((float) ($walletBalance ?? 0), 2) }}</div>
                <div class="d-flex flex-wrap" style="gap: 10px;">
                    <a href="{{ route('wallet.fund') }}" class="btn btn-primary btn-sm px-3" data-cta="vtu_hub_fund">
                        <i class="fa-solid fa-plus-circle mr-1"></i> Fund Wallet
                    </a>
                    <a href="{{ route('history') }}" class="btn btn-outline btn-sm px-3">
                        <i class="fa-solid fa-receipt mr-1"></i> Full History
                    </a>
                </div>
            </div>
            
            <div class="col-lg-7 col-md-6">
                <div class="vtu-stats-strip">
                    <div class="vtu-stat-box">
                        <div class="stat-meta">Month Spend</div>
                        <div class="stat-number">₦{{ number_format((float) ($monthSpend ?? 0), 2) }}</div>
                        <div class="stat-sub text-muted">Total debited</div>
                    </div>
                    <div class="vtu-stat-box">
                        <div class="stat-meta">Delivered</div>
                        <div class="stat-number text-success">{{ number_format((int) ($monthSuccessCount ?? 0)) }}</div>
                        <div class="stat-sub text-success">Successful</div>
                    </div>
                    <div class="vtu-stat-box">
                        <div class="stat-meta">In Queue</div>
                        <div class="stat-number text-warning">{{ number_format((int) ($pendingCount ?? 0)) }}</div>
                        <div class="stat-sub text-muted">Processing</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Services Grid (Directly Front & Center) -->
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h2 class="h5 font-weight-bold mb-0 text-white">Instant Services</h2>
                <div class="text-muted small">Choose a service to purchase or dispense instantly.</div>
            </div>
            <span class="badge badge-secondary px-2 py-1"><i class="fa-solid fa-bolt mr-1 text-warning"></i> Automated 24/7</span>
        </div>

        <div class="vtu-services-grid">
            <!-- 1. Airtime -->
            <a href="{{ route('services.vtu.airtime') }}" class="vtu-service-item text-decoration-none">
                <div class="vtu-service-icon icon-airtime">
                    <i class="fa-solid fa-mobile-screen-button"></i>
                </div>
                <div class="vtu-service-content">
                    <div class="service-title">Airtime Top-up</div>
                    <div class="service-desc">MTN, Glo, Airtel, 9mobile</div>
                </div>
                <div class="service-arrow"><i class="fa-solid fa-chevron-right"></i></div>
            </a>

            <!-- 2. Data Bundles -->
            <a href="{{ route('services.vtu.data') }}" class="vtu-service-item text-decoration-none">
                <div class="vtu-service-icon icon-data">
                    <i class="fa-solid fa-wifi"></i>
                </div>
                <div class="vtu-service-content">
                    <div class="service-title">Data Bundles</div>
                    <div class="service-desc">SME, Corporate & Gifting</div>
                </div>
                <div class="service-arrow"><i class="fa-solid fa-chevron-right"></i></div>
            </a>

            <!-- 3. Cable TV -->
            <a href="{{ route('services.vtu.cable') }}" class="vtu-service-item text-decoration-none">
                <div class="vtu-service-icon icon-cable">
                    <i class="fa-solid fa-tv"></i>
                </div>
                <div class="vtu-service-content">
                    <div class="service-title">Cable TV</div>
                    <div class="service-desc">DSTV, GOTV, Startimes</div>
                </div>
                <div class="service-arrow"><i class="fa-solid fa-chevron-right"></i></div>
            </a>

            <!-- 4. Electricity Bills -->
            <a href="{{ route('services.vtu.electricity') }}" class="vtu-service-item text-decoration-none">
                <div class="vtu-service-icon icon-elec">
                    <i class="fa-solid fa-lightbulb"></i>
                </div>
                <div class="vtu-service-content">
                    <div class="service-title">Electricity Bills</div>
                    <div class="service-desc">Prepaid token & Postpaid</div>
                </div>
                <div class="service-arrow"><i class="fa-solid fa-chevron-right"></i></div>
            </a>

            <!-- 5. Bulk SMS -->
            <a href="{{ route('services.vtu.bulksms') }}" class="vtu-service-item text-decoration-none">
                <div class="vtu-service-icon icon-sms">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <div class="vtu-service-content">
                    <div class="service-title">Bulk SMS</div>
                    <div class="service-desc">Custom Sender ID messaging</div>
                </div>
                <div class="service-arrow"><i class="fa-solid fa-chevron-right"></i></div>
            </a>

            <!-- 6. Recharge Printing -->
            <a href="{{ route('services.vtu.recharge_printing') }}" class="vtu-service-item text-decoration-none">
                <div class="vtu-service-icon icon-print">
                    <i class="fa-solid fa-print"></i>
                </div>
                <div class="vtu-service-content">
                    <div class="service-title">Card Printing</div>
                    <div class="service-desc">Airtime & Data physical PINs</div>
                </div>
                <div class="service-arrow"><i class="fa-solid fa-chevron-right"></i></div>
            </a>

            <!-- 7. Airtime to Cash -->
            <a href="{{ route('services.vtu.airtime_to_cash') }}" class="vtu-service-item text-decoration-none">
                <div class="vtu-service-icon icon-cash">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                </div>
                <div class="vtu-service-content">
                    <div class="service-title">Airtime to Cash</div>
                    <div class="service-desc">Convert balance to wallet</div>
                </div>
                <div class="service-arrow"><i class="fa-solid fa-chevron-right"></i></div>
            </a>

            <!-- 8. Betting Funding -->
            <a href="{{ route('services.vtu.betting') }}" class="vtu-service-item text-decoration-none">
                <div class="vtu-service-icon icon-bet">
                    <i class="fa-solid fa-ticket"></i>
                </div>
                <div class="vtu-service-content">
                    <div class="service-title">Betting Funding</div>
                    <div class="service-desc">Top up bookmaker accounts</div>
                </div>
                <div class="service-arrow"><i class="fa-solid fa-chevron-right"></i></div>
            </a>
        </div>
    </div>

    <!-- Education Hub (Result Checkers & Registration Tokens) -->
    <div class="panel-card p-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h3 class="h6 font-weight-bold mb-0 text-white"><i class="fa-solid fa-graduation-cap mr-2 text-primary"></i> Education PINs & Exam Tokens</h3>
                <div class="text-muted small">Purchase official result checker PINs and registration cards.</div>
            </div>
            <a href="#vtuHistory" class="small text-muted text-decoration-none">Instant PIN Delivery</a>
        </div>

        <div class="vtu-edu-grid">
            <a href="{{ route('services.education.waec') }}" class="vtu-edu-card text-decoration-none">
                <img src="{{ asset('vtusite/images/waec.png') }}" alt="WAEC" class="edu-img" onerror="this.onerror=null; this.src='{{ asset('images/waec.png') }}';">
                <div class="edu-info">
                    <div class="edu-title">WAEC Checker</div>
                    <div class="edu-sub">Result checking PIN</div>
                </div>
            </a>

            <a href="{{ route('services.education.waec_registration') }}" class="vtu-edu-card text-decoration-none">
                <img src="{{ asset('vtusite/images/waec.png') }}" alt="WAEC Reg" class="edu-img" onerror="this.onerror=null; this.src='{{ asset('images/waec.png') }}';">
                <div class="edu-info">
                    <div class="edu-title">WAEC Registration</div>
                    <div class="edu-sub">Candidate registration PIN</div>
                </div>
            </a>

            <a href="{{ route('services.education.neco') }}" class="vtu-edu-card text-decoration-none">
                <img src="{{ asset('vtusite/images/neco.png') }}" alt="NECO" class="edu-img" onerror="this.onerror=null; this.src='{{ asset('images/neco.png') }}';">
                <div class="edu-info">
                    <div class="edu-title">NECO Token</div>
                    <div class="edu-sub">Result checker token</div>
                </div>
            </a>

            <a href="{{ route('services.education.nabteb') }}" class="vtu-edu-card text-decoration-none">
                <img src="{{ asset('vtusite/images/nabteb.png') }}" alt="NABTEB" class="edu-img" onerror="this.onerror=null; this.src='{{ asset('images/nabteb.png') }}';">
                <div class="edu-info">
                    <div class="edu-title">NABTEB PIN</div>
                    <div class="edu-sub">Result checker voucher</div>
                </div>
            </a>

            <a href="{{ route('services.education.jamb') }}" class="vtu-edu-card text-decoration-none">
                <img src="{{ asset('vtusite/images/jamb.png') }}" alt="JAMB" class="edu-img" onerror="this.onerror=null; this.src='{{ asset('images/jamb.png') }}';">
                <div class="edu-info">
                    <div class="edu-title">JAMB ePIN</div>
                    <div class="edu-sub">UTME / DE registration</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Balanced Bottom Row: Transactions & Gateway Status (Zero Dead Space) -->
    <div class="row">
        <!-- Left: Recent Transactions with High-Craft Empty State -->
        <div class="col-lg-8 mb-4">
            <div class="panel-card p-4 h-100" id="vtuHistory">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h3 class="h6 font-weight-bold mb-0 text-white">Recent Transactions</h3>
                        <div class="text-muted small">Your latest airtime, data, and bill orders.</div>
                    </div>
                    <a href="{{ route('history') }}" class="btn btn-outline btn-sm">View All History</a>
                </div>

                @php
                    $recent = isset($recentVtu) ? $recentVtu : collect();
                @endphp

                @if($recent->isEmpty())
                    <div class="vtu-empty-box py-4 text-center">
                        <div class="vtu-empty-icon mb-2">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <div class="font-weight-600 text-white mb-1">No VTU Transactions Yet</div>
                        <p class="text-muted small mb-3 mx-auto" style="max-width: 320px;">
                            Your receipts, tokens, and transaction references will appear here immediately after your first purchase.
                        </p>
                        <div class="d-flex justify-content-center" style="gap: 10px;">
                            <a href="{{ route('services.vtu.airtime') }}" class="btn btn-primary btn-sm px-3">
                                <i class="fa-solid fa-mobile-screen-button mr-1"></i> Buy Airtime
                            </a>
                            <a href="{{ route('services.vtu.data') }}" class="btn btn-outline btn-sm px-3">
                                <i class="fa-solid fa-wifi mr-1"></i> Buy Data
                            </a>
                        </div>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th>Service</th>
                                    <th>Order Ref</th>
                                    <th class="text-right">Amount</th>
                                    <th>Status</th>
                                    <th class="text-right">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent->take(5) as $t)
                                    <tr>
                                        <td>
                                            <div class="font-weight-600 text-white">
                                                {{ ucfirst(str_replace('vtu_', '', (string) ($t->service_type ?? 'VTU'))) }}
                                            </div>
                                        </td>
                                        <td class="text-muted small font-mono">
                                            {{ substr((string) ($t->transaction_id ?? $t->reference ?? '—'), 0, 16) }}
                                        </td>
                                        <td class="text-right font-weight-600 text-white">
                                            ₦{{ number_format((float) ($t->total ?? 0), 2) }}
                                        </td>
                                        <td>
                                            @php $st = strtolower((string) ($t->status ?? 'pending')); @endphp
                                            <span class="badge {{ $st === 'success' ? 'badge-success' : ($st === 'failed' ? 'badge-danger' : 'badge-warning') }} px-2 py-1">
                                                {{ ucfirst($st) }}
                                            </span>
                                        </td>
                                        <td class="text-right text-muted small">
                                            {{ optional($t->created_at)->diffForHumans() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right: Gateway Status & Assistance -->
        <div class="col-lg-4 mb-4">
            <div class="panel-card p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted small font-weight-bold text-uppercase">Gateway Health</span>
                        <span class="badge badge-success px-2 py-1"><i class="fa-solid fa-circle-check mr-1"></i> Online</span>
                    </div>

                    <div class="p-3 rounded mb-3" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Provider</span>
                            <span class="font-weight-bold text-white small">Network Band</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Average Delivery</span>
                            <span class="text-success small font-weight-600"><i class="fa-solid fa-bolt mr-1"></i> ~2.4 seconds</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Uptime (30d)</span>
                            <span class="text-white small font-weight-600">99.98%</span>
                        </div>
                    </div>

                    @php
                        $last = $lastVtu ?? null;
                    @endphp
                    @if($last)
                        <div class="p-3 rounded mb-3" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08);">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted x-small text-uppercase">Latest Transaction</span>
                                @php $lst = (string) ($last->status ?? 'pending'); @endphp
                                <span class="badge {{ $lst === 'success' ? 'badge-success' : ($lst === 'failed' ? 'badge-danger' : 'badge-warning') }} x-small">{{ ucfirst($lst) }}</span>
                            </div>
                            <div class="font-weight-bold text-white small mb-1">
                                ₦{{ number_format((float) ($last->total ?? 0), 2) }} • {{ ucfirst(str_replace('vtu_', '', (string) ($last->service_type ?? 'VTU'))) }}
                            </div>
                            <div class="text-muted x-small">
                                {{ optional($last->created_at)->diffForHumans() }}
                            </div>
                        </div>
                    @endif
                </div>

                <div class="p-3 rounded text-center" style="background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.15);">
                    <div class="small font-weight-bold text-white mb-1"><i class="fa-solid fa-headset mr-1 text-primary"></i> Need Help with a Bill?</div>
                    <div class="text-muted x-small mb-2">Our support team is online 24/7 to resolve tokens and re-queries.</div>
                    <a href="{{ Route::has('tickets.create') ? route('tickets.create') : (Route::has('tickets.index') ? route('tickets.index') : '#') }}" class="btn btn-outline btn-sm w-100 py-1">
                        Open Support Ticket
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .vtu-hub-bg { background: rgba(59, 130, 246, 0.12); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.22); }
    
    /* Hero Card */
    .vtu-hero-card {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.4) 0%, rgba(15, 23, 42, 0.6) 100%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 18px;
    }
    .vtu-balance-display {
        font-size: 2rem;
        font-weight: 800;
        color: #ffffff;
        letter-spacing: -0.02em;
        line-height: 1.1;
    }
    .vtu-live-badge {
        font-size: 0.75rem;
        color: #34d399;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
    }
    .vtu-pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #10b981;
        box-shadow: 0 0 8px rgba(16, 185, 129, 0.8);
        display: inline-block;
    }
    
    .vtu-stats-strip {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }
    .vtu-stat-box {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 12px;
        padding: 12px 14px;
    }
    .vtu-stat-box .stat-meta {
        font-size: 0.72rem;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.6);
        font-weight: 600;
        letter-spacing: 0.04em;
    }
    .vtu-stat-box .stat-number {
        font-size: 1.15rem;
        font-weight: 800;
        color: #ffffff;
        margin: 4px 0 2px;
    }
    .vtu-stat-box .stat-sub {
        font-size: 0.72rem;
    }

    /* Core Services Grid */
    .vtu-services-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
    }
    .vtu-service-item {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 14px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: all 0.2s ease;
        position: relative;
    }
    .vtu-service-item:hover {
        background: rgba(255, 255, 255, 0.06);
        border-color: rgba(59, 130, 246, 0.4);
        transform: translateY(-2px);
    }
    .vtu-service-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .icon-airtime { background: rgba(239, 68, 68, 0.12); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); }
    .icon-data { background: rgba(245, 158, 11, 0.12); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2); }
    .icon-cable { background: rgba(168, 85, 247, 0.12); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.2); }
    .icon-elec { background: rgba(16, 185, 129, 0.12); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2); }
    .icon-sms { background: rgba(59, 130, 246, 0.12); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.2); }
    .icon-print { background: rgba(236, 72, 153, 0.12); color: #f472b6; border: 1px solid rgba(236, 72, 153, 0.2); }
    .icon-cash { background: rgba(14, 165, 233, 0.12); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2); }
    .icon-bet { background: rgba(132, 204, 22, 0.12); color: #a3e635; border: 1px solid rgba(132, 204, 22, 0.2); }

    .vtu-service-content {
        flex-grow: 1;
        min-width: 0;
    }
    .service-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: #ffffff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .service-desc {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.55);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-top: 2px;
    }
    .service-arrow {
        color: rgba(255, 255, 255, 0.25);
        font-size: 0.8rem;
        transition: transform 0.15s ease, color 0.15s ease;
    }
    .vtu-service-item:hover .service-arrow {
        color: #ffffff;
        transform: translateX(3px);
    }

    /* Education Shelf */
    .vtu-edu-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 10px;
    }
    .vtu-edu-card {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 12px;
        padding: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.15s ease;
    }
    .vtu-edu-card:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(59, 130, 246, 0.3);
        transform: translateY(-2px);
    }
    .edu-img {
        width: 34px;
        height: 34px;
        object-fit: contain;
        border-radius: 8px;
    }
    .edu-info {
        min-width: 0;
    }
    .edu-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: #ffffff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .edu-sub {
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.5);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Empty state */
    .vtu-empty-box {
        padding: 36px 20px;
    }
    .vtu-empty-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: rgba(255, 255, 255, 0.4);
        margin: 0 auto 12px;
    }

    @media (max-width: 1200px) {
        .vtu-services-grid { grid-template-columns: repeat(3, 1fr); }
        .vtu-edu-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 991px) {
        .border-lg-right { border-right: none !important; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 16px; margin-bottom: 16px; }
        .vtu-services-grid { grid-template-columns: repeat(2, 1fr); }
        .vtu-edu-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 576px) {
        .vtu-stats-strip { grid-template-columns: 1fr; }
        .vtu-services-grid { grid-template-columns: 1fr; }
        .vtu-edu-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush
