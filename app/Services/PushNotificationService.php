<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    public function sendWelcome(User $user): void
    {
        Log::info('Welcome notification triggered', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        try {
            activity('user_notification')
                ->performedOn($user)
                ->causedBy($user)
                ->withProperties(['type' => 'welcome'])
                ->log('Welcome to ' . config('app.name') . '! Your account has been registered successfully.');
        } catch (\Throwable $e) {
            Log::warning('Failed to record welcome notification activity: ' . $e->getMessage());
        }
    }

    public function sendLoginAlert(User $user, string $loginIp, ?string $userAgent, string $loginAtIso): void
    {
        Log::info('Login alert triggered', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $loginIp,
            'timestamp' => $loginAtIso,
        ]);

        try {
            activity('security_alert')
                ->performedOn($user)
                ->causedBy($user)
                ->withProperties([
                    'type' => 'login_alert',
                    'ip' => $loginIp,
                    'user_agent' => $userAgent,
                    'login_at' => $loginAtIso,
                ])
                ->log('New login detected on your account from IP ' . $loginIp);
        } catch (\Throwable $e) {
            Log::warning('Failed to record login alert activity: ' . $e->getMessage());
        }
    }
}
