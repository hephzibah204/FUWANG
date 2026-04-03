<?php

namespace App\Services;

use App\Services\KycService;
use App\Models\AccountBalance;
use App\Models\Transaction;
use App\Models\User;
use App\Support\DbTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Mail\LowBalanceMail;
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

            $tx = Transaction::create([
                'user_email' => $user->email,
                'order_type' => $orderType,
                'balance_before' => $oldBalance,
                'balance_after' => $newBalance,
                'transaction_id' => $txId,
                'status' => 'pending',
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
        Transaction::where('id', $id)->update(['status' => 'success']);
    }

    public function markTransactionSuccess(string $transactionId): void
    {
        Transaction::where('transaction_id', $transactionId)->update(['status' => 'success']);
    }

    public function failAndRefund(User $user, float $amount, string $orderType, string $transactionId): void
    {
        DB::transaction(function () use ($user, $amount, $orderType, $transactionId) {
            $balance = AccountBalance::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

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
            }

            if ($balance) {
                $beforeRefund = round((float) $balance->user_balance, 2);
                $afterRefund = round($beforeRefund + $amount, 2);
                $balance->user_balance = $afterRefund;
                $balance->save();

                if (Schema::hasTable('account_balance') && DbTable::isBaseTable('account_balance')) {
                    $legacy = DB::table('account_balance')->where('email', $user->email)->lockForUpdate()->first();
                    if ($legacy) {
                        DB::table('account_balance')->where('email', $user->email)->update([
                            'user_balance' => $afterRefund,
                        ]);
                    } else {
                        DB::table('account_balance')->insert([
                            'email' => $user->email,
                            'user_balance' => $afterRefund,
                            'api_key' => 'user',
                            'created_at' => now(),
                        ]);
                    }
                }

                Transaction::create([
                    'user_email' => $user->email,
                    'order_type' => 'Refund – ' . $orderType,
                    'balance_before' => $beforeRefund,
                    'balance_after' => $afterRefund,
                    'transaction_id' => $transactionId . '-RF',
                    'status' => 'success',
                ]);
            }

            Transaction::where('transaction_id', $transactionId)->update(['status' => 'failed']);
        });
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
