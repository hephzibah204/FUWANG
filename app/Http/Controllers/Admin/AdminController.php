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
                ->get();

            return [
                'totalUsers' => $aggregates['total_users'] ?? User::count(),
                'totalVerifications' => $aggregates['total_verifications'] ?? VerificationResult::count(),
                'totalRevenue' => $aggregates['total_revenue'] ?? Transaction::where('status', 'success')->where('balance_before', '>', 'balance_after')->sum(DB::raw('balance_before - balance_after')),
                'pendingTickets' => Ticket::where('status', 'open')->count(),
                'recentUsers' => $recentUsers,
                'topServices' => $topServices,
                'dailyVerifications' => $dailyVerificationsRaw,
                'dailySignups' => $dailySignupsRaw,
                'dailyRevenue' => $dailyRevenueRaw,
                'recentTransactions' => $recentTransactions,
                'recentVerifications' => $recentVerifications,
                'recentTickets' => $recentTickets,
                'disabledFeatures' => $disabledFeatureList,
            ];
        });

        return view('admin.dashboard', $metrics);
    }

    public function users(Request $request)
    {
        $query = User::query()
            ->with('balance')
            ->withExists(['transactions as has_transactions']);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->has('role')) {
            $query->where('role', $request->get('role'));
        }

        $users = $query->latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function showUser($id)
    {
        $user = User::with(['balance', 'transactions' => function($q) {
            $q->latest()->take(10);
        }])->findOrFail($id);

        $kycService = app(\App\Services\KycService::class);
        $kycData = [
            'limits' => $kycService->getTierLimits((int)$user->kyc_tier),
            'daily_spent' => $kycService->getDailySpent($user),
            'monthly_spent' => $kycService->getMonthlySpent($user),
        ];
        
        $transactions = $user->transactions;
        $hasTransactions = $transactions->isNotEmpty();

        return view('admin.users.show', compact('user', 'kycData', 'transactions', 'hasTransactions'));
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
            AdminAuditLog::create([
                'admin_id' => $admin?->id,
                'action' => 'admin.user.fund',
                'meta' => [
                    'target_user' => $user->email,
                    'amount' => $amount,
                    'reference' => $reference,
                    'note' => $note,
                ],
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);

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

            $reference = (string) ($debit['txId'] ?? Str::uuid());
            $admin = Auth::guard('admin')->user();
            Funding::create([
                'funding_type' => 'Admin Deduction',
                'amount' => -($amount),
                'email' => $user->email,
                'fullname' => $admin?->email ?? $admin?->username ?? 'admin',
                'description' => $note ?: 'Wallet Deduction (Admin Override)',
                'reference' => $reference,
            ]);

            // Audit
            AdminAuditLog::create([
                'admin_id' => $admin?->id,
                'action' => 'admin.user.deduct',
                'meta' => [
                    'target_user' => $user->email,
                    'amount' => $amount,
                    'reference' => $reference,
                    'note' => $note,
                ],
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);

            Log::info('Admin wallet deduction performed', [
                'target_user' => $user->email,
                'amount' => $amount,
                'admin_id' => $admin?->id,
                'reference' => $reference
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => "✅ Successfully deducted ₦" . number_format($amount, 2) . ". Reference: " . $reference,
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

    public function apiApplications(Request $request)
    {
        $query = User::whereIn('api_access_status', ['pending', 'approved', 'rejected']);

        if ($request->has('status')) {
            $query->where('api_access_status', $request->get('status'));
        }

        $applications = $query->latest()->paginate(20);
        return view('admin.api_applications.index', compact('applications'));
    }

    public function updateApiStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,none',
            'reason' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($id);
        $oldStatus = $user->api_access_status;
        $user->api_access_status = $request->status;
        
        $details = $user->api_application_details ?? [];
        $details['reviewed_at'] = now()->toIso8601String();
        $details['reviewed_by'] = Auth::guard('admin')->id();
        $details['review_reason'] = $request->reason;
        $user->api_application_details = $details;
        
        $user->save();

        AdminAuditLog::create([
            'admin_id' => Auth::guard('admin')->id(),
            'action' => 'admin.api.update_status',
            'meta' => [
                'target_user' => $user->email,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'reason' => $request->reason,
            ],
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        return back()->with('success', "API access status for {$user->email} updated to {$request->status}.");
    }

    public function refundUser(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|string|exists:transactions,transaction_id',
            'note' => 'nullable|string|max:255',
        ]);

        try {
            $transaction = Transaction::where('transaction_id', $request->transaction_id)->firstOrFail();

            if ($transaction->status === 'refunded') {
                return response()->json(['status' => false, 'message' => 'Transaction already refunded.'], 422);
            }

            $adminCreditId = 'REF-' . $request->transaction_id;
            if (Transaction::where('transaction_id', $adminCreditId)->exists()
                || Transaction::where('transaction_id', $request->transaction_id . '-RF')->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'This debit was already reversed (a refund credit already exists).',
                ], 422);
            }

            $before = round((float) $transaction->balance_before, 2);
            $after = round((float) $transaction->balance_after, 2);
            if ($after >= $before) {
                return response()->json([
                    'status' => false,
                    'message' => 'Only debit transactions (wallet outflow) can be refunded.',
                ], 422);
            }

            $amount = round($before - $after, 2);
            if ($amount <= 0) {
                return response()->json(['status' => false, 'message' => 'Invalid refund amount.'], 422);
            }

            $user = User::where('email', $transaction->user_email)->firstOrFail();
            $note = trim((string) ($request->note ?? ''));
            $orderType = 'Admin Refund: ' . $transaction->transaction_id . ($note !== '' ? (' – ' . $note) : '');
            $creditTxId = $adminCreditId;

            DB::beginTransaction();

            $credit = app(WalletService::class)->credit($user, $amount, $orderType, $creditTxId);
            if (!($credit['ok'] ?? false)) {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => $credit['message'] ?? 'Could not credit wallet.',
                ], 422);
            }

            $transaction->status = 'refunded';
            $transaction->save();

            $admin = Auth::guard('admin')->user();
            AdminAuditLog::create([
                'admin_id' => $admin?->id,
                'action' => 'admin.user.refund',
                'meta' => [
                    'transaction_id' => $transaction->transaction_id,
                    'credit_transaction_id' => $creditTxId,
                    'amount' => $amount,
                    'user' => $user->email,
                    'note' => $note,
                ],
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);

            DB::commit();

            app(WalletService::class)->notifyWalletRefund($user, $amount, $orderType, $creditTxId);

            return response()->json([
                'status' => true,
                'message' => '✅ Successfully refunded ₦' . number_format($amount, 2) . ' for TX: ' . $transaction->transaction_id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin wallet refund failed', [
                'error' => $e->getMessage(),
                'tx_id' => $request->transaction_id,
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Operation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Debit transactions eligible for admin refund (success/pending, balance decreased).
     */
    public function refundableTransactions(int $id)
    {
        $user = User::findOrFail($id);
        $candidates = $user->transactions()
            ->whereIn('status', ['success', 'pending'])
            ->whereColumn('balance_after', '<', 'balance_before')
            ->latest()
            ->limit(80)
            ->get(['transaction_id', 'order_type', 'balance_before', 'balance_after', 'status', 'created_at']);

        $ids = $candidates->pluck('transaction_id')->all();
        $refundIds = [];
        foreach ($ids as $tid) {
            $refundIds[] = 'REF-' . $tid;
            $refundIds[] = $tid . '-RF';
        }
        $existingRefundIds = $refundIds === []
            ? collect()
            : Transaction::query()
                ->where('user_email', $user->email)
                ->whereIn('transaction_id', $refundIds)
                ->pluck('transaction_id');

        $blockedOriginal = [];
        foreach ($existingRefundIds as $rid) {
            if (str_starts_with($rid, 'REF-')) {
                $blockedOriginal[substr($rid, 4)] = true;
            } elseif (str_ends_with($rid, '-RF') && strlen($rid) > 3) {
                $blockedOriginal[substr($rid, 0, -3)] = true;
            }
        }

        $rows = $candidates
            ->filter(fn (Transaction $t) => !isset($blockedOriginal[$t->transaction_id]))
            ->take(50)
            ->values();

        return response()->json([
            'transactions' => $rows->map(function (Transaction $t) {
                return [
                    'transaction_id' => $t->transaction_id,
                    'order_type' => $t->order_type,
                    'amount' => round(abs((float) $t->balance_before - (float) $t->balance_after), 2),
                    'status' => $t->status,
                    'created_at' => $t->created_at?->toIso8601String(),
                ];
            }),
        ]);
    }

    public function updateUserStatus($id, Request $request)
    {
        $request->validate([
            'status' => 'required|string|in:active,suspended,pending',
        ]);

        $user = User::findOrFail($id);
        $user->user_status = $request->status;
        $user->save();

        // Audit
        $admin = Auth::guard('admin')->user();
        AdminAuditLog::create([
            'admin_id' => $admin?->id,
            'action' => 'admin.user.status_update',
            'meta' => [
                'user_id' => $id,
                'new_status' => $request->status,
            ],
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        return response()->json([
            'status' => true,
            'message' => "User status updated to " . ucfirst($request->status),
        ]);
    }

    public function resetUserPassword($id, Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::findOrFail($id);
        $user->password = Hash::make($request->password);
        $user->save();

        // Audit
        $admin = Auth::guard('admin')->user();
        AdminAuditLog::create([
            'admin_id' => $admin?->id,
            'action' => 'admin.user.password_reset',
            'meta' => ['user_id' => $id],
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        return response()->json([
            'status' => true,
            'message' => "User password has been reset successfully.",
        ]);
    }

    public function userHistory(string $email)
    {
        $email = urldecode($email);
        $user = User::where('email', $email)->first();
        $history = Transaction::where('user_email', $email)->latest()->paginate(15);

        return view('admin.users.history', compact('user', 'history', 'email'));
    }
}
