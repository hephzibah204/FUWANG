<?php

namespace App\Observers;

use App\Mail\WalletTransactionMail;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        $this->maybeNotify($transaction, null);
    }

    public function updated(Transaction $transaction): void
    {
        $originalStatus = (string) $transaction->getOriginal('status');
        if ($originalStatus === (string) $transaction->status) {
            return;
        }

        $this->maybeNotify($transaction, $originalStatus);
    }

    private function maybeNotify(Transaction $transaction, ?string $originalStatus): void
    {
        $status = Str::lower((string) $transaction->status);
        if (!in_array($status, ['success', 'refunded', 'failed', 'cancelled'], true)) {
            return;
        }

        if ($originalStatus !== null) {
            $orig = Str::lower($originalStatus);
            if ($orig !== '' && $orig === $status) {
                return;
            }
        }

        $email = Str::lower(trim((string) $transaction->user_email));
        if ($email === '') {
            return;
        }

        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if (!$user || !$user->email) {
            return;
        }

        $unique = $transaction->transaction_id ?: (string) $transaction->id;
        $cacheKey = 'email:wallet_tx:' . sha1($email . '|' . $unique . '|' . $status);
        if (!Cache::add($cacheKey, true, 86400)) {
            return;
        }

        $delta = round(((float) $transaction->balance_after) - ((float) $transaction->balance_before), 2);

        try {
            Mail::to($user->email)->queue(new WalletTransactionMail($user, $transaction, $delta));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Wallet transaction email queue failed', [
                'user_id' => $user->id,
                'tx_id' => $transaction->transaction_id,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
