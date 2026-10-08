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

        // Top Leaderboard agents automatically calculated by total enrollments
        $leaderboard = EnrollmentAgent::where('status', 'approved')
            ->orderByDesc('total_enrollments')
            ->orderByDesc('monthly_enrollments')
            ->take(10)
            ->get();

        // MVP / MVA Agent of the month automatically calculated by total enrollments
        $mvaAgent = EnrollmentAgent::where('status', 'approved')
            ->where('is_mva_of_month', true)
            ->first() ?: EnrollmentAgent::where('status', 'approved')
            ->where('total_enrollments', '>', 0)
            ->orderByDesc('total_enrollments')
            ->first();

        // Agent targeted broadcasts
        $broadcasts = Broadcast::whereIn('target_audience', ['all', 'enrollment_agents'])
            ->latest()
            ->take(5)
            ->get();

        // 1. Performance Quotas & Tier Incentives
        $monthlyTarget = (int) \App\Models\SystemSetting::get('agent_monthly_target', 100);
        $monthlyCount = (int) ($agent->monthly_enrollments ?? 0);
        $targetProgress = min(100, round(($monthlyCount / max(1, $monthlyTarget)) * 100));
        $tierInfo = $agent->getCurrentTier();

        // 2. Health & Compliance Score
        $healthScore = $agent->getHealthScore();

        // 3. Operational Support & Regional Coordinator Desk
        $supportPhone = (string) (\App\Models\SystemSetting::get('agent_support_phone') ?: \App\Models\SystemSetting::get('whatsapp_number') ?: '2348000000000');
        $supportEmail = (string) \App\Models\SystemSetting::get('agent_support_email', 'support@fuwa.ng');
        $coordinatorName = data_get($agent->meta, 'coordinator_name') ?: ($agent->state ? $agent->state . ' State Desk Coordinator' : 'National Operations Desk');
        $prefilledText = "Hello Fuwa Agency Desk, my name is {$agent->full_name} (Code: " . ($agent->company_agent_code ?: 'AG-' . $agent->id) . ", IMEI: {$agent->machine_imei}). I need operational assistance with: ";
        $whatsappUrl = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $supportPhone) . '?text=' . urlencode($prefilledText);

        // 4. Station License Accreditation Info
        $effectiveFee = EnrollmentAgent::getEffectiveLicenseFee();
        $isPromo = EnrollmentAgent::isPromoActive();
        $promoEndsAt = \App\Models\SystemSetting::get('agent_license_promo_ends_at', '2026-10-10 23:59:59');
        $manualFunding = \Illuminate\Support\Facades\DB::table('manual_funding')->first();
        $walletBalance = (float) ($user->accountBalance->user_balance ?? 0.0);
        $paystackKey = \App\Support\PaymentProviderCredentials::paystack()['public_key'];

        return view('agent.dashboard', compact(
            'agent', 'leaderboard', 'mvaAgent', 'broadcasts',
            'monthlyTarget', 'targetProgress', 'tierInfo', 'healthScore',
            'supportPhone', 'supportEmail', 'coordinatorName', 'whatsappUrl',
            'effectiveFee', 'isPromo', 'promoEndsAt', 'manualFunding', 'walletBalance', 'paystackKey'
        ));
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
