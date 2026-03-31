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
            'service_type' => 'nin_face_verification',
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
        $this->assertEquals(['selfie', 'share_code'], $modes[$vuvaa->id]);
    }
}
