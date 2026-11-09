<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AccountKycIdentityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccountKycPhoneTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_kyc_nin_page_renders_phone_input_field_with_badge_for_saved_phone(): void
    {
        $userWithPhone = User::create([
            'fullname' => 'Test User',
            'username' => 'testuser1',
            'email' => 'test1@fuwa.ng',
            'password' => Hash::make('Password@123'),
            'number' => '08012345678',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($userWithPhone)->get(route('account.kyc.nin'));

        $response->assertOk();
        $response->assertSee('Phone Number');
        $response->assertSee('Saved on profile');
        $response->assertSee('08012345678');
    }

    #[Test]
    public function test_kyc_nin_page_shows_required_notice_when_user_lacks_phone(): void
    {
        $userWithoutPhone = User::create([
            'fullname' => 'No Phone User',
            'username' => 'nophone1',
            'email' => 'nophone1@fuwa.ng',
            'password' => Hash::make('Password@123'),
            'number' => null,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($userWithoutPhone)->get(route('account.kyc.nin'));

        $response->assertOk();
        $response->assertSee('Phone Number');
        $response->assertSee('Required for Tier 2 bank verification');
    }

    #[Test]
    public function test_kyc_nin_submission_requires_phone_when_user_has_no_phone(): void
    {
        $user = User::create([
            'fullname' => 'No Phone User',
            'username' => 'nophone2',
            'email' => 'nophone2@fuwa.ng',
            'password' => Hash::make('Password@123'),
            'number' => null,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('account.kyc.nin.submit'), [
            'nin' => '12345678901',
        ]);

        $response->assertSessionHasErrors(['phone_number']);
        $this->assertNull($user->fresh()->number);
    }

    #[Test]
    public function test_kyc_nin_submission_saves_phone_number_and_calls_kyc_service(): void
    {
        $user = User::create([
            'fullname' => 'Phone Update User',
            'username' => 'phoneuser',
            'email' => 'phoneuser@fuwa.ng',
            'password' => Hash::make('Password@123'),
            'number' => null,
            'email_verified_at' => now(),
        ]);

        $mockSvc = Mockery::mock(AccountKycIdentityService::class);
        $mockSvc->shouldReceive('verifyNinForTier')
            ->once()
            ->withArgs(function ($argUser, $argNin) {
                return $argUser->number === '08012345678' && $argNin === '12345678901';
            })
            ->andReturn([
                'status' => true,
                'message' => 'NIN verified successfully. Tier 2 has been updated.',
                'data' => [],
            ]);

        $this->app->instance(AccountKycIdentityService::class, $mockSvc);

        $response = $this->actingAs($user)->post(route('account.kyc.nin.submit'), [
            'nin' => '12345678901',
            'phone_number' => '08012345678',
        ]);

        $response->assertRedirect(route('account.kyc.index'));
        $this->assertEquals('08012345678', $user->fresh()->number);
    }

    #[Test]
    public function test_kyc_bvn_submission_saves_phone_number_and_calls_kyc_service(): void
    {
        $user = User::create([
            'fullname' => 'Bvn Phone User',
            'username' => 'bvnuser',
            'email' => 'bvnuser@fuwa.ng',
            'password' => Hash::make('Password@123'),
            'number' => null,
            'email_verified_at' => now(),
        ]);

        $mockSvc = Mockery::mock(AccountKycIdentityService::class);
        $mockSvc->shouldReceive('verifyBvnForTier')
            ->once()
            ->withArgs(function ($argUser, $argBvn) {
                return $argUser->number === '08098765432' && $argBvn === '22209291663';
            })
            ->andReturn([
                'status' => true,
                'message' => 'BVN verified successfully. Tier 2 has been updated.',
                'data' => [],
            ]);

        $this->app->instance(AccountKycIdentityService::class, $mockSvc);

        $response = $this->actingAs($user)->post(route('account.kyc.bvn.submit'), [
            'bvn' => '22209291663',
            'phone_number' => '08098765432',
        ]);

        $response->assertRedirect(route('account.kyc.index'));
        $this->assertEquals('08098765432', $user->fresh()->number);
    }
}
