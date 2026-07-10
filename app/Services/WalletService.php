<?php

namespace App\Services;

use App\Services\KycService;
use App\Models\AccountBalance;
use App\Models\Transaction;
use App\Models\User;
use App\Support\DbTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Mail\LowBalanceMail;
use App\Mail\WalletRefundMail;
use App\Models\CustomApi;
use App\Models\SystemSetting;

class WalletService
{
    public function debit(User $user, float $amount, string $orderType, ?string $txIdPrefix = null, ?string $transactionId = null): array
    {
        // Enforcement of KYC Tiers for non-admin actions
        if (!str_starts_with($orderType, 'Admin')) {
            $kycCheck = app(KycService::class)->canTransact($user, $amount);
            if (!$kycCheck['allowed']) {
                return [
                    'ok' => false,
                    'message' => $kycCheck['message'] ?? 'Transaction blocked due to Tier limits.',
                    'code' => 'KYC_LIMIT_EXCEEDED'
                ];
            }
        }

        return DB::transaction(function () use ($user, $amount, $orderType, $txIdPrefix, $transactionId) {
            $balance = AccountBalance::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$balance) {
                $balance = AccountBalance::query()
                    ->where('email', $user->email)
                    ->lockForUpdate()
                    ->first();
            }

            if (!$balance) {
                // Check legacy table if new table is empty
                $legacyBalance = 0.00;
                if (Schema::hasTable('account_balance') && DbTable::isBaseTable('account_balance')) {
                    $legacy = DB::table('account_balance')->where('email', $user->email)->lockForUpdate()->first();
                    if ($legacy) {
                        $legacyBalance = (float) $legacy->user_balance;
                    }
                }

                $balance = AccountBalance::create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'user_balance' => $legacyBalance,
                    'api_key' => 'user',
                ]);
                $balance = AccountBalance::query()->where('id', $balance->id)->lockForUpdate()->first();
            } else {
                if (!$balance->user_id) {
                    $balance->user_id = $user->id;
                }
                if (!$balance->email) {
                    $balance->email = $user->email;
                }
            }

            $oldBalance = round((float) $balance->user_balance, 2);
            if ($oldBalance < $amount) {
                return ['ok' => false, 'message' => 'Insufficient balance. Please fund your wallet.'];
            }

            $newBalance = round($oldBalance - $amount, 2);
            $balance->user_balance = $newBalance;
            $balance->save();

            if (Schema::hasTable('account_balance') && DbTable::isBaseTable('account_balance')) {
                $legacy = DB::table('account_balance')->where('email', $user->email)->lockForUpdate()->first();
                if ($legacy) {
                    DB::table('account_balance')->where('email', $user->email)->update([
                        'user_balance' => $newBalance,
                    ]);
                } else {
                    DB::table('account_balance')->insert([
                        'email' => $user->email,
                        'user_balance' => $newBalance,
                        'api_key' => 'user',
                        'created_at' => now(),
                    ]);
                }
            }

            $txId = $transactionId;
            if (!$txId) {
                $prefix = $txIdPrefix ?: strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $orderType), 0, 3));
                $txId = $prefix . '-' . strtoupper(bin2hex(random_bytes(4)));
            }

            $status = str_starts_with($orderType, 'Admin') ? 'success' : 'pending';

            $tx = Transaction::create([
                'user_email' => $user->email,
                'order_type' => $orderType,
                'balance_before' => $oldBalance,
                'balance_after' => $newBalance,
                'transaction_id' => $txId,
                'status' => $status,
            ]);

            // Low Balance Notification
            $this->checkLowBalance($user, $newBalance);

            return [
                'ok' => true,
                'txId' => $txId,
                'oldBalance' => $oldBalance,
                'newBalance' => $newBalance,
                'transaction' => $tx,
            ];
        });
    }

    public function credit(User $user, float $amount, string $orderType, string $transactionId): array
    {
        return DB::transaction(function () use ($user, $amount, $orderType, $transactionId) {
            $balance = AccountBalance::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$balance) {
                $balance = AccountBalance::query()
                    ->where('email', $user->email)
                    ->lockForUpdate()
                    ->first();
            }

            if (!$balance) {
                // Check legacy table if new table is empty
                $legacyBalance = 0.00;
                if (Schema::hasTable('account_balance') && DbTable::isBaseTable('account_balance')) {
                    $legacy = DB::table('account_balance')->where('email', $user->email)->lockForUpdate()->first();
                    if ($legacy) {
                        $legacyBalance = (float) $legacy->user_balance;
                    }
                }

                $balance = AccountBalance::create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'user_balance' => $legacyBalance,
                    'api_key' => 'user',
                ]);
                $balance = AccountBalance::query()->where('id', $balance->id)->lockForUpdate()->first();
            } else {
                if (!$balance->user_id) {
                    $balance->user_id = $user->id;
                }
                if (!$balance->email) {
                    $balance->email = $user->email;
                }
            }

            $oldBalance = round((float) $balance->user_balance, 2);
            $newBalance = round($oldBalance + $amount, 2);
            $balance->user_balance = $newBalance;
            $balance->save();

            if (Schema::hasTable('account_balance') && DbTable::isBaseTable('account_balance')) {
                $legacy = DB::table('account_balance')->where('email', $user->email)->lockForUpdate()->first();
                if ($legacy) {
                    DB::table('account_balance')->where('email', $user->email)->update([
                        'user_balance' => $newBalance,
                    ]);
                } else {
                    DB::table('account_balance')->insert([
                        'email' => $user->email,
                        'user_balance' => $newBalance,
                        'api_key' => 'user',
                        'created_at' => now(),
                    ]);
                }
            }

            Transaction::create([
                'user_email' => $user->email,
                'order_type' => $orderType,
                'balance_before' => $oldBalance,
                'balance_after' => $newBalance,
                'transaction_id' => $transactionId,
                'status' => 'success',
            ]);

            return [
                'ok' => true,
                'oldBalance' => $oldBalance,
                'newBalance' => $newBalance,
            ];
        });
    }

    public function markSuccessById(int $id): void
    {
        $tx = Transaction::query()->where('id', $id)->first();
        if (!$tx) {
            return;
        }
        $tx->status = 'success';
        $tx->save();
    }

    public function markTransactionSuccess(string $transactionId): void
    {
        $tx = Transaction::query()->where('transaction_id', $transactionId)->first();
        if (!$tx) {
            return;
        }
        $tx->status = 'success';
        $tx->save();
    }

    public function failAndRefund(User $user, float $amount, string $orderType, string $transactionId): void
    {
        $creditId = $transactionId . '-RF';
        $creditType = 'Refund – ' . $orderType;

        try {
            DB::transaction(function () use ($user, $amount, $creditType, $creditId, $transactionId) {
                $result = $this->credit($user, $amount, $creditType, $creditId);
                if (!($result['ok'] ?? false)) {
                    throw new \RuntimeException($result['message'] ?? 'Credit failed');
                }
                $tx = Transaction::query()->where('transaction_id', $transactionId)->first();
                if ($tx) {
                    $tx->status = 'refunded';
                    $tx->save();
                }
            });
            $this->notifyWalletRefund($user, $amount, $creditType, $creditId);
        } catch (\Throwable $e) {
            Log::error('failAndRefund failed', [
                'user_id' => $user->id,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Queue email and (when configured) SMS after a wallet refund is applied.
     */
    public function notifyWalletRefund(User $user, float $amount, string $reasonSummary, string $referenceId): void
    {
        try {
            Mail::to($user->email)->queue(new WalletRefundMail($user, $amount, $reasonSummary, $referenceId));
        } catch (\Throwable $e) {
            Log::warning('Wallet refund email queue failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        $phone = trim((string) ($user->number ?? ''));
        if ($phone === '') {
            return;
        }

        try {
            $provider = CustomApi::query()
                ->where('service_type', 'sms_gateway')
                ->where('status', true)
                ->orderByDesc('priority')
                ->first();
            if (!$provider) {
                return;
            }

            $site = (string) SystemSetting::get('site_name', config('app.name'));
            $message = $site . ': NGN ' . number_format($amount, 2) . ' refunded to your wallet. Ref: ' . $referenceId;

            app(SmsService::class)->send($provider, $phone, $message, null);
        } catch (\Throwable $e) {
            Log::warning('Wallet refund SMS failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if user balance is low and notify if necessary.
     */
    protected function checkLowBalance(User $user, float $newBalance): void
    {
        try {
            $threshold = (float) SystemSetting::get('low_balance_threshold', 500);
            
            if ($newBalance < $threshold) {
                $cacheKey = "low_balance_notif_{$user->id}";
                
                // Throttle: Send notification at most once per week
                if (!Cache::has($cacheKey)) {
                    Mail::to($user->email)->queue(new LowBalanceMail($user, $newBalance, $threshold));
                    Cache::put($cacheKey, true, now()->addDays(7));
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to process low balance notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
