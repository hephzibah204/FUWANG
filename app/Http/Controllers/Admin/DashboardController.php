<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentLicenseTransaction;
use App\Models\AuctionLot;
use App\Models\SearchKeywordLog;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VerificationResult;
use App\Models\WebsiteTrafficLog;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('refresh') || $request->boolean('refresh')) {
            Cache::forget('admin_dashboard_bi_v1');
        }

        $dashboardData = Cache::remember('admin_dashboard_bi_v1', now()->addSeconds(60), function () {
            $successfulStatuses = ['success', 'successful', 'completed', 'approved', 'paid'];

            // Revenue calculation:
            // 1) Debits where user spent wallet funds: balance_before > balance_after
            // 2) Fallback for non-wallet service debits (order_type not funding/credit/refund)
            $serviceDebitExpr = "CASE 
                WHEN balance_before > balance_after THEN (balance_before - balance_after) 
                WHEN order_type NOT LIKE '%Funding%' AND order_type NOT LIKE '%Credit%' AND order_type NOT LIKE '%Refund%' 
                THEN ABS(COALESCE(balance_after, 0) - COALESCE(balance_before, 0)) 
                ELSE 0 
            END";

            $serviceRevenue = (float) Transaction::whereIn(DB::raw('LOWER(status)'), $successfulStatuses)
                ->where(function ($q) {
                    $q->where('balance_before', '>', DB::raw('balance_after'))
                        ->orWhere(function ($q2) {
                            $q2->where('order_type', 'not like', '%Funding%')
                                ->where('order_type', 'not like', '%Credit%')
                                ->where('order_type', 'not like', '%Refund%')
                                ->whereRaw('ABS(COALESCE(balance_after, 0) - COALESCE(balance_before, 0)) > 0');
                        });
                })
                ->selectRaw("SUM({$serviceDebitExpr}) as total")
                ->value('total');

            // Agent accreditation revenue if applicable
            $agentLicenseRevenue = 0.0;
            try {
                if (Schema::hasTable('agent_license_transactions')) {
                    $agentLicenseRevenue = (float) AgentLicenseTransaction::whereIn(DB::raw('LOWER(status)'), ['paid', 'approved', 'success', 'successful'])->sum('amount');
                }
                if ($agentLicenseRevenue == 0 && Schema::hasTable('enrollment_agents')) {
                    $agentLicenseRevenue = (float) DB::table('enrollment_agents')->where('license_status', 'paid')->sum('license_fee_paid');
                }
            } catch (\Throwable $e) {
                $agentLicenseRevenue = 0.0;
            }

            $totalRevenue = round($serviceRevenue + $agentLicenseRevenue, 2);

            $today = Carbon::today();

            $stats = [
                'total_revenue' => $totalRevenue,
                'total_transactions' => Transaction::count(),
                'total_users' => User::count(),
                'total_verifications' => VerificationResult::count(),
                'total_auctions' => AuctionLot::count(),
                'daily_verifications' => VerificationResult::where('created_at', '>=', $today)->count(),
                'daily_success_verifications' => VerificationResult::where('created_at', '>=', $today)
                    ->whereIn(DB::raw('LOWER(status)'), ['success', 'successful', 'completed', 'approved'])
                    ->count(),
                'daily_failed_verifications' => VerificationResult::where('created_at', '>=', $today)
                    ->whereIn(DB::raw('LOWER(status)'), ['failed', 'error', 'rejected'])
                    ->count(),
            ];

            // Revenue by service
            $revenueByService = Transaction::whereIn(DB::raw('LOWER(status)'), $successfulStatuses)
                ->where(function ($q) {
                    $q->where('balance_before', '>', DB::raw('balance_after'))
                        ->orWhere(function ($q2) {
                            $q2->where('order_type', 'not like', '%Funding%')
                                ->where('order_type', 'not like', '%Credit%')
                                ->where('order_type', 'not like', '%Refund%')
                                ->whereRaw('ABS(COALESCE(balance_after, 0) - COALESCE(balance_before, 0)) > 0');
                        });
                })
                ->selectRaw("order_type as service, SUM({$serviceDebitExpr}) as total")
                ->groupBy('order_type')
                ->havingRaw('total > 0')
                ->orderByDesc('total')
                ->get();

            if ($agentLicenseRevenue > 0) {
                $revenueByService->push((object)[
                    'service' => 'Agent Accreditation',
                    'total' => $agentLicenseRevenue,
                ]);
                $revenueByService = $revenueByService->sortByDesc('total')->values();
            }

            // Timeline period (Last 30 Days)
            $period = CarbonPeriod::create(Carbon::now()->subDays(29)->startOfDay(), Carbon::now()->startOfDay());

            // User growth (Last 30 Days)
            $userGrowthRaw = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as users'))
                ->where('created_at', '>=', Carbon::now()->subDays(29)->startOfDay())
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            $userGrowthMap = $userGrowthRaw
                ->pluck('users', 'date')
                ->map(fn ($v) => (int) $v)
                ->all();

            $userGrowthLabels = [];
            $userGrowthValues = [];
            foreach ($period as $day) {
                $key = $day->toDateString();
                $userGrowthLabels[] = $day->format('M d');
                $userGrowthValues[] = (int) ($userGrowthMap[$key] ?? 0);
            }

            // Verification History (Last 30 Days) with aligned daily values
            $historyRaw = VerificationResult::select(
                    DB::raw('DATE(created_at) as date'),
                    'status',
                    DB::raw('count(*) as count')
                )
                ->where('created_at', '>=', Carbon::now()->subDays(29)->startOfDay())
                ->groupBy('date', 'status')
                ->orderBy('date', 'asc')
                ->get();

            $successMap = [];
            $failedMap = [];

            foreach ($historyRaw as $row) {
                $dateKey = (string) $row->date;
                $st = strtolower((string) $row->status);
                if (in_array($st, ['success', 'successful', 'completed', 'approved'], true)) {
                    $successMap[$dateKey] = ($successMap[$dateKey] ?? 0) + (int) $row->count;
                } else {
                    $failedMap[$dateKey] = ($failedMap[$dateKey] ?? 0) + (int) $row->count;
                }
            }

            $verificationHistoryLabels = [];
            $verificationSuccessValues = [];
            $verificationFailedValues = [];

            foreach ($period as $day) {
                $dateKey = $day->toDateString();
                $verificationHistoryLabels[] = $day->format('M d');
                $verificationSuccessValues[] = (int) ($successMap[$dateKey] ?? 0);
                $verificationFailedValues[] = (int) ($failedMap[$dateKey] ?? 0);
            }

            // ── Website Traffic & Originations Analytics ──────────────────
            WebsiteTrafficLog::ensureSchemaIntegrity();
            SearchKeywordLog::ensureSchemaIntegrity();

            $trafficToday = WebsiteTrafficLog::where('created_at', '>=', $today)->count();
            $uniqueVisitorsToday = WebsiteTrafficLog::where('created_at', '>=', $today)->distinct('visitor_id')->count('visitor_id');
            $traffic30d = WebsiteTrafficLog::where('created_at', '>=', Carbon::now()->subDays(29)->startOfDay())->count();
            $uniqueVisitors30d = WebsiteTrafficLog::where('created_at', '>=', Carbon::now()->subDays(29)->startOfDay())->distinct('visitor_id')->count('visitor_id');

            // Fallback: If traffic table was just created, check if AbEvent has any logged views
            if ($traffic30d === 0 && Schema::hasTable('ab_events')) {
                try {
                    $abViews = DB::table('ab_events')->where('event_name', 'page_view')->count();
                    if ($abViews > 0) {
                        $traffic30d = $abViews;
                    }
                } catch (\Throwable $e) {}
            }

            // Traffic Originations Breakdown
            $originations = WebsiteTrafficLog::where('created_at', '>=', Carbon::now()->subDays(29)->startOfDay())
                ->select('origination_source', DB::raw('count(*) as total'))
                ->groupBy('origination_source')
                ->orderByDesc('total')
                ->get();

            $trafficOriginLabels = $originations->pluck('origination_source')->values()->all();
            $trafficOriginValues = $originations->pluck('total')->map(fn($v) => (int)$v)->values()->all();

            // Daily traffic trends (Last 30 Days)
            $dailyTrafficRaw = WebsiteTrafficLog::where('created_at', '>=', Carbon::now()->subDays(29)->startOfDay())
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as views'), DB::raw('count(distinct visitor_id) as uniques'))
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            $viewsMap = $dailyTrafficRaw->pluck('views', 'date')->all();
            $uniquesMap = $dailyTrafficRaw->pluck('uniques', 'date')->all();

            $trafficDaysLabels = [];
            $trafficViewsValues = [];
            $trafficUniquesValues = [];

            foreach ($period as $day) {
                $dKey = $day->toDateString();
                $trafficDaysLabels[] = $day->format('M d');
                $trafficViewsValues[] = (int) ($viewsMap[$dKey] ?? 0);
                $trafficUniquesValues[] = (int) ($uniquesMap[$dKey] ?? 0);
            }

            // Top Visited Pages
            $topPages = WebsiteTrafficLog::where('created_at', '>=', Carbon::now()->subDays(29)->startOfDay())
                ->select('path', DB::raw('count(*) as views'), DB::raw('count(distinct visitor_id) as uniques'))
                ->groupBy('path')
                ->orderByDesc('views')
                ->limit(6)
                ->get();

            // Top Referrers
            $topReferrers = WebsiteTrafficLog::where('created_at', '>=', Carbon::now()->subDays(29)->startOfDay())
                ->whereNotNull('referrer_host')
                ->where('referrer_host', '!=', '')
                ->select('referrer_host', 'origination_source', DB::raw('count(*) as total'))
                ->groupBy('referrer_host', 'origination_source')
                ->orderByDesc('total')
                ->limit(6)
                ->get();

            // ── Search Keywords Analytics ─────────────────────────────────
            $searchKeywords = SearchKeywordLog::where('created_at', '>=', Carbon::now()->subDays(29)->startOfDay())
                ->select(
                    DB::raw('LOWER(keyword) as term'),
                    DB::raw('count(*) as total'),
                    DB::raw('MAX(created_at) as last_searched'),
                    DB::raw('MAX(source) as source')
                )
                ->groupBy('term')
                ->orderByDesc('total')
                ->limit(12)
                ->get();

            $totalSearches30d = SearchKeywordLog::where('created_at', '>=', Carbon::now()->subDays(29)->startOfDay())->count();
            $totalSearchesToday = SearchKeywordLog::where('created_at', '>=', $today)->count();

            return [
                'stats' => $stats,
                'revenue_by_service' => $revenueByService,
                'revenueLabels' => $revenueByService->pluck('service')->values()->all(),
                'revenueValues' => $revenueByService->pluck('total')->map(fn($v) => round((float)$v, 2))->values()->all(),
                'userGrowthLabels' => $userGrowthLabels,
                'userGrowthValues' => $userGrowthValues,
                'verificationHistoryLabels' => $verificationHistoryLabels,
                'verificationSuccessValues' => $verificationSuccessValues,
                'verificationFailedValues' => $verificationFailedValues,

                // Traffic metrics
                'traffic_today' => $trafficToday,
                'unique_visitors_today' => $uniqueVisitorsToday,
                'traffic_30d' => $traffic30d,
                'unique_visitors_30d' => $uniqueVisitors30d,
                'trafficOriginLabels' => $trafficOriginLabels,
                'trafficOriginValues' => $trafficOriginValues,
                'trafficDaysLabels' => $trafficDaysLabels,
                'trafficViewsValues' => $trafficViewsValues,
                'trafficUniquesValues' => $trafficUniquesValues,
                'topPages' => $topPages,
                'topReferrers' => $topReferrers,

                // Search keywords
                'searchKeywords' => $searchKeywords,
                'totalSearches30d' => $totalSearches30d,
                'totalSearchesToday' => $totalSearchesToday,
            ];
        });

        return view('admin.dashboard', compact('dashboardData'));
    }
}
