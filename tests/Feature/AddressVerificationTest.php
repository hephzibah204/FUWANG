<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\CustomApi;
use App\Models\AccountBalance;
use App\Models\FeatureToggle;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddressVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $provider;

    protected function setUp(): void
    {
        parent::setUp();
        
        FeatureToggle::create([
            'feature_name' => 'identity_verification',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create(['email' => 'test@example.com']);
        AccountBalance::create(['email' => $this->user->email, 'user_balance' => 5000]);

        $this->provider = CustomApi::create([
            'name' => 'VerifyMe',
            'service_type' => 'address_verification',
            'endpoint' => 'https://vapi.verifyme.ng/v1/verifications/addresses',
            'headers' => ['Authorization' => 'Bearer token'],
            'status' => true
        ]);
    }

    public function test_can_list_verifications()
    {
        Http::fake([
            'https://vapi.verifyme.ng/v1/verifications/addresses*' => Http::response([
                'status' => 'success',
                'data' => [
                    ['id' => 1, 'reference' => 'VMN_1']
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->user)->get(route('services.address_verify.all'));

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
    }

    public function test_can_cancel_verification()
    {
        Http::fake([
            'https://vapi.verifyme.ng/v1/verifications/addresses/1' => Http::response([
                'status' => 'success',
                'data' => 'ok'
            ], 200)
        ]);

        $response = $this->actingAs($this->user)->delete(route('services.address_verify.cancel', 1));

        $response->assertStatus(200);
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('message', 'Verification cancelled successfully.');
    }

    public function test_can_fetch_from_marketplace()
    {
        Http::fake([
            'https://vapi.verifyme.ng/v1/verifications/addresses/marketplace' => Http::response([
                'status' => 'success',
                'data' => [
                    'id' => 1,
                    'street' => 'Test Street',
                    'applicant' => ['photo' => 'url']
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->user)->post(route('services.address_verify.marketplace'), [
            'maxAddressAge' => '6M',
            'lastname' => 'Doe',
            'firstname' => 'John',
            'idNumber' => '123456789',
            'idType' => 'NIN'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.street', 'Test Street');
    }
}
