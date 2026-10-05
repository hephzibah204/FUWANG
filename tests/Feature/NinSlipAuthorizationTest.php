<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ApiToken;
use App\Models\FeatureToggle;
use App\Models\User;
use App\Models\VerificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class NinSlipAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function createSampleUser(string $username = 'slip_user_1', string $email = 'slip1@example.com'): User
    {
        $user = User::create([
            'fullname' => 'John Doe',
            'username' => $username,
            'email' => $email,
            'password' => Hash::make('Password@123'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        return $user;
    }

    private function createSampleNinResult(User $user, string $serviceType = 'nin_verification'): VerificationResult
    {
        return VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => $serviceType,
            'identifier' => '12345678901',
            'provider_name' => 'DataVerify',
            'response_data' => [
                'nin' => '12345678901',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'middlename' => 'Emeka',
                'gender' => 'M',
                'dob' => '1990-05-15',
                '_verification_mode' => 'nin',
            ],
            'status' => 'success',
            'reference_id' => 'NIN-TEST-' . strtoupper(uniqid()),
        ]);
    }

    public function test_owner_can_view_slip(): void
    {
        $user = $this->createSampleUser();
        $result = $this->createSampleNinResult($user);

        $response = $this->actingAs($user)
            ->get(route('services.nin.slip', ['id' => $result->id, 'type' => 'premium_slip']));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_admin_can_view_any_user_slip(): void
    {
        $user = $this->createSampleUser();
        $result = $this->createSampleNinResult($user);

        $admin = Admin::create([
            'username' => 'superadmin',
            'email' => 'admin@fuwa.ng',
            'password' => 'AdminPassword@123',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('services.nin.slip', ['id' => $result->id, 'type' => 'premium_slip']));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_temporary_signed_url_allows_guest_to_view_slip(): void
    {
        $user = $this->createSampleUser();
        $result = $this->createSampleNinResult($user);

        $signedUrl = URL::temporarySignedRoute(
            'services.nin.slip',
            now()->addHours(24),
            ['id' => $result->id, 'type' => 'premium_slip']
        );

        $response = $this->get($signedUrl);

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_api_token_query_parameter_allows_owner_to_view_slip(): void
    {
        $user = $this->createSampleUser();
        $user->forceFill(['api_access_status' => 'approved'])->save();
        $result = $this->createSampleNinResult($user);

        $plainToken = 'nx_testtoken1234567890abcdef';
        ApiToken::create([
            'user_id' => $user->id,
            'name' => 'Test Token',
            'token_hash' => hash('sha256', 'testtoken1234567890abcdef'),
            'last_four' => 'cdef',
        ]);

        $response = $this->get(route('services.nin.slip', [
            'id' => $result->id,
            'type' => 'premium_slip',
            'api_token' => $plainToken,
        ]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_bearer_token_header_allows_owner_to_view_slip(): void
    {
        $user = $this->createSampleUser();
        $user->forceFill(['api_access_status' => 'approved'])->save();
        $result = $this->createSampleNinResult($user);

        $plainToken = 'nx_bearersecret987654321';
        ApiToken::create([
            'user_id' => $user->id,
            'name' => 'Bearer Token',
            'token_hash' => hash('sha256', 'bearersecret987654321'),
            'last_four' => '4321',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $plainToken)
            ->get(route('services.nin.slip', [
                'id' => $result->id,
                'type' => 'premium_slip',
            ]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_guest_without_signature_or_token_is_forbidden(): void
    {
        $user = $this->createSampleUser();
        $result = $this->createSampleNinResult($user);

        $response = $this->get(route('services.nin.slip', [
            'id' => $result->id,
            'type' => 'premium_slip',
        ]));

        $response->assertStatus(403);
    }

    public function test_different_user_without_permission_is_forbidden(): void
    {
        $owner = $this->createSampleUser('owner_user', 'owner@example.com');
        $otherUser = $this->createSampleUser('other_user', 'other@example.com');
        $result = $this->createSampleNinResult($owner);

        $response = $this->actingAs($otherUser)
            ->get(route('services.nin.slip', [
                'id' => $result->id,
                'type' => 'premium_slip',
            ]));

        $response->assertStatus(403);
    }

    public function test_disabled_identity_verification_feature_does_not_block_nin_slip(): void
    {
        FeatureToggle::query()->updateOrCreate(
            ['feature_name' => 'identity_verification'],
            ['is_active' => false]
        );

        $user = $this->createSampleUser();
        $result = $this->createSampleNinResult($user);

        $response = $this->actingAs($user)
            ->get(route('services.nin.slip', ['id' => $result->id, 'type' => 'premium_slip']));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_alias_nin_slip_route_works(): void
    {
        $user = $this->createSampleUser();
        $result = $this->createSampleNinResult($user);

        $response = $this->actingAs($user)
            ->get(route('services.nin.slip.alias', ['id' => $result->id, 'type' => 'premium_slip']));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_vuvaa_verify_nin_result_can_render_premium_slip(): void
    {
        $user = $this->createSampleUser();
        $result = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'vuvaa_verify_nin',
            'identifier' => '99887766554',
            'provider_name' => 'VUVAA',
            'response_data' => [
                'data' => [
                    'nin' => '99887766554',
                    'firstname' => 'Chinedu',
                    'lastname' => 'Okafor',
                    'middlename' => 'Paul',
                    'gender' => 'Male',
                    'birthdate' => '1995-10-20',
                    'photo' => base64_encode('fake-image-bytes'),
                ],
            ],
            'status' => 'success',
            'reference_id' => 'VUVAA-TEST-001',
        ]);

        $response = $this->actingAs($user)
            ->get(route('services.nin.slip', ['id' => $result->id, 'type' => 'premium_slip']));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_admin_can_view_verification_report(): void
    {
        $user = $this->createSampleUser();
        $result = $this->createSampleNinResult($user);

        $admin = Admin::create([
            'username' => 'reportadmin',
            'email' => 'reportadmin@fuwa.ng',
            'password' => 'AdminPassword@123',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('services.verification.report', ['id' => $result->id]));

        $response->assertOk();
    }

    public function test_owner_can_view_html_print_preview_of_slip(): void
    {
        $user = $this->createSampleUser();
        $result = $this->createSampleNinResult($user);

        $response = $this->actingAs($user)
            ->get(route('services.nin.slip', ['id' => $result->id, 'type' => 'premium_slip', 'html' => 1, 'print' => 1]));

        $response->assertOk()
            ->assertSee('NIN Premium Slip Print Preview')
            ->assertSee('window.print()', false);
    }
}
