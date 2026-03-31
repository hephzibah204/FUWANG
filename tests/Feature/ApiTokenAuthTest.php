<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiTokenAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_issue_api_token_with_valid_credentials(): void
    {
        User::create([
            'fullname' => 'Api User',
            'email' => 'api@example.com',
            'password' => Hash::make('Password@123'),
        ]);

        $res = $this->postJson('/api/v1/auth/token', [
            'email' => 'api@example.com',
            'password' => 'Password@123',
            'name' => 'my-app',
        ]);

        $res->assertOk()
            ->assertJson(['status' => true])
            ->assertJsonStructure(['token', 'token_type']);

        $this->assertDatabaseCount('api_tokens', 1);
        $this->assertNotNull(ApiToken::first());
    }

    public function test_protected_api_requires_token(): void
    {
        $this->getJson('/api/v1/me')->assertStatus(401);
    }

    public function test_can_access_me_with_valid_token(): void
    {
        $user = User::create([
            'fullname' => 'Api User',
            'email' => 'api@example.com',
            'password' => Hash::make('Password@123'),
        ]);

        $plain = 'plain-token-123';
        ApiToken::create([
            'user_id' => $user->id,
            'name' => 'test',
            'token_hash' => hash('sha256', $plain),
            'last_four' => substr($plain, -4),
            'abilities' => ['*'],
            'rate_limit_per_minute' => 60,
        ]);

        $this->withHeader('Authorization', 'Bearer nx_' . $plain)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJson(['status' => true])
            ->assertJsonPath('data.email', 'api@example.com');
    }

    public function test_rate_limit_returns_429(): void
    {
        $user = User::create([
            'fullname' => 'Api User',
            'email' => 'api@example.com',
            'password' => Hash::make('Password@123'),
        ]);

        $plain = 'plain-token-rl';
        ApiToken::create([
            'user_id' => $user->id,
            'name' => 'test',
            'token_hash' => hash('sha256', $plain),
            'last_four' => substr($plain, -4),
            'abilities' => ['*'],
            'rate_limit_per_minute' => 3,
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->withHeader('Authorization', 'Bearer nx_' . $plain)
                ->getJson('/api/v1/me')
                ->assertOk();
        }

        $this->withHeader('Authorization', 'Bearer nx_' . $plain)
            ->getJson('/api/v1/me')
            ->assertStatus(429);
    }
}

