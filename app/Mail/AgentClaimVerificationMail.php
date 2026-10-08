<?php

namespace App\Mail;

use App\Models\PreApprovedAgent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgentClaimVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public PreApprovedAgent $agent;
    public string $otp;

    /**
     * Create a new message instance.
     */
    public function __construct(PreApprovedAgent $agent, string $otp)
    {
        $this->agent = $agent;
        $this->otp = $otp;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address', 'support@fuwa.ng');
        $fromName = config('mail.from.name', 'Fuwa.NG');

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: "Your OTP is {$this->otp} — Verify Agent Profile Claim ({$this->agent->agent_code})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.agent.claim_verification',
            text: 'emails.agent.claim_verification_text',
            with: [
                'agent' => $this->agent,
                'otp' => $this->otp,
            ],
        );
    }
}
