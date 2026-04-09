@extends('layouts.admin')

@section('title', 'BI Dashboard')

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($stats['total_revenue']) }}</h3>
                <p>Total Revenue</p>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($stats['total_transactions']) }}</h3>
                <p>Total Transactions</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($stats['total_users']) }}</h3>
                <p>Total Users</p>
            </div>
            <div class="icon">
                <i class="ion ion-person-add"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ number_format($stats['total_auctions']) }}</h3>
                <p>Total Auctions</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 col-md-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ number_format($stats['daily_verifications']) }}</h3>
                <p>Today's Verifications</p>
            </div>
            <div class="icon">
                <i class="ion ion-clipboard"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($stats['daily_success_verifications']) }}</h3>
                <p>Today's Success Rate</p>
            </div>
            <div class="icon">
                <i class="ion ion-checkmark"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ number_format($stats['daily_failed_verifications']) }}</h3>
                <p>Today's Failures</p>
            </div>
            <div class="icon">
                <i class="ion ion-close"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Revenue by Service</h3>
            </div>
            <div class="card-body">
                <canvas id="revenue-by-service-chart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">User Growth (Last 30 Days)</h3>
            </div>
            <div class="card-body">
                <canvas id="user-growth-chart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Verification History (Last 30 Days)</h3>
            </div>
            <div class="card-body">
                <canvas id="verification-history-chart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(function () {
        // Revenue by Service Chart
        var revenueByServiceCtx = document.getElementById('revenue-by-service-chart').getContext('2d');
        var revenueByServiceChart = new Chart(revenueByServiceCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($revenue_by_service->pluck('service')) !!},
                datasets: [{
                    data: {!! json_encode($revenue_by_service->pluck('total')) !!},
                    backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'],
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
            }
        });

        // User Growth Chart
        var userGrowthCtx = document.getElementById('user-growth-chart').getContext('2d');
        var userGrowthChart = new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($user_growth->pluck('date')) !!},
                datasets: [{
                    label: 'New Users',
                    data: {!! json_encode($user_growth->pluck('users')) !!},
                    backgroundColor: 'rgba(60,141,188,0.2)',
                    borderColor: 'rgba(60,141,188,1)',
                    pointRadius: false,
                    pointColor: '#3b8bba',
                    pointStrokeColor: 'rgba(60,141,188,1)',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(60,141,188,1)',
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    x: {
                        grid: {
                            display: false,
                        }
                    },
                    y: {
                        grid: {
                            display: true,
                        }
                    }
                }
            }
        });

        // Verification History Chart
        var verificationHistoryCtx = document.getElementById('verification-history-chart').getContext('2d');
        var verificationHistoryChart = new Chart(verificationHistoryCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($verification_history->pluck('date')->unique()) !!},
                datasets: [
                    {
                        label: 'Success',
                        data: {!! json_encode($verification_history->where('status', 'success')->pluck('count')) !!},
                        backgroundColor: '#00a65a',
                    },
                    {
                        label: 'Failed',
                        data: {!! json_encode($verification_history->where('status', 'failed')->pluck('count')) !!},
                        backgroundColor: '#f56954',
                    },
                ]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true
                    }
                }
            }
        });
    });
</script>
@endpush
