<?php

namespace App\Services;

use App\Models\User;
use App\Services\Referrals\ReferralService;

class PaidActionService
{
    public function run(User $user, float $amount, string $orderType, string $txIdPrefix, callable $action): array
    {
        $wallet = app(WalletService::class);
        $debit = $wallet->debit($user, $amount, $orderType, $txIdPrefix);
        if (!$debit['ok']) {
            return [
                'ok' => false,
                'message' => $debit['message'] ?? 'Unable to debit wallet.',
            ];
        }

        try {
            $result = $action($debit['txId']);
            $wallet->markTransactionSuccess($debit['txId']);
            
            // Process matrix commission if the action was successful
            try {
                app(ReferralService::class)->processTransaction($user, $amount);
            } catch (\Throwable $re) {
                \Illuminate\Support\Facades\Log::warning('Matrix commission non-fatal failure', ['error' => $re->getMessage()]);
            }

            return [
                'ok' => true,
                'txId' => $debit['txId'],
                'result' => $result,
            ];
        } catch (\Throwable $e) {
            $wallet->failAndRefund($user, $amount, $orderType, $debit['txId']);
            return [
                'ok' => false,
                'txId' => $debit['txId'],
                'message' => $e->getMessage(),
            ];
        }
    }
}
