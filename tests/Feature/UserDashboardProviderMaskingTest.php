<?php

namespace Tests\Feature;

use App\Models\CustomApi;
use App\Models\User;
use App\Models\VerificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserDashboardProviderMaskingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_price_list_masks_provider_names_as_network_bands(): void
    {
        CustomApi::create([
            'name' => 'DataVerify Secret Gateway',
            'provider_identifier' => 'dataverify',
            'service_type' => 'nin_verification',
            'status' => true,
            'price' => 250,
            'endpoint' => 'https://api.dataverify.org',
            'priority' => 1,
        ]);

        CustomApi::create([
            'name' => 'RobostTech Secondary Line',
            'provider_identifier' => 'robosttech',
            'service_type' => 'nin_verification',
            'status' => true,
            'price' => 280,
            'endpoint' => 'https://api.robosttech.com',
            'priority' => 2,
        ]);

        $response = $this->get(route('services.price_list'));

        $response->assertOk();
        $response->assertSee('Network Band');
        $response->assertSee('Band 1');
        $response->assertSee('Band 2');
        $response->assertDontSee('DataVerify Secret Gateway');
        $response->assertDontSee('RobostTech Secondary Line');
    }

    #[Test]
    public function test_vtu_providers_api_masks_names_as_bands(): void
    {
        $user = User::create([
            'fullname' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@fuwa.ng',
            'password' => Hash::make('Password@123'),
            'number' => '08012345678',
            'email_verified_at' => now(),
        ]);

        CustomApi::create([
            'name' => 'Vuvaa Power Gateway',
            'provider_identifier' => 'vuvaa',
            'service_type' => 'vtu_electricity',
            'status' => true,
            'price' => 50,
            'endpoint' => 'https://api.vuvaa.com',
            'priority' => 1,
        ]);

        $response = $this->actingAs($user)->getJson(route('services.vtu.providers', ['serviceType' => 'vtu_electricity']));

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Band 1']);
        $this->assertStringNotContainsString('Vuvaa Power Gateway', $response->getContent());
    }

    #[Test]
    public function test_verification_result_masks_provider_names_across_distinct_bands(): void
    {
        $res1 = new VerificationResult(['provider_name' => 'Vuvaa Direct']);
        $this->assertEquals('Band 1', $res1->provider_name);

        $res2 = new VerificationResult(['provider_name' => 'DataVerify Hub']);
        $this->assertEquals('Band 2', $res2->provider_name);

        $res3 = new VerificationResult(['provider_name' => 'RobostTech Global']);
        $this->assertEquals('Band 3', $res3->provider_name);

        $res4 = new VerificationResult(['provider_name' => 'VerifyMe ID Gateway']);
        $this->assertEquals('Band 4', $res4->provider_name);
    }
}
