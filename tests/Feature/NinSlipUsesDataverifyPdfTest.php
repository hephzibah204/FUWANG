<?php

namespace Tests\Feature;

use App\Models\ApiCenter;
use App\Models\FeatureToggle;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\VerificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NinSlipUsesDataverifyPdfTest extends TestCase
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

    public function test_premium_slip_uses_dataverify_default_endpoint_when_not_configured(): void
    {
        $user = User::create([
            'fullname' => 'Test User',
            'username' => 'u_premium_001',
            'email' => 'premium-slip@example.com',
            'password' => Hash::make('Password@123'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        ApiCenter::create([
            'dataverify_api_key' => 'test_api_key',
        ]);

        $result = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'nin_verification',
            'identifier' => '12345678901',
            'provider_name' => 'DataVerify',
            'response_data' => [
                'nin' => '12345678901',
                '_verification_mode' => 'nin',
            ],
            'status' => 'success',
            'reference_id' => 'NIN-PREMIUM-001',
        ]);

        Http::fake(function ($request) {
            if ($request->url() === 'https://dataverify.com.ng/developers/nin_slips/nin_premium') {
                $payload = $request->data();
                if (($payload['api_key'] ?? null) !== 'test_api_key' || ($payload['nin'] ?? null) !== '12345678901') {
                    return Http::response(['message' => 'Bad payload'], 422);
                }
                return Http::response('%PDF-1.4 premium', 200, ['Content-Type' => 'application/pdf']);
            }

            return Http::response(['message' => 'Unexpected request'], 500);
        });

        $this->actingAs($user)
            ->get(route('services.nin.slip', ['id' => $result->id, 'type' => 'premium_slip']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_standard_slip_rewrites_old_broken_endpoint_to_working_dataverify_endpoint(): void
    {
        $user = User::create([
            'fullname' => 'Test User',
            'username' => 'u_standard_001',
            'email' => 'standard-slip@example.com',
            'password' => Hash::make('Password@123'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        ApiCenter::create([
            'dataverify_api_key' => 'test_api_key',
            'dataverify_endpoint_standard_slip' => 'https://dataverify.com.ng/developers/standard_slip/',
        ]);

        $result = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'nin_verification',
            'identifier' => '12345678901',
            'provider_name' => 'DataVerify',
            'response_data' => [
                'nin' => '12345678901',
                '_verification_mode' => 'nin',
            ],
            'status' => 'success',
            'reference_id' => 'NIN-STANDARD-001',
        ]);

        Http::fake(function ($request) {
            if ($request->url() === 'https://dataverify.com.ng/developers/nin_slips/nin_standard') {
                $payload = $request->data();
                if (($payload['api_key'] ?? null) !== 'test_api_key' || ($payload['nin'] ?? null) !== '12345678901') {
                    return Http::response(['message' => 'Bad payload'], 422);
                }
                return Http::response('%PDF-1.4 standard', 200, ['Content-Type' => 'application/pdf']);
            }

            return Http::response(['message' => 'Unexpected request'], 500);
        });

        $this->actingAs($user)
            ->get(route('services.nin.slip', ['id' => $result->id, 'type' => 'standard_slip']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_regular_slip_uses_dataverify_default_endpoint_when_not_configured(): void
    {
        $user = User::create([
            'fullname' => 'Test User',
            'username' => 'u_regular_001',
            'email' => 'regular-slip@example.com',
            'password' => Hash::make('Password@123'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        ApiCenter::create([
            'dataverify_api_key' => 'test_api_key',
        ]);

        $result = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'nin_verification',
            'identifier' => '12345678901',
            'provider_name' => 'DataVerify',
            'response_data' => [
                'nin' => '12345678901',
                '_verification_mode' => 'nin',
            ],
            'status' => 'success',
            'reference_id' => 'NIN-REGULAR-001',
        ]);

        Http::fake(function ($request) {
            if ($request->url() === 'https://dataverify.com.ng/developers/nin_slips/nin_regular') {
                $payload = $request->data();
                if (($payload['api_key'] ?? null) !== 'test_api_key' || ($payload['nin'] ?? null) !== '12345678901') {
                    return Http::response(['message' => 'Bad payload'], 422);
                }
                return Http::response('%PDF-1.4 regular', 200, ['Content-Type' => 'application/pdf']);
            }

            return Http::response(['message' => 'Unexpected request'], 500);
        });

        $this->actingAs($user)
            ->get(route('services.nin.slip', ['id' => $result->id, 'type' => 'regular_slip']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_premium_slip_uses_phone_endpoint_for_phone_mode_when_toggle_enabled(): void
    {
        $user = User::create([
            'fullname' => 'Test User',
            'username' => 'u_premph_001',
            'email' => 'premium-slip-phone@example.com',
            'password' => Hash::make('Password@123'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        SystemSetting::set('dataverify_use_phone_slip_for_phone_mode', 'true', 'integrations');

        ApiCenter::create([
            'dataverify_api_key' => 'test_api_key',
        ]);

        $result = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'nin_verification',
            'identifier' => '08012345678',
            'provider_name' => 'DataVerify',
            'response_data' => [
                'phone' => '08012345678',
                '_verification_mode' => 'phone',
            ],
            'status' => 'success',
            'reference_id' => 'NIN-PREMIUM-PHONE-001',
        ]);

        Http::fake(function ($request) {
            if ($request->url() === 'https://dataverify.com.ng/developers/nin_slips/nin_premium_phone') {
                $payload = $request->data();
                if (($payload['api_key'] ?? null) !== 'test_api_key' || ($payload['phone'] ?? null) !== '08012345678') {
                    return Http::response(['message' => 'Bad payload'], 422);
                }
                return Http::response('%PDF-1.4 premium-phone', 200, ['Content-Type' => 'application/pdf']);
            }

            return Http::response(['message' => 'Unexpected request'], 500);
        });

        $this->actingAs($user)
            ->get(route('services.nin.slip', ['id' => $result->id, 'type' => 'premium_slip']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }
}
