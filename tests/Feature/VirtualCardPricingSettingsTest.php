<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VirtualCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VirtualCardPricingSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithBalance(float $balance): User
    {
        $user = User::create([
            'fullname' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'user_status' => 'active',
        ]);

        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => $balance,
            'api_key' => 'user',
        ]);

        return $user;
    }

    public function test_virtual_card_creation_uses_admin_configured_creation_fee(): void
    {
        SystemSetting::set('virtual_card_creation_fee_ngn', 700, 'pricing');
        SystemSetting::set('virtual_card_fx_rate_usd', 1550, 'pricing');

        $user = $this->createUserWithBalance(100000);

        $res = $this->actingAs($user)->postJson(route('virtual_card.create'), [
            'card_type' => 'usd',
            'initial_load' => 10,
        ]);

        $res->assertOk();
        $res->assertJson(['status' => true]);

        $tx = Transaction::query()->where('user_email', $user->email)
            ->where('order_type', 'Virtual Card Creation (USD)')
            ->latest('id')
            ->first();

        $this->assertNotNull($tx);
        $this->assertSame(100000.00, (float) $tx->balance_before);
        $this->assertSame(83800.00, (float) $tx->balance_after);
    }

    public function test_virtual_card_funding_uses_admin_configured_funding_fee(): void
    {
        SystemSetting::set('virtual_card_funding_fee_ngn', 250, 'pricing');
        SystemSetting::set('virtual_card_fx_rate_usd', 1550, 'pricing');

        $user = $this->createUserWithBalance(50000);

        VirtualCard::create([
            'user_id' => $user->id,
            'card_name' => 'Main USD Card',
            'card_number' => '4111 1111 1111 1111',
            'expiry_date' => '01/30',
            'cvv' => '123',
            'currency' => 'USD',
            'balance' => 0,
            'status' => 'active',
            'reference' => 'VC-REF-001',
            'provider_card_id' => null,
        ]);

        $res = $this->actingAs($user)->postJson(route('virtual_card.fund'), [
            'card_ref' => 'VC-REF-001',
            'amount' => 5,
            'currency' => 'usd',
        ]);

        $res->assertOk();
        $res->assertJson(['status' => true]);

        $tx = Transaction::query()->where('user_email', $user->email)
            ->where('order_type', 'Virtual Card Funding (USD)')
            ->latest('id')
            ->first();

        $this->assertNotNull($tx);
        $this->assertSame(50000.00, (float) $tx->balance_before);
        $this->assertSame(42000.00, (float) $tx->balance_after);
    }
}
