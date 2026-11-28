<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Post-payment onboarding instructions (owner spec 2026-11-27): after an
 * agent pays for their license, the next thing is onboarding. The page
 * carries the NIMC activation process the owner posted: activation
 * request, Fuwa approval, workflow number to NIMC, NIMC approval,
 * biometrics onboarding, three test enrolments, then the switch to
 * production via a second request. Agents reach the onboarding desk on
 * WhatsApp at 0703 253 2424 (buttons prefilled with name, code, IMEI).
 * The owner notes the process will still improve later.
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

        // Onboarding desk (owner instruction 2026-11-27): agents message
        // 0703 253 2424 on WhatsApp for their onboarding process. The
        // prefilled message carries the applicant details and the device
        // IMEI collected at registration (owner instruction 2026-11-28);
        // the agent only says they have paid and are ready for onboarding.
        $onboardingPhoneDisplay = '0703 253 2424';
        $detailsBlock = "\n\nMy details:"
            . "\nName: {$agent->full_name}"
            . "\nAgent Code: " . ($agent->company_agent_code ?: 'AG-' . $agent->id)
            . "\nPhone: {$agent->phone_number}"
            . "\nEmail: {$user->email}"
            . ($agent->state ? "\nState: {$agent->state}" : '')
            . "\nDevice IMEI: " . ($agent->machine_imei ?: 'Not yet added');
        $activationCode = $agent->ensureActivationCode();
        $activationText = "Hello, I am {$agent->full_name}. I just made payment for my station license and I am ready for onboarding." . $detailsBlock . "\nActivation Code: {$activationCode}";
        $activationWhatsappUrl = 'https://wa.me/2347032532424?text=' . urlencode($activationText);
        $productionText = "Hello, I am {$agent->full_name}. I have completed my three test enrolments and toggled the switch to production. I am sending my switch to production request." . $detailsBlock;
        $productionWhatsappUrl = 'https://wa.me/2347032532424?text=' . urlencode($productionText);

        return view('agent.license-onboarding', [
            'agent' => $agent,
            'supportPhone' => $supportPhone,
            'supportEmail' => $supportEmail,
            'coordinatorName' => $coordinatorName,
            'whatsappUrl' => $whatsappUrl,
            'onboardingPhoneDisplay' => $onboardingPhoneDisplay,
            'activationWhatsappUrl' => $activationWhatsappUrl,
            'productionWhatsappUrl' => $productionWhatsappUrl,
            'vpnRequestedAt' => data_get($agent->meta, 'vpn_requested_at'),
            'readAt' => data_get($agent->meta, 'onboarding_read_at'),
            'activationCode' => $activationCode,
            'activationRequestedAt' => data_get($agent->meta, 'activation_requested_at'),
            'activationStatus' => $agent->activationStatus(),
            'onboardingStatus' => $agent->onboardingStatus(),
            'onboardingStatusLabel' => $agent->onboardingStatusLabel(),
        ]);
    }

    /**
     * The in-app half of the activation request: recorded against the
     * logged-in, paid account, so the admin desk works from a request
     * that cannot be forged by copying a WhatsApp message.
     */
    public function requestActivation(Request $request)
    {
        $agent = Auth::user()?->enrollmentAgent;

        if (!$agent || !$agent->isLicensePaid()) {
            return back()->with('error', 'Pay for your station license before sending an activation request.');
        }

        $agent->ensureActivationCode();
        $meta = (array) ($agent->meta ?? []);
        if (empty($meta['activation_requested_at'])) {
            $meta['activation_requested_at'] = now()->toDateTimeString();
            $meta['activation_status'] = 'requested';
            $agent->update(['meta' => $meta]);
        }

        return back()->with('success', 'Your activation request is recorded. Now message the onboarding desk on WhatsApp from the phone number you registered with, so they can match and approve it.');
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
