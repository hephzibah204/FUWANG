<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountBalance;
use App\Models\NotifyingCenter;
use App\Services\Referrals\ReferralService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $balance = AccountBalance::where('user_id', $user->id)->first();
        if (!$balance) {
            $balance = AccountBalance::where('email', $user->email)->first();
            if ($balance && !$balance->user_id) {
                $balance->update(['user_id' => $user->id]);
            }
        }
        if (!$balance) {
            $balance = AccountBalance::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'user_balance' => 0.00,
                'api_key' => 'user',
            ]);
        }

        // Fetch notification
        $notification = NotifyingCenter::latest()->first();

        // Fetch verification count
        $verificationCount = \App\Models\VerificationResult::where('user_id', $user->id)->count();

        // Fetch referral stats
        $referralService = app(ReferralService::class);
        $referralService->ensureUserReferralCode($user);
        $referralStats = $referralService->statsForUser($user);

        return view('dashboard', [
            'user' => $user,
            'balance' => $balance->user_balance,
            'notification' => $notification ? $notification->notification : '',
            'verificationCount' => $verificationCount,
            'referralStats' => $referralStats
        ]);
    }
}
