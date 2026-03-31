<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $subjectLine, public string $html)
    {
    }

    public function build()
    {
        return $this->subject($this->subjectLine)->view('emails.campaign')->with([
            'html' => $this->html,
        ]);
    }
}

