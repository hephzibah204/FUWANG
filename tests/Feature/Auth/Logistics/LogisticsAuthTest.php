<?php

namespace Tests\Feature\Auth\Logistics;

use App\Models\User;
use App\Models\LogisticsProfile;
use App\Models\ServiceSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogisticsAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_new_user_can_register_for_logistics_service(): void
    {
        $response = $this->postJson('/api/v1/logistics/auth/register', [
            'fullname' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'transaction_pin' => '1234',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'token',
                'user' => ['id', 'email', 'fullname', 'username'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'fullname' => 'Test User',
        ]);

        $this->assertDatabaseHas('logistics_profiles', [
            'user_id' => User::where('email', 'test@example.com')->first()->id,
        ]);
    }

    public function test_registration_requires_valid_email(): void
    {
        $response = $this->postJson('/api/v1/logistics/auth/register', [
            'fullname' => 'Test User',
            'username' => 'testuser',
            'email' => 'invalid-email',
            'password' => 'Password123!',
            'transaction_pin' => '1234',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.type', 'validation_error')
            ->assertJsonStructure([
                'status',
                'error' => [
                    'type',
                    'message',
                    'reference_id',
                    'details' => ['email'],
                ],
            ]);
    }

    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/v1/logistics/auth/register', [
            'fullname' => 'Test User',
            'username' => 'testuser',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'transaction_pin' => '1234',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.type', 'validation_error')
            ->assertJsonStructure([
                'status',
                'error' => [
                    'type',
                    'message',
                    'reference_id',
                    'details' => ['email'],
                ],
            ]);
    }

    public function test_existing_user_can_login_to_logistics(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!'),
            'user_status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/logistics/auth/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'token',
            ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $response = $this->postJson('/api/v1/logistics/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['status' => 'error']);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!'),
            'user_status' => 'suspended',
        ]);

        $response = $this->postJson('/api/v1/logistics/auth/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(401);
    }

    public function test_service_token_can_be_validated(): void
    {
        $user = User::factory()->create();
        $service = 'logistics';

        $ssoBridge = app(\App\Services\Auth\SSOBridgeService::class);
        $token = $ssoBridge->generateServiceToken($user, $service);

        $response = $this->postJson('/api/v1/logistics/auth/validate', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson(['valid' => true]);
    }

    public function test_expired_token_is_invalid(): void
    {
        $user = User::factory()->create();

        $session = ServiceSession::create([
            'user_id' => $user->id,
            'service' => 'logistics',
            'token' => hash('sha256', 'expired_token'),
            'scopes' => json_encode(['read']),
            'expires_at' => now()->subHour(),
        ]);

        $response = $this->postJson('/api/v1/logistics/auth/validate', [], [
            'Authorization' => 'Bearer expired_token',
        ]);

        $response->assertStatus(401)
            ->assertJson(['valid' => false]);
    }

    public function test_token_can_be_revoked(): void
    {
        $user = User::factory()->create();

        $ssoBridge = app(\App\Services\Auth\SSOBridgeService::class);
        $token = $ssoBridge->generateServiceToken($user, 'logistics');

        $response = $this->postJson('/api/v1/logistics/auth/revoke', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('service_sessions', [
            'user_id' => $user->id,
            'service' => 'logistics',
        ]);
    }

    public function test_login_rate_limiting(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('Password123!'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/logistics/auth/login', [
                'email' => $user->email,
                'password' => 'wrongpassword',
            ]);
        }

        $response = $this->postJson('/api/v1/logistics/auth/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
    }
}
