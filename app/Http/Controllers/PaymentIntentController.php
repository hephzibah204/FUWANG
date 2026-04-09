<?php

namespace App\Http\Controllers;

use App\Models\PaymentIntent;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentIntentController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:50'],
            'service' => ['nullable', 'string', 'max:120'],
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $tier = $user->kyc_tier ?? 1;
        $dailyLimit = config('kyc.tiers.' . $tier . '.daily_limit');

        $todayTransactions = Transaction::where('user_email', $user->email)
            ->whereDate('created_at', today())
            ->sum('amount');

        if ($todayTransactions + $request->amount > $dailyLimit) {
            return response()->json(['status' => false, 'message' => 'Daily transaction limit exceeded.'], 403);
        }

        $reference = 'NXS-' . strtoupper(Str::random(10));

        $intent = PaymentIntent::create([
            'user_id' => $user->id,
            'reference' => $reference,
            'gateway' => null,
            'amount_expected' => (float) $request->amount,
            'currency' => 'NGN',
            'status' => 'pending',
            'metadata' => [
                'service' => (string) ($request->service ?? ''),
            ],
            'expires_at' => now()->addMinutes(30),
        ]);

        return response()->json([
            'status' => true,
            'reference' => $intent->reference,
            'amount_expected' => $intent->amount_expected,
            'currency' => $intent->currency,
        ]);
    }

    public function show(Request $request, string $reference)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $reference = trim($reference);
        if ($reference === '') {
            return response()->json(['status' => false, 'message' => 'Invalid reference'], 422);
        }

        $intent = PaymentIntent::query()
            ->where('reference', $reference)
            ->where('user_id', $user->id)
            ->first();

        if (!$intent) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        return response()->json([
            'status' => true,
            'reference' => $intent->reference,
            'gateway' => $intent->gateway,
            'amount_expected' => $intent->amount_expected,
            'currency' => $intent->currency,
            'intent_status' => $intent->status,
            'expires_at' => $this->expires_at,
        ]);
    }
}
