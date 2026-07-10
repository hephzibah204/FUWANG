<?php

namespace App\Mail;

use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WalletTransactionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Transaction $transaction,
        public float $amountDelta,
    ) {
        $this->onQueue('emails');
    }

    public function envelope(): Envelope
    {
        $site = (string) SystemSetting::get('site_name', config('app.name'));
        $direction = $this->amountDelta >= 0 ? 'credit' : 'debit';

        return new Envelope(
            subject: $site . ' – Wallet ' . $direction . ' (₦' . number_format(abs($this->amountDelta), 2) . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.wallet-transaction',
            text: 'emails.wallet-transaction-text',
            with: [
                'user' => $this->user,
                'tx' => $this->transaction,
                'amountDelta' => $this->amountDelta,
            ]
        );
    }
}

