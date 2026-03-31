<?php

namespace Tests\Feature;

use App\Models\ApiCenter;
use App\Models\PaymentGateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PaymentGatewayToggleAndValidationTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        return User::create([
            'fullname' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'user_status' => 'active',
        ]);
    }

    private function seedGateway(string $name, bool $active = true): PaymentGateway
    {
        return PaymentGateway::create([
            'name' => $name,
            'display_name' => ucfirst($name),
            'is_active' => $active,
            'priority' => 1,
            'logo_url' => null,
            'config' => [],
        ]);
    }

    public function test_validate_config_requires_authentication(): void
    {
        $this->seedGateway('paystack', true);

        $res = $this->postJson(route('payment.validate_config'), ['gateway' => 'paystack']);
        $res->assertStatus(302);
    }

    public function test_disabled_gateway_cannot_be_validated(): void
    {
        $user = $this->createUser();
        $this->seedGateway('paystack', false);

        ApiCenter::query()->create([
            'paystack_public_key' => 'pk_test_x',
            'paystack_secret_key' => 'sk_test_x',
        ]);

        $res = $this->actingAs($user)->postJson(route('payment.validate_config'), ['gateway' => 'paystack']);
        $res->assertOk();
        $res->assertJson([
            'status' => false,
            'message' => 'Selected provider is currently disabled',
        ]);
    }

    public function test_active_gateway_with_missing_keys_returns_expected_error(): void
    {
        $user = $this->createUser();
        $this->seedGateway('paystack', true);

        $res = $this->actingAs($user)->postJson(route('payment.validate_config'), ['gateway' => 'paystack']);
        $res->assertOk();
        $res->assertJson([
            'status' => false,
            'message' => 'No API key set for selected provider',
        ]);
    }
}

