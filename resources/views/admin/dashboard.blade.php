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
@endphp

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
    });
</script>
@endpush
