<?php

namespace App\Services\Payments;

use App\Models\PaymentWebhookEvent;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Detects whether a user has already been credited manually (Admin Credit /
 * Manual Funding) for a payment, so automated crediting paths never pay twice.
 *
 * This mirrors the safeguard in App\Jobs\ProcessPaymentWebhookEvent::findExistingManualCredit()
 * for callers that do not have a webhook event (CLI verification, intent reconciliation):
 *  1. exact reference match in fundings / transactions,
 *  2. manual-credit funding rows for the same user with an amount within
 *     95%–105% (+ NGN 65 tolerance) of the payment amount, created in the
 *     look-back window before the payment time,
 *  3. manual transaction rows whose balance delta falls in the same range.
 * A manual credit already claimed by another webhook event is not reused.
 */
class ManualCreditDetector
{
    public function findExistingManualCredit(User $user, float $amount, ?CarbonInterface $paidAt = null, ?string $reference = null): ?object
    {
        $paidAt = $paidAt ?? now();

        // 1. Direct match by transaction reference in fundings or transactions
        if ($reference !== null && $reference !== '') {
            if (Schema::hasTable('fundings')) {
                $exactFunding = DB::table('fundings')
                    ->where('email', $user->email)
                    ->where(function ($q) use ($reference) {
                        $q->where('reference', $reference)
                          ->orWhere('description', 'like', "%{$reference}%");
                    })
                    ->first();
                if ($exactFunding) {
                    return (object) [
                        'type' => 'funding_exact_ref',
                        'reference' => $exactFunding->reference,
                        'amount' => (float) $exactFunding->amount,
                        'created_at' => $exactFunding->created_at,
                    ];
                }
            }

            if (Schema::hasTable('transactions')) {
                $exactTx = DB::table('transactions')
                    ->where('user_email', $user->email)
                    ->where(function ($q) use ($reference) {
                        $q->where('transaction_id', $reference)
                          ->orWhere('order_type', 'like', "%{$reference}%");
                    })
                    ->first();
                if ($exactTx) {
                    return (object) [
                        'type' => 'transaction_exact_ref',
                        'reference' => $exactTx->transaction_id,
                        'amount' => (float) ($exactTx->balance_after - $exactTx->balance_before),
                        'created_at' => $exactTx->created_at,
                    ];
                }
            }
        }

        // 2. Manual credit references already claimed by other webhook events
        $alreadyClaimedRefs = [];
        if (Schema::hasTable('payment_webhook_events')) {
            $alreadyClaimedRefs = PaymentWebhookEvent::query()
                ->whereNotNull('processing_error')
                ->where('processing_error', 'like', '%Resolved via existing manual credit%')
                ->pluck('processing_error')
                ->all();
        }

        // 3. Match by recent manual credit (Admin Credit / Manual Funding) for same user & matching amount
        $minAmount = max(0.01, round($amount * 0.95, 2));
        $maxAmount = round($amount * 1.05 + 65, 2);
        $startTime = $paidAt->copy()->subHours(72);

        if (Schema::hasTable('fundings')) {
            $fundings = DB::table('fundings')
                ->where('email', $user->email)
                ->where(function ($q) {
                    $q->whereIn('funding_type', ['Admin Credit', 'Manual Funding', 'Manual Credit'])
                      ->orWhere('description', 'like', '%Admin Credit%')
                      ->orWhere('description', 'like', '%Manual%')
                      ->orWhere('description', 'like', '%Reconciliation%');
                })
                ->where('created_at', '>=', $startTime)
                ->whereBetween('amount', [$minAmount, $maxAmount])
                ->orderByDesc('id')
                ->get();

            foreach ($fundings as $f) {
                $isClaimed = false;
                foreach ($alreadyClaimedRefs as $claim) {
                    if (str_contains($claim, (string) $f->reference)) {
                        $isClaimed = true;
                        break;
                    }
                }
                if (!$isClaimed) {
                    return (object) [
                        'type' => 'funding_manual',
                        'reference' => $f->reference,
                        'amount' => (float) $f->amount,
                        'created_at' => $f->created_at,
                    ];
                }
            }
        }

        if (Schema::hasTable('transactions')) {
            $txs = DB::table('transactions')
                ->where('user_email', $user->email)
                ->where(function ($q) {
                    $q->where('order_type', 'like', 'Admin Credit%')
                      ->orWhere('order_type', 'like', 'Manual%')
                      ->orWhere('order_type', 'like', '%Admin Override%');
                })
                ->where('created_at', '>=', $startTime)
                ->orderByDesc('id')
                ->get();

            foreach ($txs as $tx) {
                $txAmt = round((float) ($tx->balance_after - $tx->balance_before), 2);
                if ($txAmt >= $minAmount && $txAmt <= $maxAmount) {
                    $isClaimed = false;
                    foreach ($alreadyClaimedRefs as $claim) {
                        if (str_contains($claim, (string) $tx->transaction_id)) {
                            $isClaimed = true;
                            break;
                        }
                    }
                    if (!$isClaimed) {
                        return (object) [
                            'type' => 'transaction_manual',
                            'reference' => $tx->transaction_id,
                            'amount' => $txAmt,
                            'created_at' => $tx->created_at,
                        ];
                    }
                }
            }
        }

        return null;
    }
}
