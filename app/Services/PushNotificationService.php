<?php

namespace App\Services;

use App\Models\User;

class PushNotificationService
{
    public function sendWelcome(User $user): void
    {
        // TODO: Implement push notification logic
    }

    public function sendLoginAlert(User $user, string $loginIp, ?string $userAgent, string $loginAtIso): void
    {
        // TODO: Implement push notification logic
    }
}
