<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentLicenseTransaction;
use App\Models\ApiCenter;
use App\Models\EnrollmentAgent;
use App\Models\SystemSetting;
use App\Services\WalletService;
use App\Support\PaymentProviderCredentials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AgentLicenseController extends Controller
{
    /**
     * Get License Information and active pricing for modal/UI
     */
    public function getLicenseInfo()
    {
        $user = Auth::user();
        $agent = $user?->enrollmentAgent;

        if (!$agent) {
            return response()->json(['status' => false, 'message' => 'Agent profile not found.'], 404);
        }

        $effectiveFee = EnrollmentAgent::getEffectiveLicenseFee();
        $isPromo = EnrollmentAgent::isPromoActive();
        $promoEndsAt = SystemSetting::get('agent_license_promo_ends_at', '2026-10-10 23:59:59');
        $promoPrice = (float) SystemSetting::get('agent_license_promo_price', 100000.00);
        $regularPrice = (float) SystemSetting::get('agent_license_regular_price', 150000.00);

        $manualFunding = DB::table('manual_funding')->first();

        return response()->json([
            'status' => true,
            'agent' => [
                'id' => $agent->id,
                'full_name' => $agent->full_name,
                'company_agent_code' => $agent->company_agent_code,
                'license_status' => $agent->license_status,
                'license_fee_paid' => $agent->license_fee_paid,
                'license_payment_method' => $agent->license_payment_method,
                'license_payment_reference' => $agent->license_payment_reference,
                'license_paid_at' => $agent->license_paid_at?->format('d M Y, h:i A'),
                'license_rejection_reason' => $agent->license_rejection_reason,
            ],
            'pricing' => [
                'effective_fee' => $effectiveFee,
                'is_promo' => $isPromo,
                'promo_price' => $promoPrice,
                'regular_price' => $regularPrice,
                'promo_ends_at' => $promoEndsAt,
            ],
            'bank_details' => [
                'bank_name' => $manualFunding?->bank_name ?? 'Zenith Bank',
                'account_number' => $manualFunding?->account_number ?? '1234567890',
                'account_name' => $manualFunding?->account_name ?? 'Fuwa Logistics Services',
            ],
        ]);
    }

    /**
     * Initialize Paystack transaction for Agent License Fee
     */
    public function initPaystack(Request $request)
    {
        $user = Auth::user();
        $agent = $user?->enrollmentAgent;

        if (!$agent) {
            return response()->json(['status' => false, 'message' => 'Enrollment Agent profile not found.'], 404);
        }

        if ($agent->isLicensePaid()) {
            return response()->json(['status' => false, 'message' => 'Agent license is already paid and accredited.'], 422);
        }

        $fee = EnrollmentAgent::getEffectiveLicenseFee();
        $amountKobo = (int) round($fee * 100);

        $apiCenter = ApiCenter::first();
        $paystack = PaymentProviderCredentials::paystack($apiCenter);
        $publicKey = $paystack['public_key'];

        if (!$publicKey) {
            return response()->json(['status' => false, 'message' => 'Paystack gateway is currently unconfigured. Please use Wallet or Offline Transfer.'], 422);
        }

        $reference = 'LIC-' . date('Ymd') . '-' . $agent->id . '-' . strtoupper(Str::random(6));

        AgentLicenseTransaction::createSafe([
            'agent_id' => $agent->id,
            'user_id' => $user->id,
            'amount' => $fee,
            'payment_method' => 'paystack',
            'reference' => $reference,
            'status' => 'pending',
            'meta' => [
                'type' => 'agent_license_fee',
                'is_promo' => EnrollmentAgent::isPromoActive(),
            ],
        ]);

        return response()->json([
            'status' => true,
            'public_key' => $publicKey,
            'email' => $user->email,
            'amount' => $fee,
            'amount_kobo' => $amountKobo,
            'reference' => $reference,
            'metadata' => [
                'payment_type' => 'agent_license',
                'agent_id' => $agent->id,
                'user_id' => $user->id,
                'custom_fields' => [
                    [
                        'display_name' => 'Payment For',
                        'variable_name' => 'payment_for',
                        'value' => 'NIN Enrollment Agent Station License',
                    ],
                    [
                        'display_name' => 'Agent Name',
                        'variable_name' => 'agent_name',
                        'value' => $agent->full_name,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Verify Paystack Transaction and automatically mark license paid
     */
    public function verifyPaystack(Request $request)
    {
        $request->validate([
            'reference' => ['required', 'string', 'max:120'],
        ]);

        $user = Auth::user();
        $agent = $user?->enrollmentAgent;

        if (!$agent) {
            return response()->json(['status' => false, 'message' => 'Agent profile not found.'], 404);
        }

        $reference = trim((string) $request->reference);

        if ($agent->isLicensePaid()) {
            return response()->json(['status' => true, 'message' => 'License already accredited.', 'redirect' => route('agent.dashboard')]);
        }

        $apiCenter = ApiCenter::first();
        $paystack = PaymentProviderCredentials::paystack($apiCenter);
        $secretKey = $paystack['secret_key'];

        if (!$secretKey) {
            return response()->json(['status' => false, 'message' => 'Paystack secret credentials missing.'], 422);
        }

        $res = Http::timeout(45)
            ->withHeaders(['Authorization' => 'Bearer ' . $secretKey])
            ->get('https://api.paystack.co/transaction/verify/' . urlencode($reference));

        if (!$res->successful()) {
            return response()->json(['status' => false, 'message' => 'Unable to verify payment with Paystack.'], 502);
        }

        $payload = $res->json();
        $data = $payload['data'] ?? null;
        $status = Str::lower((string) ($data['status'] ?? ''));

        if ($status !== 'success') {
            return response()->json(['status' => false, 'message' => 'Payment was not successful.'], 422);
        }

        $paidEmail = Str::lower((string) ($data['customer']['email'] ?? ''));
        if (!$paidEmail || Str::lower((string) $user->email) !== $paidEmail) {
            return response()->json(['status' => false, 'message' => 'Payment email mismatch.'], 422);
        }

        $amountKobo = (int) ($data['amount'] ?? 0);
        $amount = round($amountKobo / 100, 2);

        $effectiveFee = EnrollmentAgent::getEffectiveLicenseFee();
        if ($amount < ($effectiveFee - 1)) {
            return response()->json(['status' => false, 'message' => 'Paid amount does not match required license fee.'], 422);
        }

        DB::transaction(function () use ($agent, $user, $amount, $reference, $data) {
            $agent->update([
                'license_status' => 'paid',
                'license_fee_amount' => $amount,
                'license_fee_paid' => $amount,
                'license_payment_method' => 'paystack',
                'license_payment_reference' => $reference,
                'license_paid_at' => now(),
                'license_rejection_reason' => null,
            ]);

            AgentLicenseTransaction::updateOrCreateSafe(
                ['reference' => $reference],
                [
                    'agent_id' => $agent->id,
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'payment_method' => 'paystack',
                    'gateway_reference' => (string) ($data['id'] ?? $reference),
                    'status' => 'completed',
                    'meta' => $data,
                ]
            );
        });

        return response()->json([
            'status' => true,
            'message' => 'Payment verified! Your NIN Enrollment Station License has been successfully accredited.',
            'redirect' => route('agent.dashboard'),
        ]);
    }

    /**
     * Pay license fee directly from user wallet balance
     */
    public function payWithWallet(Request $request, WalletService $walletService)
    {
        $user = Auth::user();
        $agent = $user?->enrollmentAgent;

        if (!$agent) {
            return response()->json(['status' => false, 'message' => 'Agent profile not found.'], 404);
        }

        if ($agent->isLicensePaid()) {
            return response()->json(['status' => false, 'message' => 'Agent license is already accredited.'], 422);
        }

        $fee = EnrollmentAgent::getEffectiveLicenseFee();
        $reference = 'LIC-WLT-' . $agent->id . '-' . date('YmdHis') . '-' . strtoupper(Str::random(4));

        $debitResult = $walletService->debit(
            $user,
            $fee,
            'Enrollment Station License Fee',
            'LIC',
            $reference
        );

        if (!($debitResult['ok'] ?? false)) {
            $msg = $debitResult['message'] ?? 'Insufficient wallet balance. Please fund your wallet or pay via Paystack.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        DB::transaction(function () use ($agent, $user, $fee, $reference) {
            $agent->update([
                'license_status' => 'paid',
                'license_fee_amount' => $fee,
                'license_fee_paid' => $fee,
                'license_payment_method' => 'wallet',
                'license_payment_reference' => $reference,
                'license_paid_at' => now(),
                'license_rejection_reason' => null,
            ]);

            AgentLicenseTransaction::createSafe([
                'agent_id' => $agent->id,
                'user_id' => $user->id,
                'amount' => $fee,
                'payment_method' => 'wallet',
                'reference' => $reference,
                'status' => 'completed',
                'meta' => [
                    'source' => 'wallet_balance',
                    'is_promo' => EnrollmentAgent::isPromoActive(),
                ],
            ]);
        });

        $msg = 'Success! License fee of ₦' . number_format($fee, 2) . ' deducted from your wallet. Your station license is now officially accredited.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['status' => true, 'message' => $msg, 'redirect' => route('agent.dashboard')]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Upload offline payment proof (bank teller, transfer screenshot)
     */
    public function uploadOfflineProof(Request $request)
    {
        $user = Auth::user();
        $agent = $user?->enrollmentAgent;

        if (!$agent) {
            return back()->with('error', 'Agent profile not found.');
        }

        $request->validate([
            'payment_proof' => ['required', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
            'amount_paid' => ['required', 'numeric', 'min:1'],
            'bank_name' => ['required', 'string', 'max:100'],
            'payment_date' => ['required', 'date'],
            'transaction_reference' => ['required', 'string', 'max:100'],
            'depositor_name' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($agent->license_proof_path) {
            Storage::disk('public')->delete($agent->license_proof_path);
        }

        $proofPath = $request->file('payment_proof')->store('agent_licenses/proofs', 'public');
        \App\Http\Controllers\Agent\AgentMediaController::mirrorToPublicStorage($proofPath);
        $effectiveFee = EnrollmentAgent::getEffectiveLicenseFee();

        $meta = [
            'amount_paid' => (float) $request->amount_paid,
            'bank_name' => $request->bank_name,
            'payment_date' => $request->payment_date,
            'transaction_reference' => $request->transaction_reference,
            'depositor_name' => $request->depositor_name,
            'notes' => $request->notes,
            'submitted_at' => now()->toIso8601String(),
        ];

        DB::transaction(function () use ($agent, $user, $request, $proofPath, $meta, $effectiveFee) {
            $agent->update([
                'license_status' => 'pending_review',
                'license_payment_method' => 'offline_proof',
                'license_fee_amount' => $effectiveFee,
                'license_proof_path' => $proofPath,
                'license_payment_reference' => $request->transaction_reference,
                'license_proof_meta' => $meta,
                'license_rejection_reason' => null,
            ]);

            AgentLicenseTransaction::createSafe([
                'agent_id' => $agent->id,
                'user_id' => $user->id,
                'amount' => (float) $request->amount_paid,
                'payment_method' => 'offline_proof',
                'reference' => 'OFFLINE-' . date('Ymd') . '-' . $agent->id . '-' . strtoupper(Str::random(4)),
                'gateway_reference' => $request->transaction_reference,
                'proof_path' => $proofPath,
                'status' => 'pending',
                'meta' => $meta,
                'notes' => $request->notes,
            ]);
        });

        return back()->with('success', 'Offline payment proof of ₦' . number_format((float)$request->amount_paid, 2) . ' submitted successfully! Admin will verify and accredit your station license within 24 hours.');
    }

    /**
     * Submit claim for legacy pre-website payment
     */
    public function claimLegacy(Request $request)
    {
        $user = Auth::user();
        $agent = $user?->enrollmentAgent;

        if (!$agent) {
            return back()->with('error', 'Agent profile not found.');
        }

        $request->validate([
            'legacy_reference' => ['nullable', 'string', 'max:100'],
            'legacy_payment_date' => ['nullable', 'date'],
            'legacy_notes' => ['required', 'string', 'max:1000'],
            'legacy_proof_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
        ]);

        $proofPath = $agent->license_proof_path;
        if ($request->hasFile('legacy_proof_file')) {
            if ($proofPath) {
                Storage::disk('public')->delete($proofPath);
            }
            $proofPath = $request->file('legacy_proof_file')->store('agent_licenses/proofs', 'public');
            \App\Http\Controllers\Agent\AgentMediaController::mirrorToPublicStorage($proofPath);
        }

        $meta = [
            'claim_type' => 'legacy_pre_platform',
            'legacy_reference' => $request->legacy_reference,
            'legacy_payment_date' => $request->legacy_payment_date,
            'legacy_notes' => $request->legacy_notes,
            'submitted_at' => now()->toIso8601String(),
        ];

        DB::transaction(function () use ($agent, $user, $request, $proofPath, $meta) {
            $agent->update([
                'license_status' => 'pending_review',
                'license_payment_method' => 'legacy_pre_platform',
                'license_payment_reference' => $request->legacy_reference,
                'license_proof_path' => $proofPath,
                'license_proof_meta' => $meta,
                'license_admin_notes' => $request->legacy_notes,
                'license_rejection_reason' => null,
            ]);

            AgentLicenseTransaction::createSafe([
                'agent_id' => $agent->id,
                'user_id' => $user->id,
                'amount' => 0.00,
                'payment_method' => 'legacy_pre_platform',
                'reference' => 'LEGACY-' . date('Ymd') . '-' . $agent->id . '-' . strtoupper(Str::random(4)),
                'gateway_reference' => $request->legacy_reference,
                'proof_path' => $proofPath,
                'status' => 'pending',
                'meta' => $meta,
                'notes' => $request->legacy_notes,
            ]);
        });

        return back()->with('success', 'Your pre-website payment claim has been submitted to the management desk for manual verification.');
    }
}
