<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Funding;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SelfFundingController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin');
    }

    public function index()
    {
        $admin = Auth::guard('admin')->user();
        $user = User::where('email', $admin->email)->first();
        
        $balance = 0;
        if ($user && $user->balance) {
            $balance = $user->balance->user_balance;
        }

        $limit = (float) SystemSetting::get('self_funding_limit', 10000000); // 10M default

        return view('admin.self_funding.index', compact('admin', 'user', 'balance', 'limit'));
    }

    public function fund(Request $request)
    {
        $limit = (float) SystemSetting::get('self_funding_limit', 10000000);

        $request->validate([
            'amount' => "required|numeric|min:0.01|max:{$limit}",
        ]);

        $admin = Auth::guard('admin')->user();
        $user = User::where('email', $admin->email)->first();

        try {
            DB::beginTransaction();

            $amount = (float) $request->amount;
            $reference = 'SELF-' . strtoupper(Str::random(12));
            $description = 'Super Admin Self-Funding (Internal Test Credits)';

            // Credit the wallet directly
            $result = app(WalletService::class)->credit($user, $amount, $description, $reference);

            if (!($result['ok'] ?? false)) {
                throw new \Exception($result['message'] ?? 'Failed to credit wallet.');
            }

            // Record in Funding history
            Funding::create([
                'funding_type' => 'Self-Funding',
                'amount' => $amount,
                'email' => $user->email,
                'fullname' => $admin->username,
                'description' => $description,
                'reference' => $reference,
            ]);

            // Audit Logging
            AdminAuditLog::create([
                'admin_id' => $admin->id,
                'action' => 'Super Admin Self-Funding',
                'meta' => [
                    'amount' => $amount,
                    'reference' => $reference,
                    'user_id' => $user->id,
                    'email' => $user->email,
                ],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "✅ Successfully added ₦" . number_format($amount, 2) . " to your account.",
                'balance' => number_format($result['newBalance'], 2),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Super Admin Self-Funding failed', [
                'error' => $e->getMessage(),
                'admin_id' => $admin->id,
                'amount' => $request->amount
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Internal error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
}
