<?php

namespace App\Services\Email;

use App\Mail\LoginNotificationMail;
use App\Mail\WelcomeUserMail;
use App\Models\EmailLog;
use App\Models\EmailPreference;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class EmailNotificationService
{
    public function sendWelcome(User $user): void
    {
        if (!$user->email) {
            return;
        }

        if (!EmailPreference::allowsWelcome($user)) {
            return;
        }

        $mailable = new WelcomeUserMail($user);

        if (Schema::hasTable('email_logs')) {
            EmailLog::query()->create([
                'id' => $mailable->emailLogId,
                'user_id' => $user->id,
                'to_email' => $user->email,
                'type' => 'welcome',
                'subject' => $mailable->envelope()->subject,
                'status' => 'queued',
                'metadata' => [
                    'scope' => 'transactional',
                ],
            ]);
        }

        Mail::to($user->email)->queue($mailable);
    }

    public function sendLoginAlert(User $user, string $loginIp, ?string $userAgent, string $loginAtIso): void
    {
        if (!$user->email) {
            return;
        }

        if (!EmailPreference::allowsLoginAlert($user)) {
            return;
        }

        $throttleKey = 'email:login_alert:' . $user->id . ':' . sha1($loginIp);
        if (Cache::get($throttleKey) === true) {
            return;
        }
        Cache::put($throttleKey, true, 600);

        $mailable = new LoginNotificationMail($user, $loginIp, $userAgent, $loginAtIso);

        if (Schema::hasTable('email_logs')) {
            EmailLog::query()->create([
                'id' => $mailable->emailLogId,
                'user_id' => $user->id,
                'to_email' => $user->email,
                'type' => 'login_alert',
                'subject' => $mailable->envelope()->subject,
                'status' => 'queued',
                'metadata' => [
                    'login_ip' => $loginIp,
                    'user_agent' => $userAgent,
                    'login_at' => $loginAtIso,
                    'scope' => 'security',
                ],
            ]);
        }

        Mail::to($user->email)->queue($mailable);
    }
}
