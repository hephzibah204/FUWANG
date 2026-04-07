<?php

namespace App\Http\Controllers;

use App\Services\Referrals\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    public function index()
    {
        $user = Auth::user();
        $this->referralService->ensureUserReferralCode($user);

        $stats = $this->referralService->statsForUser($user);
        $recentReferrals = $this->referralService->recentForUser($user);
        $referralLink = $this->referralService->referralLink($user);

        return view('referrals.index', compact('stats', 'recentReferrals', 'referralLink'));
    }
}
