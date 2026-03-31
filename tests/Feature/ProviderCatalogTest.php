<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\CustomApi;
use App\Models\CustomApiVerificationType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProviderCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_or_unknown_verification_type_is_rejected(): void
    {
        $user = User::create([
            'fullname' => 'U',
            'email' => 'u@example.com',
            'password' => Hash::make('Password@123'),
        ]);

        AccountBalance::create([
            'email' => $user->email,
            'user_id' => $user->id,
            'user_balance' => 1000,
            'api_key' => 'user',
        ]);

        $provider = CustomApi::create([
            'name' => 'CAC',
            'service_type' => 'cac_verification',
            'endpoint' => 'https://example.com/cac',
            'headers' => [],
            'status' => true,
            'price' => 10,
            'timeout_seconds' => 60,
            'retry_count' => 0,
            'retry_delay_ms' => 0,
        ]);

        CustomApiVerificationType::create([
            'custom_api_id' => $provider->id,
            'type_key' => 'standard',
            'label' => 'Standard',
            'price' => 10,
            'status' => false,
            'sort_order' => 0,
        ]);

        $this->actingAs($user)
            ->postJson(route('services.cac_verify.verify'), [
                'rc_number' => '12345',
                'company_type' => 'RC',
                'api_provider_id' => $provider->id,
                'verification_type' => 'standard',
            ])
            ->assertStatus(422);
    }

    public function test_provider_types_endpoint_returns_only_active_types_and_requires_correct_service_type(): void
    {
        $user = User::create([
            'fullname' => 'U',
            'email' => 'u@example.com',
            'password' => Hash::make('Password@123'),
        ]);

        $provider = CustomApi::create([
            'name' => 'P1',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://example.com/nin',
            'headers' => [],
            'status' => true,
            'price' => 200,
            'timeout_seconds' => 60,
            'retry_count' => 0,
            'retry_delay_ms' => 0,
        ]);

        CustomApiVerificationType::create([
            'custom_api_id' => $provider->id,
            'type_key' => 'standard',
            'label' => 'Standard Slip',
            'price' => 120,
            'status' => true,
            'sort_order' => 0,
        ]);

        CustomApiVerificationType::create([
            'custom_api_id' => $provider->id,
            'type_key' => 'disabled',
            'label' => 'Disabled',
            'price' => 999,
            'status' => false,
            'sort_order' => 0,
        ]);

        $this->actingAs($user)
            ->getJson(route('services.providers.types', ['providerId' => $provider->id, 'service_type' => 'nin_verification']))
            ->assertOk()
            ->assertJson(['status' => true])
            ->assertJsonCount(1, 'types')
            ->assertJsonPath('types.0.key', 'standard')
            ->assertJsonPath('types.0.price', 120);

        $this->actingAs($user)
            ->getJson(route('services.providers.types', ['providerId' => $provider->id, 'service_type' => 'bvn_verification']))
            ->assertStatus(404);
    }

    public function test_verification_rejects_provider_from_wrong_service_suite(): void
    {
        $user = User::create([
            'fullname' => 'U',
            'email' => 'u@example.com',
            'password' => Hash::make('Password@123'),
        ]);

        AccountBalance::create([
            'email' => $user->email,
            'user_id' => $user->id,
            'user_balance' => 1000,
            'api_key' => 'user',
        ]);

        $wrongProvider = CustomApi::create([
            'name' => 'Wrong',
            'service_type' => 'bvn_verification',
            'endpoint' => 'https://example.com/nin',
            'headers' => [],
            'status' => true,
            'price' => 1,
            'timeout_seconds' => 60,
            'retry_count' => 0,
            'retry_delay_ms' => 0,
        ]);

        $this->actingAs($user)
            ->postJson(route('services.nin_verify.verify'), [
                'number' => '12345678901',
                'firstname' => 'A',
                'lastname' => 'B',
                'dob' => '1990-01-01',
                'mode' => 'nin',
                'api_provider_id' => $wrongProvider->id,
            ])
            ->assertOk()
            ->assertJson(['status' => false]);
    }

    public function test_invalid_credentials_from_provider_returns_graceful_message(): void
    {
        $user = User::create([
            'fullname' => 'U',
            'email' => 'u@example.com',
            'password' => Hash::make('Password@123'),
        ]);

        AccountBalance::create([
            'email' => $user->email,
            'user_id' => $user->id,
            'user_balance' => 1000,
            'api_key' => 'user',
        ]);

        $provider = CustomApi::create([
            'name' => 'NINP',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://example.com/nin',
            'headers' => [],
            'status' => true,
            'price' => 0,
            'timeout_seconds' => 60,
            'retry_count' => 0,
            'retry_delay_ms' => 0,
        ]);

        Http::fake([
            'https://example.com/*' => Http::response([
                'status' => 'error',
                'message' => 'Invalid API key',
            ], 401),
        ]);

        $this->actingAs($user)
            ->postJson(route('services.nin_verify.verify'), [
                'number' => '12345678901',
                'firstname' => 'A',
                'lastname' => 'B',
                'dob' => '1990-01-01',
                'mode' => 'nin',
                'api_provider_id' => $provider->id,
            ])
            ->assertOk()
            ->assertJson(['status' => false])
            ->assertJsonPath('message', 'Verification Failed: Invalid API key');
    }
}
