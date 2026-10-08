<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\ApiToken;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DeveloperSandboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_sandbox_requires_login(): void
    {
        $this->get(route('developer.sandbox'))->assertRedirect('/login');
    }

    public function test_user_can_view_sandbox_page(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'api_access_status' => 'approved',
        ]);
        AccountBalance::create(['user_id' => $user->id, 'email' => $user->email, 'user_balance' => 500.0]);

        $response = $this->actingAs($user)->get(route('developer.sandbox'));

        $response->assertOk();
        $response->assertSee('API Sandbox & Key Tester');
        $response->assertSee('Request Builder');
        $response->assertSee('Response Inspector');
    }

    public function test_user_can_validate_valid_api_key(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'api_access_status' => 'approved',
        ]);
        AccountBalance::create(['user_id' => $user->id, 'email' => $user->email, 'user_balance' => 1500.0]);

        $plain = 'my_test_secret_token_1234567890abcdef';
        $hash = hash('sha256', $plain);

        $token = ApiToken::create([
            'user_id' => $user->id,
            'name' => 'Test Sandbox Key',
            'token_hash' => $hash,
            'last_four' => 'cdef',
            'abilities' => ['*'],
            'rate_limit_per_minute' => 60,
        ]);

        $res = $this->actingAs($user)->postJson(route('developer.sandbox.validate_key'), [
            'api_key' => 'nx_' . $plain,
        ]);

        $res->assertOk();
        $res->assertJson([
            'status' => true,
            'valid' => true,
            'token' => [
                'name' => 'Test Sandbox Key',
                'last_four' => 'cdef',
                'is_revoked' => false,
            ],
            'account' => [
                'email' => $user->email,
                'api_access_status' => 'approved',
                'wallet_balance' => 1500.0,
            ],
            'diagnostics' => [
                'auth_status' => 'HEALTHY',
            ],
        ]);
    }

    public function test_validate_key_detects_revoked_token(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'api_access_status' => 'approved',
        ]);

        $plain = 'revoked_token_key_abcdef1234567890';
        $hash = hash('sha256', $plain);

        $token = ApiToken::create([
            'user_id' => $user->id,
            'name' => 'Revoked Token',
            'token_hash' => $hash,
            'last_four' => '7890',
            'revoked_at' => now(),
        ]);

        $res = $this->actingAs($user)->postJson(route('developer.sandbox.validate_key'), [
            'api_key' => 'nx_' . $plain,
        ]);

        $res->assertOk();
        $res->assertJson([
            'status' => true,
            'valid' => false,
            'token' => [
                'is_revoked' => true,
            ],
            'diagnostics' => [
                'auth_status' => 'ISSUE_DETECTED',
            ],
        ]);
    }

    public function test_validate_key_returns_404_for_unknown_key(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $res = $this->actingAs($user)->postJson(route('developer.sandbox.validate_key'), [
            'api_key' => 'nx_completely_fake_and_nonexistent_key_9999',
        ]);

        $res->assertStatus(404);
        $res->assertJson([
            'status' => false,
            'valid' => false,
            'error_code' => 'key_not_found',
        ]);
    }

    public function test_execute_sandbox_simulation_mode(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'api_access_status' => 'approved',
        ]);
        AccountBalance::create(['user_id' => $user->id, 'email' => $user->email, 'user_balance' => 1000.0]);

        $plain = 'simulation_secret_token_abcdef123456';
        $hash = hash('sha256', $plain);

        $token = ApiToken::create([
            'user_id' => $user->id,
            'name' => 'Sim Key',
            'token_hash' => $hash,
            'last_four' => '3456',
        ]);

        // 1. Test /api/v1/me endpoint simulation
        $resMe = $this->actingAs($user)->postJson(route('developer.sandbox.execute'), [
            'api_key' => 'nx_' . $plain,
            'endpoint' => '/api/v1/me',
            'method' => 'GET',
            'simulation_mode' => true,
        ]);

        $resMe->assertOk();
        $resMe->assertJson([
            'status' => true,
            'http_status' => 200,
            'mode' => 'sandbox',
            'response_body' => [
                'status' => true,
                'sandbox' => true,
                'data' => [
                    'email' => $user->email,
                    'api_access_status' => 'approved',
                ],
            ],
        ]);

        // 2. Test /api/v1/verifications/nin endpoint simulation with valid 11-digit payload
        $resNin = $this->actingAs($user)->postJson(route('developer.sandbox.execute'), [
            'api_key' => 'nx_' . $plain,
            'endpoint' => '/api/v1/verifications/nin',
            'method' => 'POST',
            'payload' => [
                'number' => '12345678901',
                'firstname' => 'TestDev',
                'lastname' => 'Agent',
            ],
            'simulation_mode' => true,
        ]);

        $resNin->assertOk();
        $resNin->assertJson([
            'status' => true,
            'http_status' => 200,
            'mode' => 'sandbox',
            'response_body' => [
                'status' => true,
                'sandbox' => true,
                'data' => [
                    'nin' => '12345678901',
                    'status' => 'verified',
                ],
            ],
        ]);

        // 3. Test validation error (non-11 digit NIN)
        $resInvalidNin = $this->actingAs($user)->postJson(route('developer.sandbox.execute'), [
            'api_key' => 'nx_' . $plain,
            'endpoint' => '/api/v1/verifications/nin',
            'method' => 'POST',
            'payload' => [
                'number' => '123',
            ],
            'simulation_mode' => true,
        ]);

        $resInvalidNin->assertOk();
        $resInvalidNin->assertJson([
            'status' => true,
            'http_status' => 422,
            'response_body' => [
                'status' => false,
            ],
        ]);
    }

    public function test_execute_sandbox_rejects_billable_live_execution_if_wallet_is_empty(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'api_access_status' => 'approved',
        ]);
        AccountBalance::create(['user_id' => $user->id, 'email' => $user->email, 'user_balance' => 0.0]); // Empty wallet

        $plain = 'live_test_secret_token_empty_wallet_12';
        $hash = hash('sha256', $plain);

        ApiToken::create([
            'user_id' => $user->id,
            'name' => 'Live Key',
            'token_hash' => $hash,
            'last_four' => 'et12',
        ]);

        $res = $this->actingAs($user)->postJson(route('developer.sandbox.execute'), [
            'api_key' => 'nx_' . $plain,
            'endpoint' => '/api/v1/verifications/nin',
            'method' => 'POST',
            'payload' => ['number' => '12345678901'],
            'simulation_mode' => false, // Live mode
        ]);

        $res->assertOk();
        $res->assertJson([
            'status' => false,
            'http_status' => 402,
            'error' => 'Payment Required',
        ]);
    }
}
