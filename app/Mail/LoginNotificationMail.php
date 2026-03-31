<?php

namespace App\Mail;

use App\Models\EmailLog;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email;

class LoginNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [60, 300, 900, 1800];

    public readonly string $emailLogId;
    public readonly string $unsubscribeUrl;

    public function __construct(
        public readonly User $user,
        public readonly string $loginIp,
        public readonly ?string $userAgent,
        public readonly string $loginAtIso
    ) {
        $this->onQueue('emails');
        $this->emailLogId = (string) Str::uuid();

        $this->unsubscribeUrl = URL::temporarySignedRoute(
            'email.unsubscribe',
            now()->addDays(30),
            ['user' => $user->id, 'scope' => 'login']
        );
    }

    public function build()
    {
        $this->withSymfonyMessage(function (Email $message) {
            $siteName = (string) SystemSetting::get('site_name', config('app.name'));
            $replyTo = (string) SystemSetting::get('contact_email', config('mail.from.address'));

            if ($replyTo !== '') {
                $message->replyTo($replyTo);
            }

            $message->getHeaders()->addTextHeader('X-App', $siteName);
            $message->getHeaders()->addTextHeader('X-Email-Log-Id', $this->emailLogId);

            $mailto = $replyTo !== '' ? ('mailto:' . $replyTo) : null;
            $unsub = '<' . $this->unsubscribeUrl . '>';
            $parts = array_values(array_filter([$mailto ? '<' . $mailto . '>' : null, $unsub]));

            if (!empty($parts)) {
                $message->getHeaders()->addTextHeader('List-Unsubscribe', implode(', ', $parts));
                $message->getHeaders()->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
            }
        });

        return $this;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.login.subject', ['app' => (string) SystemSetting::get('site_name', config('app.name'))])
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user.login_notification',
            text: 'emails.user.login_notification_text',
            with: [
                'user' => $this->user,
                'loginIp' => $this->loginIp,
                'userAgent' => $this->userAgent,
                'loginAtIso' => $this->loginAtIso,
                'unsubscribeUrl' => $this->unsubscribeUrl,
                'emailLogId' => $this->emailLogId,
            ]
        );
    }

    public function failed(\Throwable $e): void
    {
        if (!Schema::hasTable('email_logs')) {
            return;
        }

        EmailLog::query()
            ->where('id', $this->emailLogId)
            ->update([
                'status' => 'failed',
                'error' => substr($e->getMessage(), 0, 2000),
                'failed_at' => now(),
            ]);
    }
}

