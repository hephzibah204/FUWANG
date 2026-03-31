<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_json_success_and_redirect(): void
    {
        $user = User::create([
            'fullname' => 'Test User',
            'username' => 'test_user',
            'email' => 'test@example.com',
            'password' => Hash::make('Password@123'),
        ]);

        $response = $this
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->post('/login', [
                'email' => 'test@example.com',
                'password' => 'Password@123',
            ], [
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'redirect' => route('dashboard'),
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_returns_json_error_for_bad_credentials(): void
    {
        User::create([
            'fullname' => 'Test User',
            'username' => 'test_user',
            'email' => 'test@example.com',
            'password' => Hash::make('Password@123'),
        ]);

        $response = $this
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->post('/login', [
                'email' => 'test@example.com',
                'password' => 'WrongPassword',
            ], [
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'error',
        ]);
        $this->assertGuest();
    }
}
