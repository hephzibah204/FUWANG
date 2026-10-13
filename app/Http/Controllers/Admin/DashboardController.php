<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentLicenseTransaction;
use App\Models\ApiCenter;
use App\Models\AuctionLot;
use App\Models\CustomApi;
use App\Models\SearchKeywordLog;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VerificationResult;
use App\Models\WebsiteTrafficLog;
use App\Services\DataVerify\DataVerifyClient;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('refresh') || $request->boolean('refresh')) {
            Cache::forget('admin_dashboard_bi_v1');
            Cache::forget('admin_provider_balances_v1');
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

            // Invested vs. Made (Profitability & ROI)
            $manualCapital = (float) SystemSetting::get('total_capital_invested', 0);
            $autoFulfillmentCost = 0.0;
            try {
                if (Schema::hasTable('verification_results')) {
                    $costSetting = (float) SystemSetting::get('cost_per_verification', 0);
                    if ($costSetting > 0) {
                        $successVerifications = (int) VerificationResult::whereIn(DB::raw('LOWER(status)'), ['success', 'successful', 'completed', 'approved'])->count();
                        $autoFulfillmentCost = round($successVerifications * $costSetting, 2);
                    }
                }
            } catch (\Throwable $e) {
                $autoFulfillmentCost = 0.0;
            }

            $totalInvested = $manualCapital > 0 ? $manualCapital : $autoFulfillmentCost;
            $hasCost = $totalInvested > 0;
            $netProfit = $hasCost ? round($totalRevenue - $totalInvested, 2) : 0.0;
            $roi = ($totalInvested > 0) ? round((($totalRevenue - $totalInvested) / $totalInvested) * 100, 1) : 0.0;
            $profitMargin = ($totalRevenue > 0 && $totalInvested > 0) ? round(($netProfit / $totalRevenue) * 100, 1) : 0.0;

            $investmentStats = [
                'made' => $totalRevenue,
                'invested' => $totalInvested,
                'manual_capital' => $manualCapital,
                'auto_fulfillment_cost' => $autoFulfillmentCost,
                'has_capital' => $hasCost,
                'net_profit' => $netProfit,
                'roi' => $roi,
                'profit_margin' => $profitMargin,
                'is_profitable' => $hasCost ? ($netProfit >= 0) : null,
            ];

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

                // API Provider Balances
                'providerBalances' => Cache::remember('admin_provider_balances_v1', now()->addMinutes(3), function () {
                    return $this->fetchProviderBalances();
                }),

                // Investment vs. Made Stats
                'investmentStats' => $investmentStats,

                // Current API center settings
                'apiCenter' => ApiCenter::first(),
            ];
        });

        return view('admin.dashboard', compact('dashboardData'));
    }

    public function updateApiCredentials(Request $request)
    {
        $validated = $request->validate([
            'dataverify_api_key' => ['nullable', 'string', 'max:500'],
            'robosttech_api_key' => ['nullable', 'string', 'max:500'],
            'robosttech_endpoint_nin' => ['nullable', 'string', 'max:500'],
            'robosttech_endpoint_validation' => ['nullable', 'string', 'max:500'],
            'robosttech_endpoint_clearance' => ['nullable', 'string', 'max:500'],
            'vuvaa_username' => ['nullable', 'string', 'max:500'],
            'vuvaa_password' => ['nullable', 'string', 'max:500'],
            'vuvaa_endpoint' => ['nullable', 'string', 'max:500'],
        ]);

        $apiCenterFields = [
            'dataverify_api_key',
            'robosttech_api_key',
            'robosttech_endpoint_nin',
            'robosttech_endpoint_validation',
            'robosttech_endpoint_clearance',
        ];
        $apiCenterData = [];
        foreach ($apiCenterFields as $field) {
            if (isset($validated[$field]) && $validated[$field] !== null && trim((string) $validated[$field]) !== '') {
                $apiCenterData[$field] = $validated[$field];
            }
        }

        if (!empty($apiCenterData)) {
            DB::table('api_centers')->updateOrInsert(
                ['id' => DB::table('api_centers')->min('id') ?? 1],
                $apiCenterData
            );
        }

        foreach (['vuvaa_username', 'vuvaa_password', 'vuvaa_endpoint'] as $vuvaaField) {
            if ($request->filled($vuvaaField)) {
                SystemSetting::set($vuvaaField, $request->input($vuvaaField), 'integrations');
            }
        }

        Cache::forget('admin_dashboard_bi_v1');
        Cache::forget('admin_provider_balances_v1');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'API credentials updated successfully.',
            ]);
        }

        return back()->with('success', 'API credentials updated successfully. Checking balances...');
    }

    public function updateCapitalInvestment(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        SystemSetting::set('total_capital_invested', $validated['amount'], 'financial', 'number', 'Total Capital Invested');
        Cache::forget('admin_dashboard_bi_v1');

        return back()->with('success', 'Capital investment updated to NGN ' . number_format((float) $validated['amount'], 2) . '.');
    }

    public function providerBalances(Request $request)
    {
        $force = $request->boolean('refresh');
        if ($force) {
            Cache::forget('admin_provider_balances_v1');
        }

        $balances = Cache::remember('admin_provider_balances_v1', now()->addMinutes(3), function () {
            return $this->fetchProviderBalances();
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($balances);
        }

        return back()->with('success', 'Provider balances refreshed.');
    }

    public function fetchProviderBalances(): array
    {
        // 1. DataVerify
        $dataverify = [
            'name' => 'DataVerify',
            'configured' => false,
            'balance' => null,
            'formatted' => 'Not Configured',
            'status' => 'not_configured',
            'status_label' => 'Not Configured',
            'badge_class' => 'bg-secondary',
            'message' => 'API Key not configured',
        ];

        try {
            $apiCenter = ApiCenter::first();
            $customDv = CustomApi::where(function ($q) {
                $q->where('provider_identifier', 'dataverify')
                  ->orWhere('name', 'like', '%dataverify%');
            })->first();

            $dvKey = trim((string) (
                $apiCenter?->dataverify_api_key
                ?: $customDv?->api_key
                ?: env('DATAVERIFY_API_KEY')
                ?: SystemSetting::get('dataverify_api_key', '')
            ));

            if (!empty($dvKey)) {
                $dataverify['configured'] = true;
                $client = new DataVerifyClient($customDv);
                $res = $client->checkBalance();

                if ($res['ok'] && $res['balance'] !== null) {
                    $bal = (float) $res['balance'];
                    $dataverify['balance'] = $bal;
                    $dataverify['formatted'] = 'NGN ' . number_format($bal, 2);
                    if ($bal < 5000) {
                        $dataverify['status'] = 'low';
                        $dataverify['status_label'] = 'Low Balance (< ₦5,000)';
                        $dataverify['badge_class'] = 'bg-warning text-dark';
                    } else {
                        $dataverify['status'] = 'ok';
                        $dataverify['status_label'] = 'Operational';
                        $dataverify['badge_class'] = 'bg-success';
                    }
                    $dataverify['message'] = 'Operational & Connected';
                } else {
                    $dataverify['status'] = 'error';
                    $dataverify['status_label'] = 'Check Failed';
                    $dataverify['badge_class'] = 'bg-danger';
                    $dataverify['formatted'] = 'Unavailable';
                    $dataverify['message'] = $res['message'] ?? 'Unable to retrieve balance';
                }
            }
        } catch (\Throwable $e) {
            $dataverify['status'] = 'error';
            $dataverify['status_label'] = 'Error';
            $dataverify['badge_class'] = 'bg-danger';
            $dataverify['formatted'] = 'Error';
            $dataverify['message'] = $e->getMessage();
        }

        // 2. RobostTech
        $robost = [
            'name' => 'RobostTech',
            'configured' => false,
            'balance' => null,
            'formatted' => 'Not Configured',
            'status' => 'not_configured',
            'status_label' => 'Not Configured',
            'badge_class' => 'bg-secondary',
            'message' => 'API Key not configured',
        ];

        try {
            $apiCenter = $apiCenter ?? ApiCenter::first();
            $customRobost = CustomApi::where(function ($q) {
                $q->where('provider_identifier', 'robosttech')
                  ->orWhere('name', 'like', '%robost%')
                  ->orWhere('endpoint', 'like', '%robosttech%');
            })->first();

            $robostKey = trim((string) (
                $apiCenter?->robosttech_api_key
                ?: $customRobost?->api_key
                ?: config('services.robosttech.api_key')
                ?: env('ROBOSTTECH_API_KEY')
                ?: SystemSetting::get('robosttech_api_key', '')
                ?: SystemSetting::get('robost_api_key', '')
            ));

            if (!empty($robostKey)) {
                $robost['configured'] = true;

                // Determine clean base URL
                $rawEndpoint = (string) (
                    $customRobost?->endpoint
                    ?: $apiCenter?->robosttech_endpoint_nin
                    ?: $apiCenter?->robosttech_endpoint_validation
                    ?: $apiCenter?->robosttech_endpoint_clearance
                    ?: config('services.robosttech.base_url')
                    ?: 'https://robosttech.com/api'
                );

                $endpoint = rtrim(trim($rawEndpoint), '/');
                foreach (['/nin_verify', '/nin', '/clearance_status', '/clearance', '/validation', '/personalization', '/balance', '/wallet'] as $suffix) {
                    if (str_ends_with(strtolower($endpoint), $suffix)) {
                        $endpoint = substr($endpoint, 0, -strlen($suffix));
                    }
                }

                $headers = [
                    'api-key' => $robostKey,
                    'x-api-key' => $robostKey,
                    'Authorization' => 'Bearer ' . $robostKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ];

                $urlsToTry = [
                    $endpoint . '/balance',
                    $endpoint . '/wallet',
                    $endpoint . '/wallet_balance',
                    $endpoint . '/user/balance',
                    $endpoint . '/check_balance',
                ];

                $res = null;
                foreach ($urlsToTry as $bUrl) {
                    try {
                        $attempt = Http::timeout(6)->withHeaders($headers)->get($bUrl);
                        if ($attempt->successful()) {
                            $res = $attempt;
                            break;
                        }
                        if ($attempt->status() === 405 || $attempt->status() === 400) {
                            $attemptPost = Http::timeout(6)->withHeaders($headers)->asJson()->post($bUrl, [
                                'api-key' => $robostKey,
                                'api_key' => $robostKey,
                            ]);
                            if ($attemptPost->successful()) {
                                $res = $attemptPost;
                                break;
                            }
                        }
                    } catch (\Throwable $te) {}
                }

                if (!$res) {
                    // Try direct POST to /balance and /wallet if GETs weren't successful
                    try {
                        $attemptPost = Http::timeout(6)->withHeaders($headers)->asJson()->post($endpoint . '/balance', [
                            'api-key' => $robostKey,
                            'api_key' => $robostKey,
                        ]);
                        if ($attemptPost->successful()) {
                            $res = $attemptPost;
                        }
                    } catch (\Throwable $te) {}
                }

                if (!$res || !$res->successful()) {
                    // RobostTech does not expose a standalone /balance endpoint on some deployments.
                    // Authenticate the API key by pinging the clearance_status endpoint.
                    try {
                        $authPing = Http::timeout(6)->withHeaders($headers)->asJson()->post($endpoint . '/clearance_status', [
                            'tracking_id' => 'PING_CHECK',
                        ]);
                        // If it returns HTTP 200 or anything other than 'Unauthorised' / 'The api-key field is required.', the key is active.
                        if ($authPing->status() === 200) {
                            $res = $authPing;
                        }
                    } catch (\Throwable $te) {}
                }

                if ($res && $res->successful()) {
                    $json = $res->json();
                    if (is_array($json)) {
                        $rawBal = $json['balance']
                            ?? $json['data']['balance']
                            ?? $json['wallet_balance']
                            ?? $json['data']['wallet_balance']
                            ?? $json['wallet']['balance']
                            ?? $json['data']['wallet']['balance']
                            ?? $json['user']['balance']
                            ?? $json['data']['user']['balance']
                            ?? $json['user']['wallet_balance']
                            ?? $json['data']['user']['wallet_balance']
                            ?? $json['wallet']
                            ?? $json['data']['wallet']
                            ?? null;

                        $balFloat = null;
                        if ($rawBal !== null) {
                            if (is_numeric($rawBal)) {
                                $balFloat = (float) $rawBal;
                            } elseif (is_string($rawBal)) {
                                $cleaned = preg_replace('/[^0-9.]/', '', str_replace(',', '', $rawBal));
                                if (is_numeric($cleaned)) {
                                    $balFloat = (float) $cleaned;
                                }
                            }
                        }

                        if ($balFloat !== null) {
                            $robost['balance'] = $balFloat;
                            $robost['formatted'] = 'NGN ' . number_format($balFloat, 2);
                            if ($balFloat < 5000) {
                                $robost['status'] = 'low';
                                $robost['status_label'] = 'Low Balance (< ₦5,000)';
                                $robost['badge_class'] = 'bg-warning text-dark';
                            } else {
                                $robost['status'] = 'ok';
                                $robost['status_label'] = 'Operational';
                                $robost['badge_class'] = 'bg-success';
                            }
                            $robost['message'] = 'Operational & Connected';
                        } else {
                            $robost['status'] = 'ok';
                            $robost['status_label'] = 'Connected';
                            $robost['badge_class'] = 'bg-success';
                            $robost['formatted'] = is_string($rawBal) ? $rawBal : 'Connected / Active';
                            $robost['message'] = 'API Key Connected & Active';
                        }
                    } else {
                        $robost['status'] = 'error';
                        $robost['status_label'] = 'Check Failed';
                        $robost['badge_class'] = 'bg-danger';
                        $robost['formatted'] = 'Unavailable';
                        $robost['message'] = 'Invalid response format';
                    }
                } else {
                    $json = $res ? $res->json() : null;
                    $errMsg = is_array($json) ? ($json['message'] ?? $json['detail'] ?? null) : null;
                    if (is_array($errMsg)) {
                        $errMsg = reset($errMsg);
                        if (is_array($errMsg)) $errMsg = reset($errMsg);
                    }
                    $robost['status'] = 'error';
                    $robost['status_label'] = 'Check Failed';
                    $robost['badge_class'] = 'bg-danger';
                    $robost['formatted'] = 'Unavailable';
                    $robost['message'] = (string) ($errMsg ?: ($res ? ('HTTP ' . $res->status()) : 'Could not reach RobostTech server'));
                }
            }
        } catch (\Throwable $e) {
            $robost['status'] = 'error';
            $robost['status_label'] = 'Error';
            $robost['badge_class'] = 'bg-danger';
            $robost['formatted'] = 'Error';
            $robost['message'] = $e->getMessage();
        }

        // 3. Vuvaa (Demo / Live)
        $vuvaa = [
            'name' => 'VUVAA (Demo / Live)',
            'configured' => false,
            'balance' => null,
            'formatted' => 'Not Configured',
            'status' => 'not_configured',
            'status_label' => 'Not Configured',
            'badge_class' => 'bg-secondary',
            'message' => 'Credentials not configured',
        ];

        try {
            $customVuvaa = CustomApi::where(function ($q) {
                $q->where('provider_identifier', 'vuvaa')
                  ->orWhere('name', 'like', '%vuvaa%');
            })->first();

            $vuvaaUser = trim((string) (
                SystemSetting::get('vuvaa_username', '')
                ?: env('VUVAA_USERNAME', '')
                ?: ($customVuvaa?->config['username'] ?? '')
                ?: 'fuwa_demo_0417190741'
            ));

            if (!empty($vuvaaUser)) {
                $vuvaa['configured'] = true;
                $provider = $customVuvaa ?? new CustomApi([
                    'name' => 'VUVAA',
                    'provider_identifier' => 'vuvaa',
                    'endpoint' => SystemSetting::get('vuvaa_endpoint') ?: env('VUVAA_LIVE_URL') ?: 'https://premiere.vuvaa.com/demo/NIN_Validation_LIVE',
                    'config' => [
                        'username' => $vuvaaUser,
                        'password' => SystemSetting::get('vuvaa_password') ?: env('VUVAA_PASSWORD') ?: 'Password',
                        'encryption_key' => SystemSetting::get('vuvaa_encryption_key') ?: env('VUVAA_ENCRYPTION_KEY') ?: 'FD!-F=15B46BAD21',
                        'encryption_iv' => SystemSetting::get('vuvaa_encryption_iv') ?: env('VUVAA_ENCRYPTION_IV') ?: '0123456789012345',
                    ],
                ]);

                $vuvaaClient = new \App\Services\Vuvaa\VuvaaClient($provider);
                $res = $vuvaaClient->getWalletDetails();

                if ($res['ok']) {
                    $units = $res['data']['wallet_units'] 
                        ?? $res['data']['data'][0]['validation_units'] 
                        ?? $res['data']['data']['validation_units'] 
                        ?? $res['data']['validation_units'] 
                        ?? null;

                    if ($units !== null && is_numeric($units)) {
                        $unitsVal = (float) $units;
                        $vuvaa['balance'] = $unitsVal;
                        $vuvaa['formatted'] = number_format($unitsVal) . ' Units';
                        if ($unitsVal < 10) {
                            $vuvaa['status'] = 'low';
                            $vuvaa['status_label'] = 'Low Units (< 10)';
                            $vuvaa['badge_class'] = 'bg-warning text-dark';
                        } else {
                            $vuvaa['status'] = 'ok';
                            $vuvaa['status_label'] = 'Operational';
                            $vuvaa['badge_class'] = 'bg-success';
                        }
                        $isDemo = str_contains($vuvaaUser, 'demo');
                        $vuvaa['message'] = ($isDemo ? 'Demo: ' : 'Live: ') . $vuvaaUser;
                    } else {
                        $vuvaa['status'] = 'ok';
                        $vuvaa['status_label'] = 'Connected';
                        $vuvaa['badge_class'] = 'bg-success';
                        $vuvaa['formatted'] = 'Connected';
                        $vuvaa['message'] = $res['message'] ?? 'Connected successfully';
                    }
                } else {
                    $vuvaa['status'] = 'error';
                    $vuvaa['status_label'] = 'Check Failed';
                    $vuvaa['badge_class'] = 'bg-danger';
                    $vuvaa['formatted'] = 'Unavailable';
                    $vuvaa['message'] = (string) ($res['message'] ?? 'Could not retrieve Vuvaa wallet balance');
                }
            }
        } catch (\Throwable $e) {
            $vuvaa['status'] = 'error';
            $vuvaa['status_label'] = 'Error';
            $vuvaa['badge_class'] = 'bg-danger';
            $vuvaa['formatted'] = 'Error';
            $vuvaa['message'] = $e->getMessage();
        }

        return [
            'dataverify' => $dataverify,
            'robosttech' => $robost,
            'vuvaa' => $vuvaa,
            'checked_at' => now()->format('h:i A'),
        ];
    }
}
