<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Post-payment onboarding instructions (owner spec 2026-11-27): after an
 * agent pays for their license, the next thing is onboarding. The agent
 * must read the instructions: (1) get their device ready, (2) request
 * their VPN, (3) get the Admin Onboarding details. The full process
 * document is still to come from the owner; this page carries the three
 * steps with the platform's real contact data.
 */
class AgentOnboardingInstructionsController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $agent = $user?->enrollmentAgent;

        if (!$agent) {
            return redirect()->route('agent.dashboard')->with('error', 'Agent profile not found.');
        }

        if (!$agent->isLicensePaid()) {
            return redirect()->route('agent.dashboard')
                ->with('info', 'Pay for your station license first; your onboarding instructions unlock immediately after payment.');
        }

        $supportPhone = (string) (SystemSetting::get('agent_support_phone') ?: SystemSetting::get('whatsapp_number') ?: '2348000000000');
        $supportEmail = (string) SystemSetting::get('agent_support_email', 'support@fuwa.ng');
        $coordinatorName = data_get($agent->meta, 'coordinator_name')
            ?: ($agent->state ? $agent->state . ' State Desk Coordinator' : 'National Operations Desk');
        $prefilledText = "Hello Fuwa Agency Desk, my name is {$agent->full_name} (Code: " . ($agent->company_agent_code ?: 'AG-' . $agent->id) . "). I have paid for my station license and I am completing my onboarding.";
        $whatsappUrl = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $supportPhone) . '?text=' . urlencode($prefilledText);

        return view('agent.license-onboarding', [
            'agent' => $agent,
            'supportPhone' => $supportPhone,
            'supportEmail' => $supportEmail,
            'coordinatorName' => $coordinatorName,
            'whatsappUrl' => $whatsappUrl,
            'vpnRequestedAt' => data_get($agent->meta, 'vpn_requested_at'),
            'readAt' => data_get($agent->meta, 'onboarding_read_at'),
        ]);
    }

    public function requestVpn(Request $request)
    {
        $agent = Auth::user()?->enrollmentAgent;

        if (!$agent || !$agent->isLicensePaid()) {
            return back()->with('error', 'Pay for your station license before requesting a VPN.');
        }

        $meta = (array) ($agent->meta ?? []);
        if (empty($meta['vpn_requested_at'])) {
            $meta['vpn_requested_at'] = now()->toDateTimeString();
            $meta['vpn_status'] = 'requested';
            $agent->update(['meta' => $meta]);
        }

        return back()->with('success', 'Your VPN request has been sent to the admin desk. They will send your VPN details to you.');
    }

    public function markRead(Request $request)
    {
        $agent = Auth::user()?->enrollmentAgent;

        if (!$agent || !$agent->isLicensePaid()) {
            return back()->with('error', 'Pay for your station license first.');
        }

        $meta = (array) ($agent->meta ?? []);
        $meta['onboarding_read_at'] = now()->toDateTimeString();
        $agent->update(['meta' => $meta]);

        return back()->with('success', 'Thank you. You have confirmed reading the onboarding instructions.');
    }
}
