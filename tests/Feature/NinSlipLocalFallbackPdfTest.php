<?php

namespace Tests\Feature;

use App\Models\ApiCenter;
use App\Models\FeatureToggle;
use App\Models\User;
use App\Models\VerificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NinSlipLocalFallbackPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        FeatureToggle::query()->updateOrCreate(
            ['feature_name' => 'identity_verification'],
            ['is_active' => true]
        );
    }

    public function test_falls_back_to_local_pdf_when_dataverify_slip_endpoint_fails(): void
    {
        $user = User::create([
            'fullname' => 'Test User',
            'username' => 'u_localfb_001',
            'email' => 'local-fallback@example.com',
            'password' => Hash::make('Password@123'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        ApiCenter::create([
            'dataverify_api_key' => 'test_api_key',
            'dataverify_endpoint_premium_slip' => 'https://dataverify.com.ng/developers/nin_slips/nin_premium',
        ]);

        $result = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'nin_verification',
            'identifier' => '12345678901',
            'provider_name' => 'DataVerify',
            'response_data' => [
                'nin' => '12345678901',
                'firstname' => 'JOHN',
                'lastname' => 'DOE',
                'middlename' => 'MICHAEL',
                'dob' => '15-05-1985',
                'gender' => 'MALE',
                'phone' => '08012345678',
                'address' => '123 Sample Street, Lagos',
                '_verification_mode' => 'nin',
            ],
            'status' => 'success',
            'reference_id' => 'NIN-LOCALFB-001',
        ]);

        Http::fake(function ($request) {
            if ($request->url() === 'https://dataverify.com.ng/developers/nin_slips/nin_premium') {
                return Http::response(['message' => 'Upstream down'], 500);
            }
            return Http::response(['message' => 'Unexpected request'], 500);
        });

        $resp = $this->actingAs($user)->get(route('services.nin.slip', ['id' => $result->id, 'type' => 'premium_slip']));

        $resp->assertOk()->assertHeader('Content-Type', 'application/pdf');

        $bin = method_exists($resp, 'streamedContent') && $resp->baseResponse instanceof \Symfony\Component\HttpFoundation\StreamedResponse
            ? $resp->streamedContent()
            : (string) $resp->getContent();
        $this->assertIsString($bin);
        $this->assertTrue(str_starts_with($bin, '%PDF'), 'Expected a PDF header from local DomPDF rendering.');
    }
}
