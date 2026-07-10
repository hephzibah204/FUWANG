<?php

namespace Tests\Feature\Referrals;

use App\Models\AccountBalance;
use App\Models\Referral;
use App\Models\ReferralTier;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Referrals\ReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralProductionTest extends TestCase
{
    use RefreshDatabase;

    public function test_referral_link_is_full_url(): void
    {
        $user = User::factory()->createOne();
        assert($user instanceof User);

        app(ReferralService::class)->ensureUserReferralCode($user);

        $link = app(ReferralService::class)->referralLink($user);
        $this->assertStringContainsString('/register?ref=', $link);
        $this->assertStringContainsString((string) $user->referral_id, $link);
    }

    public function test_referrer_is_rewarded_on_first_funding_when_enabled(): void
    {
        ReferralTier::query()->create([
            'name' => 'Bronze',
            'minimum_referrals' => 0,
            'commission_rate' => 10,
        ]);

        SystemSetting::set('referral_reward_enabled', 'true', 'referrals');

        $referrer = User::factory()->createOne();
        assert($referrer instanceof User);
        app(ReferralService::class)->ensureUserReferralCode($referrer);

        AccountBalance::query()->create([
            'user_id' => $referrer->id,
            'email' => $referrer->email,
            'user_balance' => 0,
        ]);

        $referred = User::factory()->createOne();
        assert($referred instanceof User);

        app(ReferralService::class)->recordRegistration($referrer, $referred, (string) $referrer->referral_id);

        app(ReferralService::class)->handleFunding($referred, 10000, 'TX-1', 'Wallet Funding - Paystack');

        $ref = Referral::query()->where('referred_user_id', $referred->id)->first();
        $this->assertNotNull($ref);
        $this->assertEquals('rewarded', $ref->status);
        $this->assertEquals('paid', $ref->reward_status);
        $this->assertEquals(1000.0, (float) $ref->reward_amount);

        $balance = AccountBalance::query()->where('user_id', $referrer->id)->first();
        $this->assertNotNull($balance);
        $this->assertEquals(1000.0, (float) $balance->user_balance);
    }
}

