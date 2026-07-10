<?php

namespace Tests\Feature;

use App\Mail\WalletTransactionMail;
use App\Models\Transaction;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WalletTransactionEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_credit_queues_email_alert(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        app(WalletService::class)->credit($user, 1000, 'Wallet Funding – Paystack', 'PS-REF-1');

        Mail::assertQueued(WalletTransactionMail::class, function (WalletTransactionMail $mail) use ($user) {
            return $mail->user->id === $user->id
                && $mail->transaction->transaction_id === 'PS-REF-1'
                && $mail->amountDelta === 1000.0;
        });
    }

    public function test_wallet_debit_success_queues_email_alert_on_status_change(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'user2@example.com',
        ]);

        app(WalletService::class)->credit($user, 1000, 'Wallet Funding – Paystack', 'PS-REF-2');

        $debit = app(WalletService::class)->debit($user, 250, 'NIN Verification');
        $this->assertTrue($debit['ok']);

        $txId = (string) $debit['txId'];
        $tx = Transaction::query()->where('transaction_id', $txId)->first();
        $this->assertNotNull($tx);
        $this->assertSame('pending', $tx->status);

        app(WalletService::class)->markTransactionSuccess($txId);

        Mail::assertQueued(WalletTransactionMail::class, function (WalletTransactionMail $mail) use ($user, $txId) {
            return $mail->user->id === $user->id
                && $mail->transaction->transaction_id === $txId
                && $mail->amountDelta === -250.0;
        });
    }
}
