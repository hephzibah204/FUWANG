<?php

namespace Tests\Feature\Auth;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $dir = storage_path('app');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents(storage_path('app/installed'), '1');
    }

    public function test_forgot_password_sends_email_for_existing_user_and_returns_generic_message(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'resetme@example.com',
        ]);

        $res = $this->post('/forgot-password', [
            'email' => 'resetme@example.com',
        ]);

        $res->assertRedirect();
        $res->assertSessionHas('status');

        Mail::assertQueued(PasswordResetMail::class, function (PasswordResetMail $mail) use ($user) {
            return $mail->user->is($user);
        });

        $this->assertDatabaseHas('email_logs', [
            'user_id' => $user->id,
            'to_email' => 'resetme@example.com',
            'type' => 'password_reset',
            'status' => 'queued',
        ]);
    }

    public function test_forgot_password_does_not_reveal_user_existence(): void
    {
        Mail::fake();

        $res = $this->post('/forgot-password', [
            'email' => 'unknown@example.com',
        ]);

        $res->assertRedirect();
        $res->assertSessionHas('status');
        Mail::assertNothingQueued();
    }

    public function test_reset_password_updates_password_and_redirects_to_login(): void
    {
        $user = User::factory()->create([
            'email' => 'reset2@example.com',
        ]);

        $token = Password::broker()->createToken($user);

        $res = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'reset2@example.com',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $res->assertRedirect(route('login'));
        $res->assertSessionHas('status');

        $user->refresh();
        $this->assertTrue(Hash::check('new-password-123', $user->password));
    }

    public function test_reset_password_rejects_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'reset3@example.com',
        ]);

        $res = $this->from('/reset-password/bad-token?email=reset3@example.com')->post('/reset-password', [
            'token' => 'bad-token',
            'email' => 'reset3@example.com',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $res->assertRedirect('/reset-password/bad-token?email=reset3@example.com');
        $res->assertSessionHasErrors('email');
    }
}
