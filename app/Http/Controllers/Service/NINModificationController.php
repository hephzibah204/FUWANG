<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;

class NINModificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $price = SystemSetting::get('nin_modification_price', 2500);
        
        return view('services.identity.nin_modification', compact('user', 'price'));
    }

    public function acceptConsent(Request $request)
    {
        $user = Auth::user();
        $user->update([
            'nin_mod_consent' => true,
            'nin_mod_consent_at' => now()
        ]);

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
            'photo' => 'required|file|image|max:5120', // 5MB max
        ]);

        $user = Auth::user();
        if ($request->self_service === 'yes') {
            return response()->json(['status' => false, 'message' => 'Users with existing Delinked Self-Service accounts cannot continue.']);
        }

        if (!$user->nin_mod_consent) {
            return response()->json(['status' => false, 'message' => 'You must accept the terms and conditions first.']);
        }

        $price = SystemSetting::get('nin_modification_price', 2500);
        $wallet = app(\App\Services\WalletService::class);
        
        $debit = $wallet->debit($user, (float)$price, 'NIN Modification Request', 'NIN_MOD');
        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        try {
            // Store the supporting document
            $path = $request->file('photo')->store('verifications/nin_modifications', 'public');

            // Log the verification request
            \App\Models\VerificationResult::create([
                'user_id' => $user->id,
                'service_type' => 'nin_modification',
                'identifier' => $request->nin,
                'provider_name' => 'NEXUS_INTERNAL',
                'status' => 'waiting_for_review', 
                'reference_id' => 'NIN-MOD-' . strtoupper(bin2hex(random_bytes(4))),
                'response_data' => [
                    'nin' => $request->nin,
                    'full_name' => $request->full_name,
                    'modification_type' => $request->modification_type,
                    'old_value' => $request->old_value,
                    'new_value' => $request->new_value,
                    'document_path' => $path
                ]
            ]);

            $wallet->markTransactionSuccess($debit['txId']);

            return response()->json([
                'status' => true, 
                'message' => 'Modification request submitted successfully. Our agents will process it shortly.'
            ]);

        } catch (\Exception $e) {
            $wallet->failAndRefund($user, (float)$price, 'NIN Modification Failure', $debit['txId']);
            return response()->json(['status' => false, 'message' => 'An error occurred during submission. Your balance has been refunded.']);
        }
    }
}
