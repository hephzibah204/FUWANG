@extends('layouts.nexus')

@section('title', 'Business Intelligence Dashboard | ' . config('app.name'))

@section('content')
@php
    $stats = $dashboardData['stats'] ?? [];
    $revenue_by_service = $dashboardData['revenue_by_service'] ?? collect();
    $revenueLabels = $dashboardData['revenueLabels'] ?? [];
    $revenueValues = $dashboardData['revenueValues'] ?? [];
    $userGrowthLabels = $dashboardData['userGrowthLabels'] ?? [];
    $userGrowthValues = $dashboardData['userGrowthValues'] ?? [];
    $verificationHistoryLabels = $dashboardData['verificationHistoryLabels'] ?? [];
    $verificationSuccessValues = $dashboardData['verificationSuccessValues'] ?? [];
    $verificationFailedValues = $dashboardData['verificationFailedValues'] ?? [];

    // Traffic metrics
    $trafficToday = $dashboardData['traffic_today'] ?? 0;
    $uniqueVisitorsToday = $dashboardData['unique_visitors_today'] ?? 0;
    $traffic30d = $dashboardData['traffic_30d'] ?? 0;
    $uniqueVisitors30d = $dashboardData['unique_visitors_30d'] ?? 0;
    $trafficOriginLabels = $dashboardData['trafficOriginLabels'] ?? [];
    $trafficOriginValues = $dashboardData['trafficOriginValues'] ?? [];
    $trafficDaysLabels = $dashboardData['trafficDaysLabels'] ?? [];
    $trafficViewsValues = $dashboardData['trafficViewsValues'] ?? [];
    $trafficUniquesValues = $dashboardData['trafficUniquesValues'] ?? [];
    $topPages = $dashboardData['topPages'] ?? collect();
    $topReferrers = $dashboardData['topReferrers'] ?? collect();

    // Search keywords
    $searchKeywords = $dashboardData['searchKeywords'] ?? collect();
    $totalSearches30d = $dashboardData['totalSearches30d'] ?? 0;
    $totalSearchesToday = $dashboardData['totalSearchesToday'] ?? 0;

    // API Provider Balances
    $providerBalances = $dashboardData['providerBalances'] ?? [];

    // Investment vs. Made Stats
    $investmentStats = $dashboardData['investmentStats'] ?? [];
    $apiCenter = $dashboardData['apiCenter'] ?? null;
@endphp

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center justify-content-between mb-4 shadow-sm" role="alert">
        <div>
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" data-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="mb-1 fw-bold text-white">Business Intelligence Dashboard</h2>
        <div class="text-muted small">Live analytics, transactions, traffic originations & search intent</div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.dashboard', ['refresh' => 1]) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Refresh Live Stats</span>
        </a>
    </div>
</div>

{{-- API PROVIDER BALANCE MONITOR --}}
<div class="bi-panel mb-4">
    <div class="bi-panel-header d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 border-bottom border-white-10">
        <div class="d-flex align-items-center gap-2">
            <div class="bi-icon-box text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>
            <div>
                <h5 class="mb-0 fw-bold text-white">API Provider Balance Monitor</h5>
                <small class="text-muted">Live external API balances for continuous identity & NIN services</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="text-muted small" id="provider-balance-checked-at">Last checked: {{ $providerBalances['checked_at'] ?? now()->format('h:i A') }}</span>
            <button type="button" id="btn-refresh-balances" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 shadow-sm">
                <svg id="refresh-spinner-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Check Live Balances</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#editApiKeysModal" data-toggle="modal" data-target="#editApiKeysModal">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                <span>Configure Keys</span>
            </button>
            <a href="{{ route('admin.settings.index') }}" class="btn btn-sm btn-outline-secondary">API Settings</a>
        </div>
    </div>
    <div class="bi-panel-body p-3">
        <div class="row g-3">
            {{-- DataVerify Card --}}
            <div class="col-lg-3 col-md-6">
                <div class="bi-provider-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <div class="bi-provider-name">DataVerify API</div>
                                <div class="bi-provider-service">NIN &bull; BVN Retrieval</div>
                            </div>
                            <span id="dataverify-badge" class="bi-status-badge {{ $providerBalances['dataverify']['badge_class'] ?? 'bg-secondary' }}">
                                <span class="bi-status-dot"></span>
                                <span class="bi-status-text">{{ $providerBalances['dataverify']['status_label'] ?? 'Unknown' }}</span>
                            </span>
                        </div>
                        <div class="bi-balance-wrap my-2">
                            <div class="bi-balance-val" id="dataverify-balance-val">
                                {{ $providerBalances['dataverify']['formatted'] ?? '—' }}
                            </div>
                        </div>
                    </div>
                    <div class="bi-card-footer d-flex justify-content-between align-items-center text-muted small mt-2 pt-2 border-top border-white-10">
                        <span id="dataverify-msg" class="text-truncate me-2" style="max-width: 65%;">{{ $providerBalances['dataverify']['message'] ?? '' }}</span>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            @if(empty($providerBalances['dataverify']['configured']) || ($providerBalances['dataverify']['status'] ?? '') === 'error')
                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 11px;" data-bs-toggle="modal" data-bs-target="#editApiKeysModal" data-toggle="modal" data-target="#editApiKeysModal">Edit Key</button>
                            @endif
                            <a href="https://dataverify.org" target="_blank" rel="noopener" class="bi-link">Top Up &nearr;</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RobostTech Card --}}
            <div class="col-lg-3 col-md-6">
                <div class="bi-provider-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <div class="bi-provider-name">RobostTech API</div>
                                <div class="bi-provider-service">NIN &bull; Validation &bull; Clearance</div>
                            </div>
                            <span id="robost-badge" class="bi-status-badge {{ $providerBalances['robosttech']['badge_class'] ?? 'bg-secondary' }}">
                                <span class="bi-status-dot"></span>
                                <span class="bi-status-text">{{ $providerBalances['robosttech']['status_label'] ?? 'Unknown' }}</span>
                            </span>
                        </div>
                        <div class="bi-balance-wrap my-2">
                            <div class="bi-balance-val" id="robost-balance-val">
                                {{ $providerBalances['robosttech']['formatted'] ?? '—' }}
                            </div>
                        </div>
                    </div>
                    <div class="bi-card-footer d-flex justify-content-between align-items-center text-muted small mt-2 pt-2 border-top border-white-10">
                        <span id="robost-msg" class="text-truncate me-2" style="max-width: 65%;">{{ $providerBalances['robosttech']['message'] ?? '' }}</span>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            @if(empty($providerBalances['robosttech']['configured']) || ($providerBalances['robosttech']['status'] ?? '') === 'error')
                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 11px;" data-bs-toggle="modal" data-bs-target="#editApiKeysModal" data-toggle="modal" data-target="#editApiKeysModal">Configure Key</button>
                            @endif
                            <a href="https://robosttech.com" target="_blank" rel="noopener" class="bi-link">Top Up &nearr;</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Vuvaa Card --}}
            <div class="col-lg-3 col-md-6">
                <div class="bi-provider-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <div class="bi-provider-name">VUVAA API</div>
                                <div class="bi-provider-service">In-Person &bull; Selfie Verification</div>
                            </div>
                            <span id="vuvaa-badge" class="bi-status-badge {{ $providerBalances['vuvaa']['badge_class'] ?? 'bg-secondary' }}">
                                <span class="bi-status-dot"></span>
                                <span class="bi-status-text">{{ $providerBalances['vuvaa']['status_label'] ?? 'Unknown' }}</span>
                            </span>
                        </div>
                        <div class="bi-balance-wrap my-2">
                            <div class="bi-balance-val" id="vuvaa-balance-val">
                                {{ $providerBalances['vuvaa']['formatted'] ?? '—' }}
                            </div>
                        </div>
                    </div>
                    <div class="bi-card-footer d-flex justify-content-between align-items-center text-muted small mt-2 pt-2 border-top border-white-10">
                        <span id="vuvaa-msg" class="text-truncate me-2" style="max-width: 65%;">{{ $providerBalances['vuvaa']['message'] ?? '' }}</span>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            @if(empty($providerBalances['vuvaa']['configured']) || ($providerBalances['vuvaa']['status'] ?? '') === 'error')
                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 11px;" data-bs-toggle="modal" data-bs-target="#editApiKeysModal" data-toggle="modal" data-target="#editApiKeysModal">Configure</button>
                            @endif
                            <a href="https://premiere.vuvaa.com" target="_blank" rel="noopener" class="bi-link">Portal &nearr;</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Datasub247 Card --}}
            <div class="col-lg-3 col-md-6">
                <div class="bi-provider-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <div class="bi-provider-name">Datasub247 API</div>
                                <div class="bi-provider-service">VTU &bull; Bill Payments</div>
                            </div>
                            <span id="datasub-badge" class="bi-status-badge {{ $providerBalances['datasub247']['badge_class'] ?? 'bg-secondary' }}">
                                <span class="bi-status-dot"></span>
                                <span class="bi-status-text">{{ $providerBalances['datasub247']['status_label'] ?? 'Unknown' }}</span>
                            </span>
                        </div>
                        <div class="bi-balance-wrap my-2">
                            <div class="bi-balance-val" id="datasub-balance-val">
                                {{ $providerBalances['datasub247']['formatted'] ?? '—' }}
                            </div>
                        </div>
                    </div>
                    <div class="bi-card-footer d-flex justify-content-between align-items-center text-muted small mt-2 pt-2 border-top border-white-10">
                        <span id="datasub-msg" class="text-truncate me-2" style="max-width: 65%;">{{ $providerBalances['datasub247']['message'] ?? '' }}</span>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            @if(empty($providerBalances['datasub247']['configured']) || ($providerBalances['datasub247']['status'] ?? '') === 'error')
                                <a href="{{ route('admin.custom_apis.index') }}" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 11px;">Configure</a>
                            @endif
                            <a href="https://datasub247.com" target="_blank" rel="noopener" class="bi-link">Top Up &nearr;</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION: CAPITAL INVESTMENT VS REVENUE MADE (PROFITABILITY & ROI ANALYSIS) --}}
<div class="bi-panel mb-4">
    <div class="bi-panel-header d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 border-bottom border-white-10">
        <div class="d-flex align-items-center gap-2">
            <div class="bi-icon-box text-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h5 class="mb-0 fw-bold text-white">Investment vs. Revenue Analysis</h5>
                <small class="text-muted">Compare capital & provider API investments against gross sales to calculate net profit & ROI</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#editInvestmentModal" data-toggle="modal" data-target="#editInvestmentModal">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Set Capital Invested</span>
            </button>
        </div>
    </div>
    <div class="bi-panel-body p-3">
        <div class="row g-3 mb-3">
            {{-- Total Invested Card --}}
            <div class="col-lg-3 col-sm-6">
                <div class="bi-subcard h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="bi-label">Total Invested</span>
                        <span class="bi-pill bi-pill-primary">Outlay</span>
                    </div>
                    <div class="bi-value text-white">
                        NGN {{ number_format($investmentStats['invested'] ?? 0, 2) }}
                    </div>
                    <div class="text-muted small mt-1">
                        @if(($investmentStats['manual_capital'] ?? 0) > 0)
                            Manual capital recorded
                        @elseif(($investmentStats['auto_fulfillment_cost'] ?? 0) > 0)
                            Est. API fulfillment costs
                        @else
                            <a href="#" data-bs-toggle="modal" data-bs-target="#editInvestmentModal" data-toggle="modal" data-target="#editInvestmentModal" class="bi-link">Set capital investment &rarr;</a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Total Made (Revenue) Card --}}
            <div class="col-lg-3 col-sm-6">
                <div class="bi-subcard h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="bi-label">Total Made</span>
                        <span class="bi-pill bi-pill-success">Gross Sales</span>
                    </div>
                    <div class="bi-value text-success">
                        NGN {{ number_format($investmentStats['made'] ?? 0, 2) }}
                    </div>
                    <div class="text-muted small mt-1">
                        Services & accreditations
                    </div>
                </div>
            </div>

            {{-- Net Profit Card --}}
            <div class="col-lg-3 col-sm-6">
                <div class="bi-subcard h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="bi-label">Net Profit</span>
                        @if($investmentStats['has_capital'] ?? false)
                            <span class="bi-pill {{ ($investmentStats['is_profitable'] ?? false) ? 'bi-pill-success' : 'bi-pill-danger' }}">
                                {{ ($investmentStats['is_profitable'] ?? false) ? 'Profitable' : 'Deficit' }}
                            </span>
                        @else
                            <span class="bi-pill bi-pill-secondary">
                                Awaiting Outlay
                            </span>
                        @endif
                    </div>
                    @if($investmentStats['has_capital'] ?? false)
                        <div class="bi-value {{ ($investmentStats['is_profitable'] ?? false) ? 'text-success' : 'text-danger' }}">
                            {{ ($investmentStats['net_profit'] ?? 0) < 0 ? '-' : '' }}NGN {{ number_format(abs($investmentStats['net_profit'] ?? 0), 2) }}
                        </div>
                        <div class="text-muted small mt-1">
                            Margin: <strong class="text-white">{{ number_format($investmentStats['profit_margin'] ?? 0, 1) }}%</strong>
                        </div>
                    @else
                        <div class="bi-value text-white">
                            NGN {{ number_format($investmentStats['made'] ?? 0, 2) }}
                        </div>
                        <div class="text-muted small mt-1">
                            Gross turnover before outlay deduction
                        </div>
                    @endif
                </div>
            </div>

            {{-- ROI Card --}}
            <div class="col-lg-3 col-sm-6">
                <div class="bi-subcard h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="bi-label">Return on Investment</span>
                        @if($investmentStats['has_capital'] ?? false)
                            <span class="bi-pill {{ ($investmentStats['roi'] ?? 0) >= 0 ? 'bi-pill-success' : 'bi-pill-danger' }}">
                                ROI Ratio
                            </span>
                        @else
                            <span class="bi-pill bi-pill-secondary">
                                Pending
                            </span>
                        @endif
                    </div>
                    @if($investmentStats['has_capital'] ?? false)
                        <div class="bi-value {{ ($investmentStats['roi'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ ($investmentStats['roi'] ?? 0) > 0 ? '+' : '' }}{{ number_format($investmentStats['roi'] ?? 0, 1) }}%
                        </div>
                        <div class="text-muted small mt-1">
                            {{ ($investmentStats['is_profitable'] ?? false) ? 'Capital returned + surplus' : 'Recovering capital' }}
                        </div>
                    @else
                        <div class="bi-value text-muted">
                            —
                        </div>
                        <div class="text-muted small mt-1">
                            Set capital invested to compute ROI
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Visual Ratio Bar --}}
        @php
            $invAmount = max(0, (float) ($investmentStats['invested'] ?? 0));
            $madeAmount = max(0, (float) ($investmentStats['made'] ?? 0));
            $hasCap = !empty($investmentStats['has_capital']);
            $barTotal = max(1, $invAmount + $madeAmount);
            $invPct = $hasCap ? round(($invAmount / $barTotal) * 100) : 0;
            $madePct = $hasCap ? (100 - $invPct) : 100;
        @endphp
        <div class="bi-ratio-panel p-3">
            <div class="d-flex justify-content-between align-items-center mb-2 small flex-wrap gap-2">
                @if($hasCap)
                    <div class="d-flex align-items-center gap-2">
                        <span class="bi-indicator-dot bg-primary"></span>
                        <span class="text-white">Invested: <strong>NGN {{ number_format($invAmount, 2) }}</strong> ({{ $invPct }}%)</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="bi-indicator-dot bg-success"></span>
                        <span class="text-white">Made (Revenue): <strong>NGN {{ number_format($madeAmount, 2) }}</strong> ({{ $madePct }}%)</span>
                    </div>
                @else
                    <div class="d-flex align-items-center gap-2">
                        <span class="bi-indicator-dot bg-secondary"></span>
                        <span class="text-muted">Capital Invested: <em>Not recorded</em></span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="bi-indicator-dot bg-success"></span>
                        <span class="text-white">Gross Realized Revenue: <strong>NGN {{ number_format($madeAmount, 2) }}</strong></span>
                    </div>
                @endif
            </div>
            <div class="progress bi-progress-track">
                @if($hasCap)
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $invPct }}%;" aria-valuenow="{{ $invPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $madePct }}%;" aria-valuenow="{{ $madePct }}" aria-valuemin="0" aria-valuemax="100"></div>
                @else
                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                @endif
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2 text-muted" style="font-size: 11.5px;">
                <span>Total Capital / Provider Outlay</span>
                <span>Gross Realized Turnover</span>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: Set Capital Invested --}}
<div class="modal fade" id="editInvestmentModal" tabindex="-1" aria-labelledby="editInvestmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.capital_investment.update') }}" class="modal-content bi-modal border-0 shadow">
            @csrf
            <div class="modal-header border-bottom border-white-10">
                <h5 class="modal-title fw-bold text-white" id="editInvestmentModalLabel">Set Capital Invested</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Record your total capital investment (e.g. initial capital injected, bank transfers to DataVerify/RobostTech/VTU provider wallets). The dashboard will use this to accurately compute Net Profit & ROI.
                </p>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-white">Total Capital Invested (NGN)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-muted">NGN</span>
                        <input type="number" step="0.01" min="0" name="amount" class="form-control font-monospace bg-dark border-secondary text-white" value="{{ $investmentStats['manual_capital'] ?? '' }}" placeholder="e.g. 200000" required>
                    </div>
                    <div class="form-text text-muted small">Enter your actual capital outlay to calculate accurate net profit and ROI.</div>
                </div>
            </div>
            <div class="modal-footer border-top border-white-10">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary px-3">Save Capital Investment</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: Configure API Keys --}}
<div class="modal fade" id="editApiKeysModal" tabindex="-1" aria-labelledby="editApiKeysModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" action="{{ route('admin.api_credentials.update') }}" class="modal-content bi-modal border-0 shadow">
            @csrf
            <div class="modal-header border-bottom border-white-10">
                <h5 class="modal-title fw-bold text-white" id="editApiKeysModalLabel">Configure Provider API Credentials</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Enter your live API credentials for DataVerify and RobostTech to enable real-time wallet balance monitoring and automated verifications.
                </p>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-white">DataVerify API Key</label>
                        <input type="text" name="dataverify_api_key" class="form-control font-monospace bg-dark border-secondary text-white" value="{{ $apiCenter->dataverify_api_key ?? '' }}" placeholder="Paste your DataVerify API Key">
                        <div class="form-text text-muted small">Used for NIN, BVN, and VNIN retrieval. Header authentication will be applied automatically.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-white">RobostTech API Key</label>
                        <input type="text" name="robosttech_api_key" class="form-control font-monospace bg-dark border-secondary text-white" value="{{ $apiCenter->robosttech_api_key ?? '' }}" placeholder="Paste your RobostTech API Key">
                        <div class="form-text text-muted small">Used for NIN validation, clearance, and personalization.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-white">RobostTech NIN / Base Endpoint</label>
                        <input type="text" name="robosttech_endpoint_nin" class="form-control font-monospace bg-dark border-secondary text-white" value="{{ $apiCenter->robosttech_endpoint_nin ?? 'https://robosttech.com/api' }}" placeholder="https://robosttech.com/api">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-white">RobostTech Validation Endpoint</label>
                        <input type="text" name="robosttech_endpoint_validation" class="form-control font-monospace bg-dark border-secondary text-white" value="{{ $apiCenter->robosttech_endpoint_validation ?? 'https://robosttech.com/api' }}" placeholder="https://robosttech.com/api">
                    </div>
                    <div class="col-12 pt-2 border-top border-white-10">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-bold small text-white">VUVAA Credentials</span>
                            <span class="badge bg-secondary-subtle text-secondary small">Demo / Live</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-white">VUVAA Username</label>
                        <input type="text" name="vuvaa_username" class="form-control font-monospace bg-dark border-secondary text-white" value="{{ \App\Models\SystemSetting::get('vuvaa_username', 'fuwa_demo_0417190741') }}" placeholder="fuwa_demo_0417190741">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-white">VUVAA Password</label>
                        <input type="text" name="vuvaa_password" class="form-control font-monospace bg-dark border-secondary text-white" value="{{ \App\Models\SystemSetting::get('vuvaa_password', 'Password') }}" placeholder="Password">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-white">VUVAA Base Endpoint</label>
                        <input type="text" name="vuvaa_endpoint" class="form-control font-monospace bg-dark border-secondary text-white" value="{{ \App\Models\SystemSetting::get('vuvaa_endpoint', 'https://premiere.vuvaa.com/demo/NIN_Validation_LIVE') }}" placeholder="https://premiere.vuvaa.com/demo/NIN_Validation_LIVE">
                        <div class="form-text text-muted small">Default demo endpoint: <code>https://premiere.vuvaa.com/demo/NIN_Validation_LIVE</code></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top border-white-10">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary px-3">Save & Refresh Balances</button>
            </div>
        </form>
    </div>
</div>

{{-- SECTION 1: PRIMARY FINANCIAL & OPERATIONAL KPIS --}}
<div class="row g-3 mb-3">
    <div class="col-lg-3 col-sm-6">
        <div class="bi-subcard h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="bi-label">Total Revenue</span>
                <span class="bi-pill bi-pill-success">Live</span>
            </div>
            <div class="bi-value text-white">NGN {{ number_format($stats['total_revenue'] ?? 0, 2) }}</div>
            <div class="text-muted small mt-1">Service debits & accreditations</div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="bi-subcard h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="bi-label">Total Transactions</span>
                <a href="{{ route('admin.transactions.index') }}" class="bi-link">View &rarr;</a>
            </div>
            <div class="bi-value text-white">{{ number_format($stats['total_transactions'] ?? 0) }}</div>
            <div class="text-muted small mt-1">All platform transactions</div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="bi-subcard h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="bi-label">Total Users</span>
                <a href="{{ route('admin.users.index') }}" class="bi-link">Manage &rarr;</a>
            </div>
            <div class="bi-value text-white">{{ number_format($stats['total_users'] ?? 0) }}</div>
            <div class="text-muted small mt-1">Registered user accounts</div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="bi-subcard h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="bi-label">Total Verifications</span>
                <a href="{{ route('admin.verifications.index') }}" class="bi-link">Records &rarr;</a>
            </div>
            <div class="bi-value text-white">{{ number_format($stats['total_verifications'] ?? 0) }}</div>
            <div class="text-muted small mt-1">All-time identity checks</div>
        </div>
    </div>
</div>

{{-- SECTION 2: TODAY'S ACTIVITY --}}
<div class="row g-3 mb-4">
    <div class="col-lg-4 col-md-6">
        <div class="bi-subcard h-100">
            <div class="bi-label mb-2">Today's Verifications</div>
            <div class="d-flex align-items-baseline gap-2">
                <div class="bi-value text-white">{{ number_format($stats['daily_verifications'] ?? 0) }}</div>
                <span class="text-muted small">requests</span>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="bi-subcard h-100">
            <div class="bi-label mb-2 text-success">Today's Success</div>
            <div class="d-flex align-items-baseline gap-2">
                <div class="bi-value text-success">{{ number_format($stats['daily_success_verifications'] ?? 0) }}</div>
                <span class="text-muted small">completed</span>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12">
        <div class="bi-subcard h-100">
            <div class="bi-label mb-2 text-danger">Today's Failed / Pending</div>
            <div class="d-flex align-items-baseline gap-2">
                <div class="bi-value text-danger">{{ number_format($stats['daily_failed_verifications'] ?? 0) }}</div>
                <span class="text-muted small">unresolved</span>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 3: REVENUE & USER GROWTH CHARTS --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="bi-panel h-100">
            <div class="bi-panel-header d-flex justify-content-between align-items-center p-3 border-bottom border-white-10">
                <h5 class="mb-0 fw-bold text-white">Revenue by Service</h5>
                <span class="text-muted small">Debits & Products</span>
            </div>
            <div class="bi-panel-body p-3">
                @if(empty($revenueValues) || array_sum($revenueValues) <= 0)
                    <div class="d-flex flex-column align-items-center justify-content-center text-muted" style="min-height: 280px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="mb-2 opacity-50" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                        </svg>
                        <div class="small">No service revenue recorded yet</div>
                    </div>
                @else
                    <div style="position: relative; height: 280px; width: 100%;">
                        <canvas id="revenue-by-service-chart"></canvas>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="bi-panel h-100">
            <div class="bi-panel-header d-flex justify-content-between align-items-center p-3 border-bottom border-white-10">
                <h5 class="mb-0 fw-bold text-white">User Growth (Last 30 Days)</h5>
                <span class="text-muted small">Daily Signups</span>
            </div>
            <div class="bi-panel-body p-3">
                <div style="position: relative; height: 280px; width: 100%;">
                    <canvas id="user-growth-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 4: VERIFICATION HISTORY CHART --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="bi-panel">
            <div class="bi-panel-header d-flex justify-content-between align-items-center p-3 border-bottom border-white-10">
                <h5 class="mb-0 fw-bold text-white">Verification History (Last 30 Days)</h5>
                <span class="text-muted small">Success vs. Failed checks</span>
            </div>
            <div class="bi-panel-body p-3">
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="verification-history-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 5: WEBSITE TRAFFIC & ORIGINATIONS --}}
<div class="bi-panel mb-4">
    <div class="bi-panel-header d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 border-bottom border-white-10">
        <div>
            <h5 class="mb-0 fw-bold text-white">Website Traffic & Traffic Originations</h5>
            <div class="text-muted small">Visitors, referral channels, and landing page discovery</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="bi-pill bi-pill-primary">
                Today: <strong>{{ number_format($trafficToday) }}</strong> views ({{ number_format($uniqueVisitorsToday) }} visitors)
            </span>
            <span class="bi-pill bi-pill-secondary">
                30 Days: <strong>{{ number_format($traffic30d) }}</strong> views ({{ number_format($uniqueVisitors30d) }} visitors)
            </span>
        </div>
    </div>
    <div class="bi-panel-body p-3">
        <div class="row g-3 mb-4">
            {{-- Traffic Originations Doughnut --}}
            <div class="col-lg-5">
                <div class="bi-subcard h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="bi-label">Traffic Channels / Sources</span>
                        <span class="small text-muted">Last 30 Days</span>
                    </div>
                    @if(empty($trafficOriginValues) || array_sum($trafficOriginValues) <= 0)
                        <div class="d-flex flex-column align-items-center justify-content-center text-muted" style="min-height: 240px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="mb-2 opacity-50">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="small">Collecting incoming traffic channels...</div>
                        </div>
                    @else
                        <div style="position: relative; height: 240px; width: 100%;">
                            <canvas id="traffic-origination-chart"></canvas>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 30-Day Daily Traffic Timeline --}}
            <div class="col-lg-7">
                <div class="bi-subcard h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="bi-label">Daily Page Views & Unique Visitors</span>
                        <span class="small text-muted">Trends</span>
                    </div>
                    <div style="position: relative; height: 240px; width: 100%;">
                        <canvas id="traffic-timeline-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Referrers & Top Pages Tables --}}
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="bi-subcard h-100">
                    <div class="bi-label mb-2">Top Referrer Domains</div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 bi-table">
                            <thead>
                                <tr>
                                    <th>Referring Source</th>
                                    <th>Category</th>
                                    <th class="text-end">Visits</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topReferrers as $ref)
                                    <tr>
                                        <td>
                                            <span class="fw-medium text-white">{{ $ref->referrer_host }}</span>
                                        </td>
                                        <td>
                                            <span class="bi-pill bi-pill-secondary">{{ $ref->origination_source }}</span>
                                        </td>
                                        <td class="text-end fw-semibold text-white">{{ number_format($ref->total) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted small py-3">
                                            Direct visits and new traffic will appear here as visitors arrive.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="bi-subcard h-100">
                    <div class="bi-label mb-2">Top Visited Landing Pages</div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 bi-table">
                            <thead>
                                <tr>
                                    <th>Page Path</th>
                                    <th class="text-end">Views</th>
                                    <th class="text-end">Uniques</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topPages as $page)
                                    <tr>
                                        <td>
                                            <code class="text-primary bg-transparent">{{ $page->path }}</code>
                                        </td>
                                        <td class="text-end fw-semibold text-white">{{ number_format($page->views) }}</td>
                                        <td class="text-end text-muted">{{ number_format($page->uniques) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted small py-3">
                                            Page visit metrics will populate here as pages are browsed.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 6: SEARCH KEYWORDS & INTENT ANALYTICS --}}
<div class="bi-panel mb-4">
    <div class="bi-panel-header d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 border-bottom border-white-10">
        <div>
            <h5 class="mb-0 fw-bold text-white">Search Keywords & User Intent Analytics</h5>
            <div class="text-muted small">Search terms entered across Search Hub, portal search bars, and search engine referrals</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="bi-pill bi-pill-primary">
                Searches Today: <strong>{{ number_format($totalSearchesToday) }}</strong>
            </span>
            <span class="bi-pill bi-pill-secondary">
                30-Day Searches: <strong>{{ number_format($totalSearches30d) }}</strong>
            </span>
        </div>
    </div>
    <div class="bi-panel-body p-3">
        @if($searchKeywords->isEmpty())
            <div class="text-center py-4 text-muted">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="mb-2 opacity-50">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <div class="fw-semibold text-white">No Search Queries Recorded Yet</div>
                <div class="small">Queries typed in the Search Hub or incoming search engine keywords are automatically logged here in real-time.</div>
            </div>
        @else
            {{-- Keyword cloud badges --}}
            <div class="mb-3 p-3 bi-subcard">
                <div class="bi-label mb-2">Trending Search Terms</div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($searchKeywords as $kw)
                        <span class="bi-kw-pill">
                            <span class="text-white">{{ $kw->term }}</span>
                            <span class="bi-kw-count">{{ $kw->total }}</span>
                        </span>
                    @endforeach
                </div>
            </div>

            {{-- Keyword detailed table --}}
            <div class="table-responsive bi-subcard p-0 overflow-hidden">
                <table class="table align-middle mb-0 bi-table">
                    <thead>
                        <tr>
                            <th>Keyword / Query</th>
                            <th>Primary Origin</th>
                            <th class="text-center">Search Count</th>
                            <th class="text-end">Last Searched</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($searchKeywords as $kw)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa fa-search text-muted small"></i>
                                        <span class="fw-semibold text-white">{{ $kw->term }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($kw->source === 'search_hub')
                                        <span class="bi-pill bi-pill-primary">Search Hub (Navigation)</span>
                                    @elseif($kw->source === 'search_engine_referer')
                                        <span class="bi-pill bi-pill-success">Search Engine (Google/Bing)</span>
                                    @elseif($kw->source === 'utm_campaign')
                                        <span class="bi-pill bi-pill-warning">Ad Campaign Keyword</span>
                                    @else
                                        <span class="bi-pill bi-pill-secondary">{{ $kw->source }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-white">{{ number_format($kw->total) }}</span>
                                </td>
                                <td class="text-end text-muted small">
                                    {{ \Carbon\Carbon::parse($kw->last_searched)->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    /* ========================================================
       Fuwa.NG Un-slopped Business Intelligence Theme Styles
       ======================================================== */
    .border-white-10 {
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    .bi-panel {
        background: rgba(15, 23, 42, 0.55);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        overflow: hidden;
        backdrop-filter: blur(8px);
    }

    .bi-panel-header {
        background: rgba(255, 255, 255, 0.02);
    }

    .bi-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .bi-provider-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 14px;
        padding: 16px;
        transition: border-color 0.2s ease, transform 0.2s ease;
    }

    .bi-provider-card:hover {
        border-color: rgba(255, 255, 255, 0.15);
    }

    .bi-provider-name {
        font-weight: 700;
        font-size: 0.95rem;
        color: #f8fafc;
        line-height: 1.25;
    }

    .bi-provider-service {
        font-size: 0.72rem;
        color: #94a3b8;
        font-weight: 500;
        margin-top: 2px;
        letter-spacing: 0.02em;
    }

    .bi-balance-val {
        font-size: 1.35rem;
        font-weight: 800;
        color: #ffffff;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .bi-subcard {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 14px;
        padding: 16px;
    }

    .bi-label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94a3b8;
    }

    .bi-value {
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .bi-ratio-panel {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 12px;
    }

    .bi-progress-track {
        height: 10px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 9999px;
        overflow: hidden;
    }

    .bi-indicator-dot {
        width: 8px;
        height: 8px;
        border-radius: 9999px;
        display: inline-block;
    }

    /* Refined Status Pill */
    .bi-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 9999px;
        line-height: 1.3;
        white-space: nowrap;
        flex-shrink: 0;
        border: 1px solid transparent;
        margin: 0;
    }

    .bi-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 9999px;
        background: currentColor;
        flex-shrink: 0;
    }

    .bi-status-badge.bg-success {
        background: rgba(16, 185, 129, 0.15) !important;
        border-color: rgba(16, 185, 129, 0.3) !important;
        color: #34d399 !important;
    }

    .bi-status-badge.bg-warning,
    .bi-status-badge.text-dark {
        background: rgba(245, 158, 11, 0.15) !important;
        border-color: rgba(245, 158, 11, 0.3) !important;
        color: #fbbf24 !important;
    }

    .bi-status-badge.bg-danger {
        background: rgba(239, 68, 68, 0.15) !important;
        border-color: rgba(239, 68, 68, 0.3) !important;
        color: #f87171 !important;
    }

    .bi-status-badge.bg-secondary {
        background: rgba(148, 163, 184, 0.12) !important;
        border-color: rgba(148, 163, 184, 0.25) !important;
        color: #94a3b8 !important;
    }

    /* Refined Contextual Pills */
    .bi-pill {
        display: inline-flex;
        align-items: center;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 9999px;
        line-height: 1.4;
        margin: 0;
    }

    .bi-pill-primary {
        background: rgba(59, 130, 246, 0.15);
        color: #60a5fa;
        border: 1px solid rgba(59, 130, 246, 0.25);
    }

    .bi-pill-success {
        background: rgba(16, 185, 129, 0.15);
        color: #34d399;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }

    .bi-pill-warning {
        background: rgba(245, 158, 11, 0.15);
        color: #fbbf24;
        border: 1px solid rgba(245, 158, 11, 0.25);
    }

    .bi-pill-danger {
        background: rgba(239, 68, 68, 0.15);
        color: #f87171;
        border: 1px solid rgba(239, 68, 68, 0.25);
    }

    .bi-pill-secondary {
        background: rgba(148, 163, 184, 0.12);
        color: #94a3b8;
        border: 1px solid rgba(148, 163, 184, 0.2);
    }

    /* Keyword Pill */
    .bi-kw-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 9999px;
        padding: 4px 12px;
        font-size: 0.8rem;
    }

    .bi-kw-count {
        background: rgba(59, 130, 246, 0.25);
        color: #93c5fd;
        border-radius: 9999px;
        padding: 1px 6px;
        font-size: 0.72rem;
        font-weight: 700;
    }

    /* Table styles */
    .bi-table {
        color: #e2e8f0 !important;
    }

    .bi-table thead th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94a3b8;
        background: rgba(255, 255, 255, 0.02);
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        padding: 10px 12px;
    }

    .bi-table tbody td {
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        padding: 10px 12px;
    }

    .bi-table tbody tr:hover {
        background: rgba(255, 255, 255, 0.02);
    }

    .bi-link {
        color: #60a5fa;
        text-decoration: none;
        font-size: 0.75rem;
        font-weight: 500;
        transition: color 0.15s ease;
    }

    .bi-link:hover {
        color: #93c5fd;
        text-decoration: underline;
    }

    /* Dark Modals */
    .bi-modal {
        background: #0f172a !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        border-radius: 16px;
    }

    @keyframes spin-kf {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@push('scripts')
<script nonce="{{ $cspNonce ?? '' }}" src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script nonce="{{ $cspNonce ?? '' }}">
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart !== 'undefined') {
            // Chart.js dark theme defaults
            Chart.defaults.color = '#94a3b8';
            Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.06)';

            // Universal palettes
            var serviceColors = [
                '#3b82f6', '#10b981', '#06b6d4', '#f59e0b', '#ef4444', 
                '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1'
            ];

            var originationColors = [
                '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', 
                '#06b6d4', '#ec4899', '#14b8a6', '#6366f1', '#f97316'
            ];

            // 1. Revenue by Service Chart
            var revenueCanvas = document.getElementById('revenue-by-service-chart');
            if (revenueCanvas) {
                var revenueCtx = revenueCanvas.getContext('2d');
                var revenueLabels = {!! json_encode($revenueLabels) !!};
                var revenueValues = {!! json_encode($revenueValues) !!};

                new Chart(revenueCtx, {
                    type: 'doughnut',
                    data: {
                        labels: revenueLabels,
                        datasets: [{
                            data: revenueValues,
                            backgroundColor: serviceColors.slice(0, Math.max(revenueValues.length, 1)),
                            borderWidth: 2,
                            borderColor: '#0f172a'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 12,
                                    font: { size: 11 },
                                    color: '#cbd5e1'
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        var val = Number(context.raw || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                        return ' ' + context.label + ': NGN ' + val;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 2. User Growth Chart
            var userGrowthCanvas = document.getElementById('user-growth-chart');
            if (userGrowthCanvas) {
                var userGrowthCtx = userGrowthCanvas.getContext('2d');
                new Chart(userGrowthCtx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($userGrowthLabels) !!},
                        datasets: [{
                            label: 'New Users',
                            data: {!! json_encode($userGrowthValues) !!},
                            backgroundColor: 'rgba(59, 130, 246, 0.12)',
                            borderColor: '#3b82f6',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.35,
                            pointRadius: 2,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#3b82f6'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10 }, color: '#94a3b8', maxTicksLimit: 10 }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { precision: 0, font: { size: 10 }, color: '#94a3b8' }
                            }
                        }
                    }
                });
            }

            // 3. Verification History Chart
            var verifCanvas = document.getElementById('verification-history-chart');
            if (verifCanvas) {
                var verifCtx = verifCanvas.getContext('2d');
                new Chart(verifCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($verificationHistoryLabels) !!},
                        datasets: [
                            {
                                label: 'Success',
                                data: {!! json_encode($verificationSuccessValues) !!},
                                backgroundColor: '#10b981',
                                borderRadius: 3
                            },
                            {
                                label: 'Failed / Other',
                                data: {!! json_encode($verificationFailedValues) !!},
                                backgroundColor: '#ef4444',
                                borderRadius: 3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { boxWidth: 12, font: { size: 11 }, color: '#cbd5e1' }
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                grid: { display: false },
                                ticks: { font: { size: 10 }, color: '#94a3b8', maxTicksLimit: 12 }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { precision: 0, font: { size: 10 }, color: '#94a3b8' }
                            }
                        }
                    }
                });
            }

            // 4. Traffic Originations Chart
            var trafficOriginCanvas = document.getElementById('traffic-origination-chart');
            if (trafficOriginCanvas) {
                var trafficOriginCtx = trafficOriginCanvas.getContext('2d');
                var trafficOriginLabels = {!! json_encode($trafficOriginLabels) !!};
                var trafficOriginValues = {!! json_encode($trafficOriginValues) !!};

                new Chart(trafficOriginCtx, {
                    type: 'doughnut',
                    data: {
                        labels: trafficOriginLabels,
                        datasets: [{
                            data: trafficOriginValues,
                            backgroundColor: originationColors.slice(0, Math.max(trafficOriginValues.length, 1)),
                            borderWidth: 2,
                            borderColor: '#0f172a'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 10, padding: 8, font: { size: 10 }, color: '#cbd5e1' }
                            }
                        }
                    }
                });
            }

            // 5. Daily Traffic Timeline Chart (Views & Uniques)
            var trafficTimelineCanvas = document.getElementById('traffic-timeline-chart');
            if (trafficTimelineCanvas) {
                var trafficTimelineCtx = trafficTimelineCanvas.getContext('2d');
                new Chart(trafficTimelineCtx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($trafficDaysLabels) !!},
                        datasets: [
                            {
                                label: 'Total Page Views',
                                data: {!! json_encode($trafficViewsValues) !!},
                                backgroundColor: 'rgba(99, 102, 241, 0.12)',
                                borderColor: '#6366f1',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.35,
                                pointRadius: 2
                            },
                            {
                                label: 'Unique Visitors',
                                data: {!! json_encode($trafficUniquesValues) !!},
                                borderColor: '#10b981',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.35,
                                pointRadius: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { boxWidth: 12, font: { size: 11 }, color: '#cbd5e1' }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10 }, color: '#94a3b8', maxTicksLimit: 10 }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { precision: 0, font: { size: 10 }, color: '#94a3b8' }
                            }
                        }
                    }
                });
            }
        } else {
            console.warn('Chart.js library not available; skipping dashboard chart rendering.');
        }

        // Live Provider Balances Refresh
        const refreshBalancesBtn = document.getElementById('btn-refresh-balances');
        if (refreshBalancesBtn) {
            refreshBalancesBtn.addEventListener('click', function() {
                const btn = this;
                const spinner = document.getElementById('refresh-spinner-icon');
                btn.disabled = true;
                if (spinner) spinner.style.animation = 'spin-kf 0.8s linear infinite';

                fetch("{{ route('admin.provider_balances', ['refresh' => 1]) }}", {
                    headers: { 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    function updateProviderCard(key, badgeId, valId, msgId) {
                        const p = data[key];
                        if (!p) return;
                        const valEl = document.getElementById(valId);
                        if (valEl) valEl.innerText = p.formatted || '—';
                        const badgeEl = document.getElementById(badgeId);
                        if (badgeEl) {
                            badgeEl.className = 'bi-status-badge ' + (p.badge_class || 'bg-secondary');
                            badgeEl.innerHTML = '<span class="bi-status-dot"></span><span class="bi-status-text">' + (p.status_label || 'Unknown') + '</span>';
                        }
                        const msgEl = document.getElementById(msgId);
                        if (msgEl) msgEl.innerText = p.message || '';
                    }

                    updateProviderCard('dataverify', 'dataverify-badge', 'dataverify-balance-val', 'dataverify-msg');
                    updateProviderCard('robosttech', 'robost-badge', 'robost-balance-val', 'robost-msg');
                    updateProviderCard('vuvaa', 'vuvaa-badge', 'vuvaa-balance-val', 'vuvaa-msg');
                    updateProviderCard('datasub247', 'datasub-badge', 'datasub-balance-val', 'datasub-msg');

                    if (data.checked_at) {
                        const checkedAt = document.getElementById('provider-balance-checked-at');
                        if (checkedAt) checkedAt.innerText = 'Last checked: ' + data.checked_at;
                    }
                })
                .catch(err => {
                    console.error('Error refreshing provider balances:', err);
                })
                .finally(() => {
                    btn.disabled = false;
                    if (spinner) spinner.style.animation = '';
                });
            });
        }
    });
</script>
@endpush
