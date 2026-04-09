<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\AuctionLot;
use App\Models\User;
use App\Models\VerificationResult;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Fetch data for BI dashboard
        $stats = [
            'total_revenue' => Transaction::where('status', 'completed')->sum('amount'),
            'total_transactions' => Transaction::count(),
            'total_users' => User::count(),
            'total_auctions' => AuctionLot::count(),
            'daily_verifications' => VerificationResult::where('created_at', '>=', Carbon::today())->count(),
            'daily_success_verifications' => VerificationResult::where('created_at', '>=', Carbon::today())->where('status', 'success')->count(),
            'daily_failed_verifications' => VerificationResult::where('created_at', '>=', Carbon::today())->where('status', 'failed')->count(),
        ];

        $revenue_by_service = Transaction::where('status', 'completed')
            ->select('service', DB::raw('SUM(amount) as total'))
            ->groupBy('service')
            ->orderBy('total', 'desc')
            ->get();

        $user_growth = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as users'))
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $verification_history = VerificationResult::select(DB::raw('DATE(created_at) as date'), 'status', DB::raw('count(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date', 'status')
            ->orderBy('date', 'asc')
            ->get();

        return view('admin.dashboard', compact('stats', 'revenue_by_service', 'user_growth', 'verification_history'));
    }
}
