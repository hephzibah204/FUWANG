<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgentBroadcastMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $agentName;
    public string $subjectText;
    public string $bodyText;

    public function __construct(string $agentName, string $subjectText, string $bodyText)
    {
        $this->agentName = $agentName;
        $this->subjectText = $subjectText;
        $this->bodyText = $bodyText;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Fuwa.NG Agency Notice] ' . $this->subjectText,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "
                <div style='font-family: Arial, sans-serif; background: #0f172a; color: #f8fafc; padding: 30px; border-radius: 12px; max-width: 600px; margin: 0 auto;'>
                    <div style='border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; margin-bottom: 20px;'>
                        <span style='background: #2563eb; color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase;'>Official Agency Notice</span>
                        <h2 style='color: #ffffff; margin-top: 10px; margin-bottom: 5px; font-size: 20px;'>{$this->subjectText}</h2>
                    </div>
                    
                    <p style='color: #cbd5e1; font-size: 14px;'>Hello <strong>{$this->agentName}</strong>,</p>
                    
                    <div style='background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); padding: 18px; border-radius: 8px; margin: 20px 0; color: #e2e8f0; font-size: 14px; line-height: 1.6; white-space: pre-line;'>
                        {$this->bodyText}
                    </div>

                    <div style='text-align: center; margin: 25px 0;'>
                        <a href='https://fuwa.ng/agent/dashboard' style='background: #2563eb; color: #ffffff; padding: 10px 24px; text-decoration: none; border-radius: 25px; font-weight: bold; font-size: 13px; display: inline-block;'>Open Agent Dashboard</a>
                    </div>
                    
                    <hr style='border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: 25px 0;'>
                    <p style='color: #64748b; font-size: 12px; text-align: center; margin: 0;'>NIMC Accredited Partner Platform — Fuwa.NG Agency Network</p>
                </div>
            "
        );
    }
}
