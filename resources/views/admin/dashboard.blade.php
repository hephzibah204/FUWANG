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

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="mb-1 fw-bold">Business Intelligence Dashboard</h2>
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
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>
            <div>
                <h5 class="mb-0 fw-bold">API Provider Balance Monitor</h5>
                <small class="text-muted">Live external API balances for continuous identity & NIN services</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small" id="provider-balance-checked-at">Last checked: {{ $providerBalances['checked_at'] ?? now()->format('h:i A') }}</span>
            <button type="button" id="btn-refresh-balances" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 shadow-sm">
                <svg id="refresh-spinner-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Check Live Balances</span>
            </button>
            <a href="{{ route('admin.settings.index') }}" class="btn btn-sm btn-light border text-muted">API Settings</a>
        </div>
    </div>
    <div class="card-body pt-0 pb-3">
        <div class="row g-3">
            {{-- DataVerify Card --}}
            <div class="col-md-6">
                <div class="p-3 rounded-3 border bg-light-subtle h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-dark">DataVerify API</span>
                            <span class="badge bg-secondary-subtle text-secondary small">NIN / BVN Retrieval</span>
                        </div>
                        <span id="dataverify-badge" class="badge {{ $providerBalances['dataverify']['badge_class'] ?? 'bg-secondary' }} rounded-pill px-2 py-1 small">
                            {{ $providerBalances['dataverify']['status_label'] ?? 'Unknown' }}
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-1">
                        <h3 class="mb-0 fw-bold text-dark" id="dataverify-balance-val">
                            {{ $providerBalances['dataverify']['formatted'] ?? '—' }}
                        </h3>
                    </div>
                    <div class="d-flex justify-content-between align-items-center text-muted small mt-2">
                        <span id="dataverify-msg">{{ $providerBalances['dataverify']['message'] ?? '' }}</span>
                        <a href="https://dataverify.org" target="_blank" rel="noopener" class="text-decoration-none small">Top Up Portal &nearr;</a>
                    </div>
                </div>
            </div>

            {{-- RobostTech Card --}}
            <div class="col-md-6">
                <div class="p-3 rounded-3 border bg-light-subtle h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-dark">RobostTech API</span>
                            <span class="badge bg-secondary-subtle text-secondary small">NIN / Clearance / Validation</span>
                        </div>
                        <span id="robost-badge" class="badge {{ $providerBalances['robosttech']['badge_class'] ?? 'bg-secondary' }} rounded-pill px-2 py-1 small">
                            {{ $providerBalances['robosttech']['status_label'] ?? 'Unknown' }}
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-1">
                        <h3 class="mb-0 fw-bold text-dark" id="robost-balance-val">
                            {{ $providerBalances['robosttech']['formatted'] ?? '—' }}
                        </h3>
                    </div>
                    <div class="d-flex justify-content-between align-items-center text-muted small mt-2">
                        <span id="robost-msg">{{ $providerBalances['robosttech']['message'] ?? '' }}</span>
                        <a href="https://robosttech.com" target="_blank" rel="noopener" class="text-decoration-none small">Top Up Portal &nearr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION: CAPITAL INVESTMENT VS REVENUE MADE (PROFITABILITY & ROI ANALYSIS) --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-success-subtle text-success p-2 rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h5 class="mb-0 fw-bold">Investment vs. Revenue Analysis</h5>
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
    <div class="card-body pt-0 pb-3">
        <div class="row g-3 mb-3">
            {{-- Total Invested Card --}}
            <div class="col-lg-3 col-sm-6">
                <div class="p-3 rounded-3 border bg-light-subtle h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small text-uppercase fw-semibold">Total Invested</span>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0 small">Outlay</span>
                    </div>
                    <h3 class="mb-1 fw-bold text-dark">
                        NGN {{ number_format($investmentStats['invested'] ?? 0, 2) }}
                    </h3>
                    <div class="text-muted small">
                        @if(($investmentStats['manual_capital'] ?? 0) > 0)
                            Manual capital recorded
                        @else
                            Est. API fulfillment costs
                        @endif
                    </div>
                </div>
            </div>

            {{-- Total Made (Revenue) Card --}}
            <div class="col-lg-3 col-sm-6">
                <div class="p-3 rounded-3 border bg-light-subtle h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small text-uppercase fw-semibold">Total Made</span>
                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0 small">Gross Sales</span>
                    </div>
                    <h3 class="mb-1 fw-bold text-success">
                        NGN {{ number_format($investmentStats['made'] ?? 0, 2) }}
                    </h3>
                    <div class="text-muted small">
                        Services & accreditations
                    </div>
                </div>
            </div>

            {{-- Net Profit Card --}}
            <div class="col-lg-3 col-sm-6">
                <div class="p-3 rounded-3 border bg-light-subtle h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small text-uppercase fw-semibold">Net Profit</span>
                        <span class="badge {{ ($investmentStats['is_profitable'] ?? false) ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-pill px-2 py-0 small">
                            {{ ($investmentStats['is_profitable'] ?? false) ? 'Profitable' : 'Deficit' }}
                        </span>
                    </div>
                    <h3 class="mb-1 fw-bold {{ ($investmentStats['is_profitable'] ?? false) ? 'text-success' : 'text-danger' }}">
                        {{ ($investmentStats['net_profit'] ?? 0) < 0 ? '-' : '' }}NGN {{ number_format(abs($investmentStats['net_profit'] ?? 0), 2) }}
                    </h3>
                    <div class="text-muted small">
                        Margin: <strong>{{ number_format($investmentStats['profit_margin'] ?? 0, 1) }}%</strong>
                    </div>
                </div>
            </div>

            {{-- ROI Card --}}
            <div class="col-lg-3 col-sm-6">
                <div class="p-3 rounded-3 border bg-light-subtle h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small text-uppercase fw-semibold">Return on Investment</span>
                        <span class="badge {{ ($investmentStats['roi'] ?? 0) >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-pill px-2 py-0 small">
                            ROI Ratio
                        </span>
                    </div>
                    <h3 class="mb-1 fw-bold {{ ($investmentStats['roi'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ ($investmentStats['roi'] ?? 0) > 0 ? '+' : '' }}{{ number_format($investmentStats['roi'] ?? 0, 1) }}%
                    </h3>
                    <div class="text-muted small">
                        {{ ($investmentStats['is_profitable'] ?? false) ? 'Capital returned + surplus' : 'Recovering capital' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Visual Ratio Bar --}}
        @php
            $invAmount = max(0, (float) ($investmentStats['invested'] ?? 0));
            $madeAmount = max(0, (float) ($investmentStats['made'] ?? 0));
            $barTotal = max(1, $invAmount + $madeAmount);
            $invPct = round(($invAmount / $barTotal) * 100);
            $madePct = 100 - $invPct;
        @endphp
        <div class="p-3 rounded-3 bg-white border">
            <div class="d-flex justify-content-between align-items-center mb-2 small">
                <span>
                    <strong class="text-primary">&#9632; Invested:</strong> NGN {{ number_format($invAmount, 2) }} ({{ $invPct }}%)
                </span>
                <span>
                    <strong class="text-success">&#9632; Made (Revenue):</strong> NGN {{ number_format($madeAmount, 2) }} ({{ $madePct }}%)
                </span>
            </div>
            <div class="progress" style="height: 12px; border-radius: 6px; overflow: hidden;">
                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $invPct }}%;" aria-valuenow="{{ $invPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $madePct }}%;" aria-valuenow="{{ $madePct }}" aria-valuemin="0" aria-valuemax="100"></div>
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
        <form method="POST" action="{{ route('admin.capital_investment.update') }}" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="editInvestmentModalLabel">Set Capital Invested</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Record your total capital investment (e.g. initial capital injected, bank transfers to DataVerify/RobostTech/VTU provider wallets). The dashboard will use this to accurately compute Net Profit & ROI.
                </p>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Total Capital Invested (NGN)</label>
                    <div class="input-group">
                        <span class="input-group-text">NGN</span>
                        <input type="number" step="0.01" min="0" name="amount" class="form-control font-monospace" value="{{ $investmentStats['manual_capital'] ?? '' }}" placeholder="e.g. 200000" required>
                    </div>
                    <div class="form-text small">Enter 0 to automatically use calculated API fulfillment costs.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary px-3">Save Capital Investment</button>
            </div>
        </form>
    </div>
</div>

{{-- SECTION 1: PRIMARY FINANCIAL & OPERATIONAL KPIS --}}
<div class="row g-3 mb-3">
    <div class="col-lg-3 col-sm-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-semibold tracking-wider">Total Revenue</span>
                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1">Live</span>
                </div>
                <h3 class="mb-1 fw-bold text-dark">NGN {{ number_format($stats['total_revenue'] ?? 0, 2) }}</h3>
                <div class="text-muted small">Service debits & accreditations</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-semibold tracking-wider">Total Transactions</span>
                    <a href="{{ route('admin.transactions.index') }}" class="text-decoration-none small">View &rarr;</a>
                </div>
                <h3 class="mb-1 fw-bold text-dark">{{ number_format($stats['total_transactions'] ?? 0) }}</h3>
                <div class="text-muted small">All platform transactions</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-semibold tracking-wider">Total Users</span>
                    <a href="{{ route('admin.users.index') }}" class="text-decoration-none small">Manage &rarr;</a>
                </div>
                <h3 class="mb-1 fw-bold text-dark">{{ number_format($stats['total_users'] ?? 0) }}</h3>
                <div class="text-muted small">Registered user accounts</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-semibold tracking-wider">Total Verifications</span>
                    <a href="{{ route('admin.verifications.index') }}" class="text-decoration-none small">Records &rarr;</a>
                </div>
                <h3 class="mb-1 fw-bold text-dark">{{ number_format($stats['total_verifications'] ?? 0) }}</h3>
                <div class="text-muted small">All-time identity checks</div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 2: TODAY'S ACTIVITY --}}
<div class="row g-3 mb-4">
    <div class="col-lg-4 col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold mb-2">Today's Verifications</div>
                <div class="d-flex align-items-baseline gap-2">
                    <h3 class="mb-0 fw-bold">{{ number_format($stats['daily_verifications'] ?? 0) }}</h3>
                    <span class="text-muted small">requests</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold mb-2 text-success">Today's Success</div>
                <div class="d-flex align-items-baseline gap-2">
                    <h3 class="mb-0 fw-bold text-success">{{ number_format($stats['daily_success_verifications'] ?? 0) }}</h3>
                    <span class="text-muted small">completed</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold mb-2 text-danger">Today's Failed / Pending</div>
                <div class="d-flex align-items-baseline gap-2">
                    <h3 class="mb-0 fw-bold text-danger">{{ number_format($stats['daily_failed_verifications'] ?? 0) }}</h3>
                    <span class="text-muted small">unresolved</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 3: REVENUE & USER GROWTH CHARTS --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 px-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">Revenue by Service</h5>
                <span class="text-muted small">Debits & Products</span>
            </div>
            <div class="card-body px-3 pb-3">
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
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 px-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">User Growth (Last 30 Days)</h5>
                <span class="text-muted small">Daily Signups</span>
            </div>
            <div class="card-body px-3 pb-3">
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
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 px-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">Verification History (Last 30 Days)</h5>
                <span class="text-muted small">Success vs. Failed checks</span>
            </div>
            <div class="card-body px-3 pb-3">
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="verification-history-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 5: WEBSITE TRAFFIC & ORIGINATIONS --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 pt-3 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="card-title mb-0 fw-bold">Website Traffic & Traffic Originations</h5>
            <div class="text-muted small">Visitors, referral channels, and landing page discovery</div>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                Today: <strong>{{ number_format($trafficToday) }}</strong> views ({{ number_format($uniqueVisitorsToday) }} visitors)
            </span>
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                30 Days: <strong>{{ number_format($traffic30d) }}</strong> views ({{ number_format($uniqueVisitors30d) }} visitors)
            </span>
        </div>
    </div>
    <div class="card-body px-3 pb-3">
        <div class="row g-3 mb-4">
            {{-- Traffic Originations Doughnut --}}
            <div class="col-lg-5">
                <div class="border rounded-3 p-3 h-100 bg-light bg-opacity-25">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold small text-uppercase text-muted">Traffic Channels / Sources</span>
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
                <div class="border rounded-3 p-3 h-100 bg-light bg-opacity-25">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold small text-uppercase text-muted">Daily Page Views & Unique Visitors</span>
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
                <div class="border rounded-3 p-3 h-100">
                    <div class="fw-semibold small text-uppercase text-muted mb-2">Top Referrer Domains</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Referring Source</th>
                                    <th>Category</th>
                                    <th class="text-end">Visits</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topReferrers as $ref)
                                    <tr>
                                        <td>
                                            <span class="fw-medium text-dark">{{ $ref->referrer_host }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary">{{ $ref->origination_source }}</span>
                                        </td>
                                        <td class="text-end fw-semibold">{{ number_format($ref->total) }}</td>
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
                <div class="border rounded-3 p-3 h-100">
                    <div class="fw-semibold small text-uppercase text-muted mb-2">Top Visited Landing Pages</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Page Path</th>
                                    <th class="text-end">Views</th>
                                    <th class="text-end">Uniques</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topPages as $page)
                                    <tr>
                                        <td>
                                            <code class="text-primary">{{ $page->path }}</code>
                                        </td>
                                        <td class="text-end fw-semibold">{{ number_format($page->views) }}</td>
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
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 pt-3 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="card-title mb-0 fw-bold">Search Keywords & User Intent Analytics</h5>
            <div class="text-muted small">Search terms entered across Search Hub, portal search bars, and search engine referrals</div>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                Searches Today: <strong>{{ number_format($totalSearchesToday) }}</strong>
            </span>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                30-Day Searches: <strong>{{ number_format($totalSearches30d) }}</strong>
            </span>
        </div>
    </div>
    <div class="card-body px-3 pb-3">
        @if($searchKeywords->isEmpty())
            <div class="text-center py-4 text-muted">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="mb-2 opacity-50">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <div class="fw-semibold">No Search Queries Recorded Yet</div>
                <div class="small">Queries typed in the Search Hub or incoming search engine keywords are automatically logged here in real-time.</div>
            </div>
        @else
            {{-- Keyword cloud badges --}}
            <div class="mb-3 p-3 bg-light bg-opacity-25 rounded-3">
                <div class="small text-muted fw-semibold text-uppercase mb-2">Trending Search Terms</div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($searchKeywords as $kw)
                        <span class="badge bg-white text-dark border shadow-sm px-3 py-2 d-inline-flex align-items-center gap-2">
                            <span class="fw-bold">{{ $kw->term }}</span>
                            <span class="badge bg-primary rounded-pill">{{ $kw->total }}</span>
                        </span>
                    @endforeach
                </div>
            </div>

            {{-- Keyword detailed table --}}
            <div class="table-responsive border rounded-3">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted">
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
                                        <span class="fw-semibold text-dark">{{ $kw->term }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($kw->source === 'search_hub')
                                        <span class="badge bg-primary-subtle text-primary">Search Hub (Navigation)</span>
                                    @elseif($kw->source === 'search_engine_referer')
                                        <span class="badge bg-success-subtle text-success">Search Engine (Google/Bing)</span>
                                    @elseif($kw->source === 'utm_campaign')
                                        <span class="badge bg-warning-subtle text-warning">Ad Campaign Keyword</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $kw->source }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-dark">{{ number_format($kw->total) }}</span>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Universal color palettes
        var serviceColors = [
            '#4f46e5', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', 
            '#8b5cf6', '#ec4899', '#3b82f6', '#14b8a6', '#f97316', 
            '#6366f1', '#64748b'
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
                        borderColor: '#ffffff'
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
                                font: { size: 11 }
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
                            ticks: { font: { size: 10 }, maxTicksLimit: 10 }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0, 0, 0, 0.05)' },
                            ticks: { precision: 0, font: { size: 10 } }
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
                            labels: { boxWidth: 12, font: { size: 11 } }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            grid: { display: false },
                            ticks: { font: { size: 10 }, maxTicksLimit: 12 }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            grid: { color: 'rgba(0, 0, 0, 0.05)' },
                            ticks: { precision: 0, font: { size: 10 } }
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
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 10, padding: 8, font: { size: 10 } }
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
                            labels: { boxWidth: 12, font: { size: 11 } }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10 }, maxTicksLimit: 10 }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0, 0, 0, 0.05)' },
                            ticks: { precision: 0, font: { size: 10 } }
                        }
                    }
                }
            });
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
                    if (data.dataverify) {
                        const dvVal = document.getElementById('dataverify-balance-val');
                        if (dvVal) dvVal.innerText = data.dataverify.formatted || '—';
                        const badge = document.getElementById('dataverify-badge');
                        if (badge) {
                            badge.className = 'badge ' + (data.dataverify.badge_class || 'bg-secondary') + ' rounded-pill px-2 py-1 small';
                            badge.innerText = data.dataverify.status_label || 'Unknown';
                        }
                        const dvMsg = document.getElementById('dataverify-msg');
                        if (dvMsg) dvMsg.innerText = data.dataverify.message || '';
                    }

                    if (data.robosttech) {
                        const robVal = document.getElementById('robost-balance-val');
                        if (robVal) robVal.innerText = data.robosttech.formatted || '—';
                        const badge = document.getElementById('robost-badge');
                        if (badge) {
                            badge.className = 'badge ' + (data.robosttech.badge_class || 'bg-secondary') + ' rounded-pill px-2 py-1 small';
                            badge.innerText = data.robosttech.status_label || 'Unknown';
                        }
                        const robMsg = document.getElementById('robost-msg');
                        if (robMsg) robMsg.innerText = data.robosttech.message || '';
                    }

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
<style>
@keyframes spin-kf {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endpush
