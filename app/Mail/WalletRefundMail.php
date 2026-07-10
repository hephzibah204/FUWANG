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

class WalletRefundMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public float $amount,
        public string $reasonSummary,
        public string $referenceId,
    ) {
        $this->onQueue('emails');
    }

    public function envelope(): Envelope
    {
        $site = (string) SystemSetting::get('site_name', config('app.name'));

        return new Envelope(
            subject: $site.' – Wallet refund (₦'.number_format($this->amount, 2).')',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.wallet-refund',
            text: 'emails.wallet-refund-text',
        );
    }
}
