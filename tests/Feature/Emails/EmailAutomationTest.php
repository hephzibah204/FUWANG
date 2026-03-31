<?php

namespace Tests\Feature\Emails;

use App\Mail\LoginNotificationMail;
use App\Mail\WelcomeUserMail;
use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_email_is_queued_on_registration_event(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        event(new \Illuminate\Auth\Events\Registered($user));

        Mail::assertQueued(WelcomeUserMail::class, function (WelcomeUserMail $mail) use ($user) {
            return $mail->user->is($user);
        });

        $this->assertDatabaseHas('email_logs', [
            'user_id' => $user->id,
            'type' => 'welcome',
            'status' => 'queued',
        ]);
    }

    public function test_login_email_is_queued_on_login_event(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_USER_AGENT' => 'PHPUnit',
        ]);

        event(new \Illuminate\Auth\Events\Login('web', $user, false));

        Mail::assertQueued(LoginNotificationMail::class, function (LoginNotificationMail $mail) use ($user) {
            return $mail->user->is($user) && $mail->loginIp === '203.0.113.10';
        });

        $this->assertDatabaseHas('email_logs', [
            'user_id' => $user->id,
            'type' => 'login_alert',
            'status' => 'queued',
        ]);
    }
}

