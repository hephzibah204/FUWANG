<?php

namespace Tests\Feature;

use App\Models\ApiCenter;
use App\Models\User;
use App\Models\VerificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NinPremiumDemoSlipTest extends TestCase
{
    use RefreshDatabase;

    public function test_premium_demo_slip_posts_demographic_payload_and_returns_pdf(): void
    {
        $user = User::create([
            'fullname' => 'Test User',
            'email' => 'demo-slip@example.com',
            'password' => Hash::make('Password@123'),
        ]);

        ApiCenter::create([
            'dataverify_api_key' => 'test_api_key',
            'dataverify_endpoint_premium_slip' => 'https://dataverify.com.ng/developers/nin_slips/nin_premium_demo.php',
        ]);

        $result = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'nin_verification',
            'identifier' => 'JOHN DOE 15-05-1985',
            'provider_name' => 'DataVerify',
            'response_data' => [
                '_lookup' => [
                    'mode' => 'demographic',
                    'firstname' => 'JOHN',
                    'lastname' => 'DOE',
                    'dob' => '15-05-1985',
                    'gender' => 'male',
                ],
            ],
            'status' => 'success',
            'reference_id' => 'NIN-ABCDEFGH',
        ]);

        Http::fake(function ($request) {
            if ($request->url() === 'https://dataverify.com.ng/developers/nin_slips/nin_premium_demo.php') {
                $payload = $request->data();
                if (
                    ($payload['api_key'] ?? null) !== 'test_api_key' ||
                    ($payload['firstname'] ?? null) !== 'JOHN' ||
                    ($payload['lastname'] ?? null) !== 'DOE' ||
                    ($payload['dob'] ?? null) !== '15-05-1985' ||
                    ($payload['gender'] ?? null) !== 'm'
                ) {
                    return Http::response(['message' => 'Bad payload'], 422);
                }

                return Http::response('%PDF-1.4 demo', 200, ['Content-Type' => 'application/pdf']);
            }

            return Http::response(['message' => 'Unexpected request'], 500);
        });

        $this->actingAs($user)
            ->get(route('services.nin.slip', ['id' => $result->id, 'type' => 'premium_slip']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }
}

