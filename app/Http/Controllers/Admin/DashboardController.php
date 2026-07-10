<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\AuctionLot;
use App\Models\User;
use App\Models\VerificationResult;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $dashboardData = Cache::remember('admin_dashboard_bi_v1', now()->addSeconds(60), function () {
            $amountExpr = 'ABS(COALESCE(balance_after, 0) - COALESCE(balance_before, 0))';

            $stats = [
                'total_revenue' => (float) Transaction::where('status', 'completed')
                    ->selectRaw("SUM({$amountExpr}) as total")
                    ->value('total'),
                'total_transactions' => Transaction::count(),
                'total_users' => User::count(),
                'total_auctions' => AuctionLot::count(),
                'daily_verifications' => VerificationResult::where('created_at', '>=', Carbon::today())->count(),
                'daily_success_verifications' => VerificationResult::where('created_at', '>=', Carbon::today())->where('status', 'success')->count(),
                'daily_failed_verifications' => VerificationResult::where('created_at', '>=', Carbon::today())->where('status', 'failed')->count(),
            ];

            $revenueByService = Transaction::where('status', 'completed')
                ->selectRaw('order_type as service, SUM(' . $amountExpr . ') as total')
                ->groupBy('order_type')
                ->orderBy('total', 'desc')
                ->get();

            $userGrowthRaw = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as users'))
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();
            $userGrowthMap = $userGrowthRaw
                ->pluck('users', 'date')
                ->map(fn ($v) => (int) $v)
                ->all();
            $period = CarbonPeriod::create(Carbon::now()->subDays(29)->startOfDay(), Carbon::now()->startOfDay());
            $userGrowthLabels = [];
            $userGrowthValues = [];
            foreach ($period as $day) {
                $key = $day->toDateString();
                $userGrowthLabels[] = $day->format('M d');
                $userGrowthValues[] = (int) ($userGrowthMap[$key] ?? 0);
            }

            $verificationHistory = VerificationResult::select(DB::raw('DATE(created_at) as date'), 'status', DB::raw('count(*) as count'))
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->groupBy('date', 'status')
                ->orderBy('date', 'asc')
                ->get();

            return [
                'stats' => $stats,
                'revenue_by_service' => $revenueByService,
                'userGrowthLabels' => $userGrowthLabels,
                'userGrowthValues' => $userGrowthValues,
                'verification_history' => $verificationHistory,
            ];
        });

        return view('admin.dashboard', compact(
            'dashboardData'
        ));
    }
}
