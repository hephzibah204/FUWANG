<?php

namespace App\Mail;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WalletFundedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public float $amount,
        public string $summary,
        public string $referenceId,
        public float $balanceBefore,
        public float $balanceAfter,
    ) {
        $this->onQueue('emails');
    }

    public function envelope(): Envelope
    {
        $site = (string) SystemSetting::get('site_name', config('app.name'));

        return new Envelope(
            subject: $site . ' – Wallet funded (₦' . number_format($this->amount, 2) . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.wallet-funded',
            text: 'emails.wallet-funded-text',
        );
    }
}

