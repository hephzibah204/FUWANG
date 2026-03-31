<?php

namespace App\Services;

use App\Models\User;

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

