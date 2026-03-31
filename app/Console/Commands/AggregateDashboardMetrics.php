<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Ticket;
use App\Models\FeatureToggle;
use App\Models\CustomApi;
use App\Models\NotaryRequest;
use App\Models\LogisticsRequest;
use App\Models\ServiceInvoice;
use App\Models\Transaction;
use App\Models\VerificationResult;
use App\Models\SystemMetric;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AggregateDashboardMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:aggregate-metrics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate heavy dashboard metrics to reduce database load on admin page';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting metric aggregation...');

        $todayStart = now()->startOfDay();
        
        $metrics = [
            'totalUsers' => User::count(),
            'onlineUsers' => User::where('online_status', 'online')->count(),
            'newUsersToday' => User::where('created_at', '>=', $todayStart)->count(),
            
            'totalRevenue' => (float) Transaction::query()
                ->where('status', 'success')
                ->selectRaw('COALESCE(SUM(CASE WHEN balance_before > balance_after THEN (balance_before - balance_after) ELSE 0 END), 0) as total')
                ->value('total'),
                
            'revenueToday' => (float) Transaction::query()
                ->where('status', 'success')
                ->where('created_at', '>=', $todayStart)
                ->selectRaw('COALESCE(SUM(CASE WHEN balance_before > balance_after THEN (balance_before - balance_after) ELSE 0 END), 0) as total')
                ->value('total'),
                
            'tx24h' => Transaction::where('created_at', '>=', now()->subDay())->count(),
            'failedTx24h' => Transaction::where('created_at', '>=', now()->subDay())->where('status', 'failed')->count(),
            
            'verificationsTotal' => VerificationResult::count(),
            'verifications24h' => VerificationResult::where('created_at', '>=', now()->subDay())->count(),
            'pendingVerifications' => VerificationResult::where('status', 'pending')->count(),
            
            'openTickets' => Ticket::where('status', 'open')->count(),
            'answeredTickets' => Ticket::where('status', 'answered')->count(),
            
            'providersTotal' => CustomApi::count(),
            'providersDown' => CustomApi::where('status', false)->count(),
            
            'disabledFeatures' => FeatureToggle::where('is_active', false)->count(),
            
            'pendingNotary' => NotaryRequest::whereNotIn('status', ['completed'])->count(),
            'pendingShipments' => LogisticsRequest::whereNotIn('status', ['delivered', 'completed'])->count(),
            'openInvoices' => ServiceInvoice::whereIn('status', ['sent', 'overdue'])->count(),
        ];

        SystemMetric::updateOrCreate(
            ['metric_key' => 'dashboard_aggregates'],
            ['metric_value' => $metrics]
        );

        $this->info('Metrics aggregated successfully!');
    }
}
