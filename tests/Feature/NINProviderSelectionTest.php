<?php

namespace Tests\Feature;

use App\Models\CustomApi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NINProviderSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_nin_controller_passes_supported_modes_to_view()
    {
        $this->withoutMiddleware(\App\Http\Middleware\CheckInstallation::class);

        $user = User::factory()->create();

        CustomApi::create([
            'name' => 'DataVerify Test',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://test.com',
            'price' => 200,
            'priority' => 1,
            'status' => true,
            'supported_modes' => ['nin', 'phone']
        ]);

        CustomApi::create([
            'name' => 'Vuvaa Test',
            'provider_identifier' => 'vuvaa',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://test2.com',
            'price' => 500,
            'priority' => 2,
            'status' => true,
            'supported_modes' => ['selfie', 'share_code']
        ]);

        $response = $this->actingAs($user)->get(route('services.nin'));

        if ($response->status() === 302) {
            dump($response->headers->get('Location'));
        }

        $response->assertStatus(200);
        
        $modes = $response->viewData('providerModes');
        
        $this->assertIsIterable($modes);
        
        // Assert modes were passed correctly
        $dataverify = CustomApi::where('name', 'DataVerify Test')->first();
        $vuvaa = CustomApi::where('name', 'Vuvaa Test')->first();

        $this->assertEquals(['nin', 'phone'], $modes[$dataverify->id]);
        $this->assertEquals(['nin', 'selfie', 'share_code', 'requery'], $modes[$vuvaa->id]);
    }

    public function test_nin_service_type_provider_is_included()
    {
        $this->withoutMiddleware(\App\Http\Middleware\CheckInstallation::class);

        $user = User::factory()->create();

        $legacyProvider = CustomApi::create([
            'name' => 'Legacy NIN Provider',
            'service_type' => 'nin',
            'endpoint' => 'https://test-legacy.com',
            'price' => 150,
            'priority' => 3,
            'status' => true,
            'supported_modes' => null
        ]);

        $response = $this->actingAs($user)->get(route('services.nin'));
        $response->assertStatus(200);
        
        $modes = $response->viewData('providerModes');
        $this->assertArrayHasKey($legacyProvider->id, $modes);
        
        $this->assertEquals(['nin', 'phone', 'demographic', 'tracking', 'vnin'], $modes[$legacyProvider->id]);
    }
}
