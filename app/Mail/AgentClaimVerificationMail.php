<?php

namespace App\Mail;

use App\Models\PreApprovedAgent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
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
        return new Envelope(
            subject: 'Verification Code for Pre-Approved Agent Profile Claim — Fuwa.NG Ecosystem',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: "
                <div style='font-family: Arial, sans-serif; background: #0f172a; color: #f8fafc; padding: 30px; border-radius: 12px;'>
                    <h2 style='color: #fbbf24; margin-bottom: 10px;'>Agent Profile Claim Verification</h2>
                    <p style='color: #cbd5e1; font-size: 15px;'>Hello <strong>{$this->agent->full_name}</strong>,</p>
                    <p style='color: #cbd5e1; font-size: 14px;'>You (or someone using your details) requested to claim pre-approved station license <strong>{$this->agent->agent_code}</strong> on the Fuwa.NG Enrollment Platform.</p>
                    
                    <div style='background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.4); padding: 20px; border-radius: 10px; text-align: center; margin: 25px 0;'>
                        <span style='font-size: 12px; color: #fbbf24; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 5px;'>Your 6-Digit Claim Verification OTP</span>
                        <strong style='font-size: 32px; letter-spacing: 6px; color: #ffffff;'>{$this->otp}</strong>
                    </div>

                    <p style='color: #94a3b8; font-size: 13px;'>This code will expire in 15 minutes. If you did not initiate this registration, please ignore this email.</p>
                    <hr style='border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: 25px 0;'>
                    <p style='color: #64748b; font-size: 12px; text-align: center;'>NIMC Accredited Enrollment Partner — Fuwa.NG Ecosystem</p>
                </div>
            ",
        );
    }
}
