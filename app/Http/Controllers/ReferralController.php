<?php

namespace App\Http\Controllers;

use App\Services\Referrals\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    public function __construct(protected ReferralService $referralService)
    {
    }

    public function index()
    {
        $user = Auth::user();
        $stats = $this->referralService->statsForUser($user);
        $recent = $this->referralService->recentForUser($user);
        $link = $this->referralService->referralLink($user);

        return view('referrals.index', compact('stats', 'recent', 'link'));
    }
}
