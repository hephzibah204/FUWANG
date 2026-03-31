<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\ApiToken;
use App\Models\AccountBalance;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user
        $this->user = User::factory()->create([
            'email' => 'developer@test.com',
            'password' => bcrypt('password123')
        ]);

        // Give them a balance
        AccountBalance::create([
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'user_balance' => 5000,
        ]);

        // Create an API token
        $tokenString = 'test_token_123';
        $this->token = ApiToken::create([
            'user_id' => $this->user->id,
            'name' => 'Test Token',
            'token_hash' => hash('sha256', $tokenString),
            'abilities' => ['*'],
            'rate_limit_per_minute' => 60,
        ]);

        // Set minimum balance requirement
        SystemSetting::updateOrCreate(['key' => 'api_min_wallet_balance'], ['value' => '1000']);
    }

    public function test_api_authentication_success()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer test_token_123',
        ])->getJson('/api/v1/me');

        $response->assertStatus(200);
        $response->assertJsonPath('status', true);
    }

    public function test_api_wallet_insufficient_funds_suspends_key()
    {
        // Reduce balance below threshold
        $this->user->balance->update(['user_balance' => 500]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer test_token_123',
        ])->postJson('/api/v1/vtu/airtime', [
            'network' => 'MTN',
            'amount' => 100,
            'phone' => '08012345678'
        ]);

        // Should return 402 Payment Required
        $response->assertStatus(402);
        $response->assertJson([
            'status' => false,
            'message' => 'fund not sufficient',
            'error' => 'fund not sufficient'
        ]);

        // Token should be revoked
        $this->assertNotNull($this->token->fresh()->revoked_at);
    }

    public function test_vtu_endpoints_validation()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer test_token_123',
        ])->postJson('/api/v1/vtu/airtime', []);

        // Should fail validation (422)
        $response->assertStatus(422);
    }

    public function test_rate_limiting()
    {
        // Set limit to 1
        $this->token->update(['rate_limit_per_minute' => 1]);

        // First request succeeds
        $this->withHeaders([
            'Authorization' => 'Bearer test_token_123',
        ])->getJson('/api/v1/me')->assertStatus(200);

        // Second request fails with 429 Too Many Requests
        $this->withHeaders([
            'Authorization' => 'Bearer test_token_123',
        ])->getJson('/api/v1/me')->assertStatus(429);
    }
}
