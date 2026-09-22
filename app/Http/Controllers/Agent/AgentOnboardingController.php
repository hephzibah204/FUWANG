<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AgentOnboardingController extends Controller
{
    public function showPortal()
    {
        $user = Auth::user();
        $agent = $user->enrollmentAgent;

        if (!$agent) {
            return redirect()->route('agent.register')->with('info', 'Please submit your basic agent details first.');
        }

        if ($agent->isFullyActivated()) {
            return redirect()->route('agent.dashboard');
        }

        return view('agent.onboarding.index', compact('agent'));
    }

    public function uploadDocs(Request $request)
    {
        $user = Auth::user();
        $agent = $user->enrollmentAgent;

        if (!$agent) {
            return redirect()->route('agent.register');
        }

        $validated = $request->validate([
            'utility_bill' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
            'picture' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'business_doc' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
            'business_registration_number' => ['nullable', 'string', 'max:100'],
        ]);

        $updates = [];

        if ($request->hasFile('utility_bill')) {
            $updates['utility_bill_path'] = $request->file('utility_bill')->store('agent_kyc', 'public');
        }

        if ($request->hasFile('picture')) {
            $updates['picture_path'] = $request->file('picture')->store('agent_kyc', 'public');
        }

        if ($request->hasFile('business_doc')) {
            $updates['business_registration_doc_path'] = $request->file('business_doc')->store('agent_kyc', 'public');
        }

        if ($request->filled('business_registration_number')) {
            $updates['business_registration_number'] = $validated['business_registration_number'];
        }

        if (!empty($updates)) {
            $updates['onboarding_step'] = 'kyc_docs';
            $agent->update($updates);
        }

        return back()->with('success', 'Profile photo & KYC documents updated successfully. Uploading a clear profile photo activates your agent account.');
    }

    public function acceptCompliance(Request $request)
    {
        $user = Auth::user();
        $agent = $user->enrollmentAgent;

        if (!$agent) {
            return redirect()->route('agent.register');
        }

        if (empty($agent->utility_bill_path) || empty($agent->picture_path)) {
            return back()->with('error', 'Please upload your Utility Bill and Passport Picture before accepting compliance terms.');
        }

        $request->validate([
            'agree_no_non_appearance' => ['required', 'accepted'],
            'agree_no_illegal_enrollment' => ['required', 'accepted'],
            'agree_data_privacy' => ['required', 'accepted'],
            'agree_no_terminal_tampering' => ['required', 'accepted'],
            'agree_legal_liability' => ['required', 'accepted'],
        ]);

        $agent->update([
            'accepted_terms' => true,
            'terms_accepted_at' => now(),
            'onboarding_step' => 'submitted',
            'status' => 'pending',
        ]);

        return back()->with('success', 'Your full Agency KYC application & NIMC Code of Conduct agreement have been submitted! An administrator will review your application shortly.');
    }
}
