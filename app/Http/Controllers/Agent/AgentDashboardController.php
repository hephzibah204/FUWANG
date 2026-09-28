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
        $mode = $request->input('mode', $request->query('mode', 'agency'));

        if (! in_array($mode, ['agency', 'user'], true)) {
            $mode = 'agency';
        }

        $user = Auth::user();

        if ($mode === 'agency') {
            if (! $user->enrollmentAgent) {
                // Auto-link pre-approved agent record if available
                $preApproved = \App\Models\PreApprovedAgent::where('claimed_by_user_id', $user->id)
                    ->orWhere('email', $user->email)
                    ->first();

                if ($preApproved) {
                    \App\Models\EnrollmentAgent::firstOrCreate(
                        ['user_id' => $user->id],
                        [
                            'agent_code' => $preApproved->agent_code,
                            'status' => 'approved',
                            'state_of_operation' => $preApproved->state ?? 'FCT',
                            'lga_of_operation' => $preApproved->lga ?? 'Abuja',
                            'nin' => $preApproved->nin ?? '',
                        ]
                    );
                    $user->load('enrollmentAgent');
                }
            }

            if (! $user->enrollmentAgent) {
                return redirect()->route('dashboard')->with('error', 'You must be an enrollment agent to access Agency mode.');
            }
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
