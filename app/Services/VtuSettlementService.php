<?php

namespace App\Services;

use App\Models\VtuTransaction;
use Illuminate\Support\Facades\DB;

class VtuSettlementService
{
    public function settle(VtuTransaction $transaction, string $state, array $response = [], ?string $providerReference = null): VtuTransaction
    {
        return DB::transaction(function () use ($transaction, $state, $response, $providerReference) {
            $tx = VtuTransaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            // Final states never move backwards, even when callbacks arrive out of order.
            if (in_array($tx->status, ['success', 'failed'], true)) {
                if ($state !== 'pending' && $state !== $tx->status) {
                    \Illuminate\Support\Facades\Log::warning('Conflicting final VTU provider status', ['reference' => $tx->transaction_id, 'stored' => $tx->status, 'received' => $state]);
                }
                return $tx;
            }
            $tx->response_payload = array_merge((array) $tx->response_payload, $response);
            $tx->provider_reference = $providerReference ?: $tx->provider_reference;
            $wallet = app(WalletService::class);
            if ($state === 'success') {
                $data = (array) $tx->response_payload;
                $pin = $data['pin'] ?? $data['pins'] ?? data_get($data, 'data.pin') ?? data_get($data, 'data.pins');
                if (in_array($tx->service_type, ['vtu_epin', 'vtu_data_card', 'vtu_recharge_card'], true) && empty($pin)) {
                    $state = 'pending';
                }
                $token = $data['token'] ?? data_get($data, 'data.token');
                if ($tx->service_type === 'vtu_electricity' && ($tx->request_payload['variation_code'] ?? '') === 'prepaid' && empty($token)) {
                    $state = 'pending';
                }
            }
            if ($state === 'success') {
                if ($tx->direction === 'credit') {
                    $amount = (float) data_get($response, 'data.amount', $response['amount'] ?? 0);
                    if ($amount <= 0 || $amount > $tx->total || !$tx->user) {
                        $state = 'pending';
                    } elseif (!($wallet->credit($tx->user, $amount, 'Airtime to Cash', $tx->transaction_id)['ok'] ?? false)) {
                        $state = 'pending';
                    } else {
                        $tx->total = $amount;
                        $tx->fee = round($tx->amount - $amount, 2);
                    }
                } else {
                    $wallet->markTransactionSuccess($tx->transaction_id);
                }
            } elseif ($state === 'failed' && $tx->direction === 'debit') {
                if (!$tx->user || !$wallet->failAndRefund($tx->user, $tx->total, $tx->service_type, $tx->transaction_id)) {
                    $state = 'pending';
                }
            }
            $tx->status = $state;
            $balanceLow = \App\Support\VtuCustomerMessages::balanceLow((array) $tx->response_payload);
            $tx->error_message = $state === 'pending'
                ? ($balanceLow ? \App\Support\VtuCustomerMessages::LOW_BALANCE : \App\Support\VtuCustomerMessages::PENDING)
                : ($state === 'failed' ? ($balanceLow ? \App\Support\VtuCustomerMessages::LOW_BALANCE : 'Transaction failed.'.($tx->direction === 'debit' ? ' Your wallet has been refunded.' : '')) : null);
            $tx->save();
            return $tx;
        });
    }
}
