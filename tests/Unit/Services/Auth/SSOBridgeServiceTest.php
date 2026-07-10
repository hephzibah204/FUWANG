<?php

namespace Tests\Unit\Services\Auth;

use App\Models\User;
use App\Models\ServiceSession;
use App\Services\Auth\SSOBridgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SSOBridgeServiceTest extends TestCase
{
    use RefreshDatabase;

    private SSOBridgeService $ssoBridge;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ssoBridge = new SSOBridgeService();
    }

    public function test_generates_valid_service_token(): void
    {
        $user = User::factory()->create();
        $token = $this->ssoBridge->generateServiceToken($user, 'logistics');

        $this->assertNotEmpty($token);
        $this->assertEquals(64, strlen($token));
    }

    public function test_validates_correct_token(): void
    {
        $user = User::factory()->create();
        $token = $this->ssoBridge->generateServiceToken($user, 'logistics', ['read', 'write']);

        $result = $this->ssoBridge->validateServiceToken($token, 'logistics');

        $this->assertNotNull($result);
        $this->assertEquals($user->id, $result['user_id']);
        $this->assertEquals(['read', 'write'], $result['scopes']);
    }

    public function test_rejects_invalid_token(): void
    {
        $result = $this->ssoBridge->validateServiceToken('invalid_token', 'logistics');

        $this->assertNull($result);
    }

    public function test_rejects_expired_token(): void
    {
        $user = User::factory()->create();

        $session = ServiceSession::create([
            'user_id' => $user->id,
            'service' => 'logistics',
            'token' => hash('sha256', 'expired_token'),
            'scopes' => json_encode(['read']),
            'expires_at' => now()->subHour(),
        ]);

        $result = $this->ssoBridge->validateServiceToken('expired_token', 'logistics');

        $this->assertNull($result);
    }

    public function test_authenticate_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!'),
            'user_status' => 'active',
        ]);

        $result = $this->ssoBridge->authenticateWithCredentials(
            'test@example.com',
            'Password123!',
            'logistics'
        );

        $this->assertNotNull($result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals($user->id, $result['user']['id']);
    }

    public function test_authenticate_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $result = $this->ssoBridge->authenticateWithCredentials(
            'test@example.com',
            'WrongPassword!',
            'logistics'
        );

        $this->assertNull($result);
    }

    public function test_authenticate_inactive_user(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!'),
            'user_status' => 'suspended',
        ]);

        $result = $this->ssoBridge->authenticateWithCredentials(
            'test@example.com',
            'Password123!',
            'logistics'
        );

        $this->assertNull($result);
    }

    public function test_authenticate_existing_user(): void
    {
        $user = User::factory()->create(['user_status' => 'active']);

        $result = $this->ssoBridge->authenticateExistingUser($user, 'logistics');

        $this->assertNotNull($result);
        $this->assertArrayHasKey('token', $result);
    }

    public function test_revoke_token(): void
    {
        $user = User::factory()->create();
        $token = $this->ssoBridge->generateServiceToken($user, 'logistics');

        $result = $this->ssoBridge->revokeServiceToken($token, 'logistics');

        $this->assertTrue($result);
        $this->assertNull($this->ssoBridge->validateServiceToken($token, 'logistics'));
    }

    public function test_revoke_all_user_tokens(): void
    {
        $user = User::factory()->create();
        $this->ssoBridge->generateServiceToken($user, 'logistics');
        $this->ssoBridge->generateServiceToken($user, 'logistics');

        $count = $this->ssoBridge->revokeAllUserServiceTokens($user->id, 'logistics');

        $this->assertEquals(2, $count);
    }

    public function test_get_user_by_service_token(): void
    {
        $user = User::factory()->create();
        $token = $this->ssoBridge->generateServiceToken($user, 'logistics');

        $foundUser = $this->ssoBridge->getUserByServiceToken($token, 'logistics');

        $this->assertNotNull($foundUser);
        $this->assertEquals($user->id, $foundUser->id);
    }
}