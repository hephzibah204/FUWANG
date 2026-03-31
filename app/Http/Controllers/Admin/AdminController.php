<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ticket;
use App\Models\FeatureToggle;
use App\Models\CustomApi;
use App\Models\NotaryRequest;
use App\Models\LogisticsRequest;
use App\Models\ServiceInvoice;
use App\Models\Transaction;
use App\Models\VerificationResult;
use App\Models\ApiCenter;
use App\Models\AdminAuditLog;
use App\Models\SystemMetric;
use App\Models\SystemSetting;
use App\Models\Funding;
use App\Models\AbEvent;
use App\Services\WalletService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Services\Referrals\ReferralService;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        $metrics = Cache::remember('admin:dashboard_metrics', now()->addSeconds(60), function () {
            $startDate = Carbon::now()->subDays(6)->startOfDay();
            
            // Get pre-aggregated fast metrics from scheduled command if available
            $aggregates = [];
            if (Schema::hasTable('system_metrics')) {
                $systemMetrics = SystemMetric::where('metric_key', 'dashboard_aggregates')->first();
                $aggregates = $systemMetrics ? $systemMetrics->metric_value : [];
            }

            // Dynamic queries that shouldn't be pre-calculated
            $recentUsers = User::select(['id', 'fullname', 'email', 'created_at'])->latest()->take(5)->get();

            $topServices = VerificationResult::query()
                ->select('service_type', DB::raw('COUNT(*) as total'))
                ->groupBy('service_type')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            $dailyVerificationsRaw = VerificationResult::query()
                ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
                ->where('created_at', '>=', $startDate)
                ->groupBy('day')
                ->orderBy('day')
                ->get();

            $dailySignupsRaw = User::query()
                ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
                ->where('created_at', '>=', $startDate)
                ->groupBy('day')
                ->orderBy('day')
                ->get();

            $dailyRevenueRaw = Transaction::query()
                ->selectRaw('DATE(created_at) as day, COALESCE(SUM(CASE WHEN balance_before > balance_after THEN (balance_before - balance_after) ELSE 0 END), 0) as total')
                ->where('status', 'success')
                ->where('created_at', '>=', $startDate)
                ->groupBy('day')
                ->orderBy('day')
                ->get();

            $recentTransactions = Transaction::query()
                ->select(['id', 'transaction_id', 'user_email', 'order_type', 'balance_before', 'balance_after', 'status', 'created_at'])
                ->latest()
                ->take(8)
                ->get();

            $recentVerifications = VerificationResult::query()
                ->select(['id', 'user_id', 'service_type', 'identifier', 'provider_name', 'status', 'reference_id', 'created_at'])
                ->with(['user:id,fullname,email'])
                ->latest()
                ->take(8)
                ->get();

            $recentTickets = Ticket::query()
                ->with(['user'])
                ->orderBy('updated_at', 'desc')
                ->take(8)
                ->get();

            $disabledFeatureList = FeatureToggle::query()
                ->select(['id', 'feature_name', 'offline_message'])
                ->where('is_active', false)
                ->orderBy('feature_name')
                ->take(6)
                ->get();

            // Fallbacks if scheduled task hasn't run yet
            return [
                'totalUsers' => $aggregates['totalUsers'] ?? User::count(),
                'onlineUsers' => $aggregates['onlineUsers'] ?? User::where('online_status', 'online')->count(),
                'recentUsers' => $recentUsers,
                'newUsersToday' => $aggregates['newUsersToday'] ?? User::where('created_at', '>=', now()->startOfDay())->count(),
                
                'totalRevenue' => $aggregates['totalRevenue'] ?? 0,
                'revenueToday' => $aggregates['revenueToday'] ?? 0,
                
                'tx24h' => $aggregates['tx24h'] ?? Transaction::where('created_at', '>=', now()->subDay())->count(),
                'failedTx24h' => $aggregates['failedTx24h'] ?? Transaction::where('created_at', '>=', now()->subDay())->where('status', 'failed')->count(),
                
                'verificationsTotal' => $aggregates['verificationsTotal'] ?? VerificationResult::count(),
                'verifications24h' => $aggregates['verifications24h'] ?? VerificationResult::where('created_at', '>=', now()->subDay())->count(),
                'pendingVerifications' => $aggregates['pendingVerifications'] ?? VerificationResult::where('status', 'pending')->count(),
                
                'topServices' => $topServices,
                'startDate' => $startDate,
                'dailyVerificationsRaw' => $dailyVerificationsRaw,
                'dailySignupsRaw' => $dailySignupsRaw,
                'dailyRevenueRaw' => $dailyRevenueRaw,
                
                'openTickets' => $aggregates['openTickets'] ?? Ticket::where('status', 'open')->count(),
                'answeredTickets' => $aggregates['answeredTickets'] ?? Ticket::where('status', 'answered')->count(),
                
                'providersTotal' => $aggregates['providersTotal'] ?? CustomApi::count(),
                'providersDown' => $aggregates['providersDown'] ?? CustomApi::where('status', false)->count(),
                
                'disabledFeatures' => $aggregates['disabledFeatures'] ?? FeatureToggle::where('is_active', false)->count(),
                
                'pendingNotary' => $aggregates['pendingNotary'] ?? NotaryRequest::whereNotIn('status', ['completed'])->count(),
                'pendingShipments' => $aggregates['pendingShipments'] ?? LogisticsRequest::whereNotIn('status', ['delivered', 'completed'])->count(),
                'openInvoices' => $aggregates['openInvoices'] ?? ServiceInvoice::whereIn('status', ['sent', 'overdue'])->count(),
                
                'recentTransactions' => $recentTransactions,
                'recentVerifications' => $recentVerifications,
                'recentTickets' => $recentTickets,
                'disabledFeatureList' => $disabledFeatureList
            ];
        });

        $totalUsers = $metrics['totalUsers'];
        $onlineUsers = $metrics['onlineUsers'];
        $recentUsers = $metrics['recentUsers'];
        $newUsersToday = $metrics['newUsersToday'];
        $totalRevenue = $metrics['totalRevenue'];
        $revenueToday = $metrics['revenueToday'];
        $tx24h = $metrics['tx24h'];
        $failedTx24h = $metrics['failedTx24h'];
        $verificationsTotal = $metrics['verificationsTotal'];
        $verifications24h = $metrics['verifications24h'];
        $pendingVerifications = $metrics['pendingVerifications'];
        $topServices = $metrics['topServices'];

        $startDate = $metrics['startDate'];
        $dailyVerificationsRaw = $metrics['dailyVerificationsRaw'];
        $dailySignupsRaw = $metrics['dailySignupsRaw'];
        $dailyRevenueRaw = $metrics['dailyRevenueRaw'];

        $dailyVerifications = collect();
        for ($i = 0; $i < 7; $i++) {
            $day = $startDate->copy()->addDays($i)->toDateString();
            $total = (int) ($dailyVerificationsRaw->firstWhere('day', $day)->total ?? 0);
            $dailyVerifications->push(['day' => $day, 'total' => $total]);
        }

        $dailySignups = collect();
        for ($i = 0; $i < 7; $i++) {
            $day = $startDate->copy()->addDays($i)->toDateString();
            $total = (int) ($dailySignupsRaw->firstWhere('day', $day)->total ?? 0);
            $dailySignups->push(['day' => $day, 'total' => $total]);
        }

        $dailyRevenue = collect();
        for ($i = 0; $i < 7; $i++) {
            $day = $startDate->copy()->addDays($i)->toDateString();
            $total = (float) ($dailyRevenueRaw->firstWhere('day', $day)->total ?? 0);
            $dailyRevenue->push(['day' => $day, 'total' => $total]);
        }

        $openTickets = $metrics['openTickets'];
        $answeredTickets = $metrics['answeredTickets'];
        $providersTotal = $metrics['providersTotal'];
        $providersDown = $metrics['providersDown'];
        $disabledFeatures = $metrics['disabledFeatures'];
        $pendingNotary = $metrics['pendingNotary'];
        $pendingShipments = $metrics['pendingShipments'];
        $openInvoices = $metrics['openInvoices'];

        $recentTransactions = $metrics['recentTransactions'];
        $recentVerifications = $metrics['recentVerifications'];
        $recentTickets = $metrics['recentTickets'];
        $disabledFeatureList = $metrics['disabledFeatureList'];

        $recentAdminAuditLogs = AdminAuditLog::query()
            ->with(['admin:id,username,email'])
            ->latest()
            ->take(10)
            ->get();

        $apiCenter = ApiCenter::first();
        $systemHealthItems = [
            [
                'label' => 'DataVerify API key',
                'ok' => (bool) ($apiCenter?->dataverify_api_key),
                'hint' => 'Configure API keys in Settings → API Keys.',
            ],
            [
                'label' => 'DataVerify NIN endpoint',
                'ok' => (bool) ($apiCenter?->dataverify_endpoint_nin),
                'hint' => 'Configure NIN endpoints in Settings → API Keys.',
            ],
            [
                'label' => 'Paystack secret key',
                'ok' => (bool) ($apiCenter?->paystack_secret_key),
                'hint' => 'Configure Paystack keys in Settings → API Keys.',
            ],
            [
                'label' => 'Flutterwave secret key',
                'ok' => (bool) ($apiCenter?->flutterwave_secret_key),
                'hint' => 'Configure Flutterwave keys in Settings → API Keys.',
            ],
            [
                'label' => 'VerifyMe webhook secret',
                'ok' => (bool) SystemSetting::get('verifyme_webhook_secret', ''),
                'hint' => 'Rotate webhook secrets in Settings → Security.',
            ],
        ];

        return view('admin.dashboard', compact(
            'totalUsers', 'onlineUsers', 'recentUsers',
            'newUsersToday',
            'totalRevenue', 'revenueToday',
            'tx24h', 'failedTx24h',
            'verificationsTotal', 'verifications24h', 'pendingVerifications',
            'topServices', 'dailyVerifications', 'dailySignups', 'dailyRevenue',
            'openTickets', 'answeredTickets',
            'providersTotal', 'providersDown',
            'disabledFeatures',
            'pendingNotary', 'pendingShipments', 'openInvoices',
            'recentTransactions', 'recentVerifications', 'recentTickets',
            'disabledFeatureList',
            'recentAdminAuditLogs',
            'systemHealthItems'
        ));
    }

    public function users(Request $request)
    {
        $query = User::with('balance');
        if ($search = $request->get('search')) {
            $query->where('fullname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }
        $users = $query->latest()->paginate(20)->withQueryString();

        $emails = $users->getCollection()->pluck('email')->filter()->values();
        $emailsWithTx = [];
        if ($emails->isNotEmpty()) {
            $emailsWithTx = Transaction::query()
                ->whereIn('user_email', $emails)
                ->distinct()
                ->pluck('user_email')
                ->all();
        }
        $emailTxLookup = array_fill_keys($emailsWithTx, true);

        $users->getCollection()->transform(function ($user) use ($emailTxLookup) {
            $user->has_transactions = isset($emailTxLookup[$user->email]);
            return $user;
        });

        return view('admin.users.index', compact('users'));
    }

    public function fundUser(Request $request)
    {
        $request->validate([
            'email'  => 'required|email|exists:users,email',
            'amount' => 'required|numeric|min:0.01',
            'note'   => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $user = User::where('email', $request->email)->firstOrFail();
            $reference = (string) Str::uuid();
            $amount = (float) $request->amount;
            $note = trim((string) ($request->note ?? ''));

            // Business Logic: Perform the credit
            $result = app(WalletService::class)->credit($user, $amount, 'Admin Credit' . ($note !== '' ? (': ' . $note) : ''), $reference);
            
            if (!($result['ok'] ?? false)) {
                throw new \Exception($result['message'] ?? 'Failed to credit wallet.');
            }

            // Record in Funding history
            $admin = Auth::guard('admin')->user();
            Funding::create([
                'funding_type' => 'Admin Credit',
                'amount' => $amount,
                'email' => $user->email,
                'fullname' => $admin?->email ?? $admin?->username ?? 'admin',
                'description' => $note ?: 'Wallet Credit (Admin Override)',
                'reference' => $reference,
            ]);

            // Audit
            Log::info('Admin wallet credit performed', [
                'target_user' => $user->email,
                'amount' => $amount,
                'admin_id' => $admin?->id,
                'reference' => $reference
            ]);

            try {
                app(\App\Services\Referrals\ReferralService::class)->handleFunding($user, $amount, $reference, 'Admin Credit');
            } catch (\Throwable $re) {
                Log::warning('Referral funding handler non-fatal failure', ['error' => $re->getMessage()]);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => "✅ Successfully funded ₦" . number_format($amount, 2) . ". Reference: " . $reference,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin wallet credit failed', [
                'error' => $e->getMessage(),
                'email' => $request->email,
                'amount' => $request->amount,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Operation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deductUser(Request $request)
    {
        $request->validate([
            'email'  => 'required|email|exists:users,email',
            'amount' => 'required|numeric|min:0.01',
            'note'   => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $user = User::where('email', $request->email)->firstOrFail();
            $amount = (float) $request->amount;
            $note = trim((string) ($request->note ?? ''));
            
            $debit = app(WalletService::class)->debit(
                $user,
                $amount,
                'Admin Deduction' . ($note !== '' ? (': ' . $note) : '')
            );

            if (!($debit['ok'] ?? false)) {
                return response()->json([
                    'status' => false,
                    'message' => $debit['message'] ?? 'Insufficient balance or debit failed.',
                ], 422);
            }

            $admin = Auth::guard('admin')->user();
            Funding::create([
                'funding_type' => 'Admin Deduction',
                'amount' => -($amount),
                'email' => $user->email,
                'fullname' => $admin?->email ?? $admin?->username ?? 'admin',
                'description' => $note ?: 'Wallet Deduction (Admin Override)',
                'reference' => (string) ($debit['txId'] ?? Str::uuid()),
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => "✅ Successfully deducted ₦" . number_format($amount, 2),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin wallet deduction failed', [
                'error' => $e->getMessage(),
                'email' => $request->email,
                'amount' => $request->amount
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Operation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function userHistory(Request $request, $email)
    {
        $history = Funding::query()
            ->where('email', $email)
            ->latest('id')
            ->paginate(20);

        $user = User::where('email', $email)->firstOrFail();

        return view('admin.users.history', compact('history', 'user'));
    }

    public function showUser(Request $request, int $id)
    {
        $user = User::with('balance')->findOrFail($id);

        $transactions = Transaction::query()
            ->where('user_email', $user->email)
            ->latest('id')
            ->limit(50)
            ->get();

        $fundings = Funding::query()
            ->where('email', $user->email)
            ->latest('id')
            ->limit(50)
            ->get();

        $activities = AbEvent::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit(50)
            ->get();

        $hasTransactions = Transaction::query()->where('user_email', $user->email)->exists();

        $kycService = app(\App\Services\KycService::class);
        $kycData = [
            'tier' => (int) $user->kyc_tier,
            'limits' => $kycService->getTierLimits($user->kyc_tier),
            'daily_spent' => $kycService->getDailySpent($user),
            'monthly_spent' => $kycService->getMonthlySpent($user),
        ];

        return view('admin.users.show', compact('user', 'transactions', 'fundings', 'activities', 'hasTransactions', 'kycData'));
    }

    public function refundUser(Request $request)
    {
        $request->validate([
            'email'  => 'required|email|exists:users,email',
            'amount' => 'required|numeric|min:0.01',
            'note'   => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $user = User::where('email', $request->email)->firstOrFail();
            $amount = (float) $request->amount;
            
            if (!Transaction::query()->where('user_email', $user->email)->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Refund is only allowed after the user has at least one activity sequence.',
                ], 422);
            }

            $reference = (string) Str::uuid();
            $note = trim((string) ($request->note ?? ''));

            $result = app(WalletService::class)->credit($user, $amount, 'Admin Refund' . ($note !== '' ? (': ' . $note) : ''), $reference);
            
            if (!($result['ok'] ?? false)) {
                throw new \Exception($result['message'] ?? 'Failed to credit refund.');
            }

            $admin = Auth::guard('admin')->user();
            Funding::create([
                'funding_type' => 'Admin Refund',
                'amount' => $amount,
                'email' => $user->email,
                'fullname' => $admin?->email ?? $admin?->username ?? 'admin',
                'description' => $note ?: 'Wallet Refund (Admin Resolution)',
                'reference' => $reference,
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => '✅ Successfully refunded ₦' . number_format($amount, 2) . '. Reference: ' . $reference,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin wallet refund failed', [
                'error' => $e->getMessage(),
                'email' => $request->email,
                'amount' => $request->amount
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Operation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateUserStatus(Request $request, int $id)
    {
        $request->validate([
            'user_status' => 'required|string|in:active,suspended,deleted',
        ]);

        $user = User::findOrFail($id);
        $user->user_status = $request->user_status;
        if ($request->user_status !== 'active') {
            $user->online_status = 'offline';
        }
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'User status updated to ' . $user->user_status . '.',
        ]);
    }

    public function resetUserPassword(Request $request, int $id)
    {
        $request->validate([
            'password' => 'nullable|string|min:8|max:128',
        ]);

        $user = User::findOrFail($id);
        $password = $request->input('password');
        if (!$password) {
            $password = Str::password(12);
        }

        $user->password = Hash::make($password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully.',
            'temporary_password' => $password,
        ]);
    }
}
