<?php

namespace App\Mail;

use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PasswordResetMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public string $emailLogId;
    public User $user;
    public string $resetUrl;
    public int $expireMinutes;

    public function __construct(User $user, string $token)
    {
        $this->onQueue('emails');

        $this->emailLogId = (string) Str::uuid();
        $this->user = $user;

        $email = $user->getEmailForPasswordReset();
        $this->resetUrl = url(route('password.reset', ['token' => $token, 'email' => $email], false));

        $broker = config('auth.defaults.passwords', 'users');
        $this->expireMinutes = (int) (config("auth.passwords.{$broker}.expire") ?? 60);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.password_reset.subject', ['app' => config('app.name')])
        );
    }

    public function content(): Content
    {
        $siteName = (string) (\App\Models\SystemSetting::get('site_name', config('app.name')));
        $title = __('emails.password_reset.title');
        $preheader = __('emails.password_reset.preheader', ['app' => $siteName]);

        return new Content(
            view: 'emails.user.password_reset',
            text: 'emails.user.password_reset_text',
            with: [
                'user' => $this->user,
                'resetUrl' => $this->resetUrl,
                'expireMinutes' => $this->expireMinutes,
                'emailLogId' => $this->emailLogId,
                'title' => $title,
                'preheader' => $preheader,
            ]
        );
    }

    public function build()
    {
        return $this->withSymfonyMessage(function ($message) {
            $message->getHeaders()->addTextHeader('X-Email-Log-Id', $this->emailLogId);
            $message->getHeaders()->addTextHeader('X-Email-Type', 'password_reset');
            $message->getHeaders()->addTextHeader('X-Auto-Response-Suppress', 'All');
        });
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
                'failed_at' => now(),
                'error' => substr($e->getMessage(), 0, 2000),
            ]);
    }
}

