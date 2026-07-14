<?php $__env->startSection('title', 'Admin Dashboard | ' . config('app.name')); ?>

<?php $__env->startSection('content'); ?>
<?php
    $stats = $dashboardData['stats'] ?? [];
    $revenue_by_service = $dashboardData['revenue_by_service'] ?? collect();
    $userGrowthLabels = $dashboardData['userGrowthLabels'] ?? [];
    $userGrowthValues = $dashboardData['userGrowthValues'] ?? [];
    $verification_history = $dashboardData['verification_history'] ?? collect();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Business Intelligence Dashboard</h2>
</div>

<div class="row">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Total Revenue</div>
                <h4 class="mb-0">NGN <?php echo e(number_format($stats['total_revenue'], 2)); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Total Transactions</div>
                <h4 class="mb-0"><?php echo e(number_format($stats['total_transactions'])); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Total Users</div>
                <h4 class="mb-0"><?php echo e(number_format($stats['total_users'])); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Total Auctions</div>
                <h4 class="mb-0"><?php echo e(number_format($stats['total_auctions'])); ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Today's Verifications</div>
                <h4 class="mb-0"><?php echo e(number_format($stats['daily_verifications'])); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Today's Success</div>
                <h4 class="mb-0"><?php echo e(number_format($stats['daily_success_verifications'])); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Today's Failed</div>
                <h4 class="mb-0"><?php echo e(number_format($stats['daily_failed_verifications'])); ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">Revenue by Service</h3>
            </div>
            <div class="card-body">
                <canvas id="revenue-by-service-chart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card h-100">
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
    <div class="col-12 mb-3">
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(function () {
        // Revenue by Service Chart
        var revenueByServiceCtx = document.getElementById('revenue-by-service-chart').getContext('2d');
        var revenueByServiceChart = new Chart(revenueByServiceCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($revenue_by_service->pluck('service')); ?>,
                datasets: [{
                    data: <?php echo json_encode($revenue_by_service->pluck('total')); ?>,
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
                labels: <?php echo json_encode($userGrowthLabels ?? []); ?>,
                datasets: [{
                    label: 'New Users',
                    data: <?php echo json_encode($userGrowthValues ?? []); ?>,
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
                labels: <?php echo json_encode($verification_history->pluck('date')->unique()); ?>,
                datasets: [
                    {
                        label: 'Success',
                        data: <?php echo json_encode($verification_history->where('status', 'success')->pluck('count')); ?>,
                        backgroundColor: '#00a65a',
                    },
                    {
                        label: 'Failed',
                        data: <?php echo json_encode($verification_history->where('status', 'failed')->pluck('count')); ?>,
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.nexus', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/fuwa.ng/html/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>