<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class NINModificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $price = SystemSetting::get('nin_modification_price', 2500);
        
        $hasConsent = $this->hasConsent($request);
        return view('services.identity.nin_modification', compact('user', 'price', 'hasConsent'));
    }

    public function acceptConsent(Request $request)
    {
        $this->recordConsent($request);

        return response()->json(['status' => true, 'message' => 'Terms accepted successfully.']);
    }

    public function submit(Request $request)
    {
        $request->validate([
            'self_service' => 'required|string|in:yes,no',
            'nin' => 'required|string|digits:11',
            'full_name' => 'required|string|max:255',
            'modification_type' => 'required|string|in:name,dob,address,phone,email',
            'old_value' => 'required|string|max:1000',
            'new_value' => 'required|string|max:1000',
            'photo' => 'required|file|mimes:jpeg,png,jpg,webp,pdf|max:10240', // 10MB max, supports images & PDF
            'consent' => 'nullable',
        ]);

        $user = Auth::user();
        if ($request->self_service === 'yes') {
            return response()->json(['status' => false, 'message' => 'Users with existing Delinked Self-Service accounts cannot continue.']);
        }

        if (!$this->hasConsent($request)) {
            if ($request->boolean('consent') || $request->input('consent') === 'yes' || $request->input('consent') === '1') {
                $this->recordConsent($request);
            } else {
                return response()->json(['status' => false, 'message' => 'You must accept the terms and conditions first.']);
            }
        }

        $price = SystemSetting::get('nin_modification_price', 2500);
        $wallet = app(\App\Services\WalletService::class);
        
        $debit = $wallet->debit($user, (float)$price, 'NIN Modification Request', 'NIN_MOD');
        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        try {
            // Store the supporting document
            $file = $request->file('photo');
            $path = $file->store('verifications/nin_modifications', 'public');
            $refId = 'NIN-MOD-' . strtoupper(bin2hex(random_bytes(4)));

            // Log the verification request for admin pickup
            $result = \App\Models\VerificationResult::create([
                'user_id' => $user->id,
                'service_type' => 'nin_modification',
                'identifier' => $request->nin,
                'provider_name' => 'NEXUS_INTERNAL',
                'status' => 'waiting_for_review', 
                'reference_id' => $refId,
                'admin_note' => 'Awaiting review and processing by certified NIMC modification agent.',
                'response_data' => [
                    'nin' => $request->nin,
                    'full_name' => $request->full_name,
                    'modification_type' => $request->modification_type,
                    'old_value' => $request->old_value,
                    'new_value' => $request->new_value,
                    'document_path' => $path,
                    'document_name' => $file->getClientOriginalName(),
                    'document_mime' => $file->getMimeType(),
                    'submitted_at' => now()->toIso8601String(),
                    'consent_accepted' => true,
                    'consent_recorded_at' => now()->toIso8601String(),
                    'transaction_id' => $debit['txId'],
                    'amount_paid' => (float) $price,
                ]
            ]);

            $wallet->markTransactionSuccess($debit['txId']);

            return response()->json([
                'status' => true, 
                'message' => 'Modification request submitted successfully. Our agents will process it shortly.',
                'reference_id' => $refId,
                'result_id' => $result->id,
                'redirect_url' => route('history')
            ]);

        } catch (\Exception $e) {
            report($e);
            $wallet->failAndRefund($user, (float)$price, 'NIN Modification Failure', $debit['txId']);
            return response()->json(['status' => false, 'message' => 'An error occurred during submission. Your balance has been refunded.']);
        }
    }

    private function hasConsent(Request $request): bool
    {
        return (bool) ($request->user()->nin_mod_consent
            || $request->session()->get('nin_mod_consent.' . $request->user()->id));
    }

    private function recordConsent(Request $request): void
    {
        $user = $request->user();
        // Older installations may not yet have the optional profile consent columns.
        // Keep acceptance in the authenticated session and in each submitted request.
        if (Schema::hasColumn('users', 'nin_mod_consent') && Schema::hasColumn('users', 'nin_mod_consent_at')) {
            $user->forceFill(['nin_mod_consent' => true, 'nin_mod_consent_at' => now()])->save();
        }
        $request->session()->put('nin_mod_consent.' . $user->id, now()->toIso8601String());
    }
}
