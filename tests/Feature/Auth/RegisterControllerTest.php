<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_renders_with_visual_password_requirements(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
        $response->assertSee('passwordRulesBox');
        $response->assertSee('Password Requirements:');
        $response->assertSee('8+ chars');
        $response->assertSee('Upper &amp; lower', false);
        $response->assertSee('1+ number');
        $response->assertSee('1+ symbol');
        $response->assertSee('Phone Number');
        $response->assertSee('id="phone"', false);
    }

    public function test_registration_succeeds_and_creates_user(): void
    {
        Notification::fake();

        $response = $this
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->post('/register', [
                'fullname' => 'John Doe',
                'username' => 'johndoe',
                'email' => 'john.doe@example.com',
                'phone' => '08012345678',
                'password' => 'SecurePass123@!',
                'transaction_pin' => '1234',
            ], [
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'redirect' => route('verification.notice'),
            'email_delivery_failed' => false,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
            'username' => 'johndoe',
            'number' => '08012345678',
        ]);

        $user = User::where('email', 'john.doe@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('08012345678', $user->phone);
        $this->assertEquals('08012345678', $user->number);
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_survives_mail_server_timeout_or_network_failure(): void
    {
        // Simulate mailer throwing an exception during notification send
        Notification::shouldReceive('send')
            ->andThrow(new \Symfony\Component\Mailer\Exception\TransportException('Connection to smtp.resend.com timed out (network error)'));

        $response = $this
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->post('/register', [
                'fullname' => 'Jane Network',
                'username' => 'janenet',
                'email' => 'jane.net@example.com',
                'phone' => '08098765432',
                'password' => 'SecurePass123@!',
                'transaction_pin' => '4321',
            ], [
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        // Must NOT crash with 500 error!
        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'email_delivery_failed' => true,
        ]);
        $response->assertJsonFragment([
            'message' => 'Account created, but verification email could not be delivered immediately. Redirecting...',
        ]);

        // User must be created and authenticated
        $this->assertDatabaseHas('users', [
            'email' => 'jane.net@example.com',
            'username' => 'janenet',
            'number' => '08098765432',
        ]);

        $user = User::where('email', 'jane.net@example.com')->first();
        $this->assertNotNull($user);
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_enforces_password_complexity(): void
    {
        // Simple password missing numbers and symbols
        $response = $this
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->post('/register', [
                'fullname' => 'Invalid User',
                'username' => 'invalid_user',
                'email' => 'invalid@example.com',
                'phone' => '08012345678',
                'password' => 'simplepass',
                'transaction_pin' => '1234',
            ], [
                'Accept' => 'application/json',
            ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('password', $response->json('error.details'));
    }

    public function test_registration_requires_and_validates_phone_number(): void
    {
        // Missing phone
        $response = $this
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->post('/register', [
                'fullname' => 'No Phone',
                'username' => 'nophone',
                'email' => 'nophone@example.com',
                'password' => 'SecurePass123@!',
                'transaction_pin' => '1234',
            ], [
                'Accept' => 'application/json',
            ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('phone', $response->json('error.details'));

        // Invalid phone characters/length
        $responseInvalid = $this
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->post('/register', [
                'fullname' => 'Bad Phone',
                'username' => 'badphone',
                'email' => 'badphone@example.com',
                'phone' => 'abc123',
                'password' => 'SecurePass123@!',
                'transaction_pin' => '1234',
            ], [
                'Accept' => 'application/json',
            ]);

        $responseInvalid->assertStatus(422);
        $this->assertArrayHasKey('phone', $responseInvalid->json('error.details'));
    }

    public function test_registration_normalizes_international_and_nigerian_phone_formats(): void
    {
        Notification::fake();

        // Testing +234 format normalization to 0...
        $response = $this
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->post('/register', [
                'fullname' => 'Normalized User',
                'username' => 'normuser',
                'email' => 'norm@example.com',
                'phone' => '+234 803 123 4567',
                'password' => 'SecurePass123@!',
                'transaction_pin' => '1234',
            ], [
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'norm@example.com',
            'number' => '08031234567',
        ]);
    }
}
