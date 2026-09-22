<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Broadcast;
use App\Models\EnrollmentAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $agent = $user->enrollmentAgent;

        // Ensure user active dashboard mode is agency
        session(['active_dashboard_mode' => 'agency']);

        // Top Leaderboard agents
        $leaderboard = EnrollmentAgent::where('status', 'approved')
            ->orderBy('monthly_enrollments', 'desc')
            ->orderBy('total_enrollments', 'desc')
            ->take(10)
            ->get();

        // MVA Agent of the month
        $mvaAgent = EnrollmentAgent::where('status', 'approved')
            ->where('is_mva_of_month', true)
            ->first();

        // Agent targeted broadcasts
        $broadcasts = Broadcast::whereIn('target_audience', ['all', 'enrollment_agents'])
            ->latest()
            ->take(5)
            ->get();

        return view('agent.dashboard', compact('agent', 'leaderboard', 'mvaAgent', 'broadcasts'));
    }

    public function switchMode(Request $request)
    {
        $request->validate([
            'mode' => ['required', 'in:agency,user'],
        ]);

        $mode = $request->input('mode');
        $user = Auth::user();

        if ($mode === 'agency' && ! $user->isApprovedEnrollmentAgent()) {
            return back()->with('error', 'You must be an approved enrollment agent to access Agency mode.');
        }

        session(['active_dashboard_mode' => $mode]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'mode' => $mode,
                'redirect' => $mode === 'agency' ? route('agent.dashboard') : route('dashboard'),
            ]);
        }

        return redirect()->to($mode === 'agency' ? route('agent.dashboard') : route('dashboard'));
    }
}
