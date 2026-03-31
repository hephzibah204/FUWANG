<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VirtualCardFxRateSettingsTest extends TestCase
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

    public function test_virtual_card_creation_uses_virtual_card_fx_rate_when_set(): void
    {
        SystemSetting::set('virtual_card_creation_fee_ngn', 500, 'pricing');
        SystemSetting::set('virtual_card_fx_rate_usd', 2000, 'pricing');

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
        // total = creationFee(500) + initial_load(10)*rate(2000) = 20500
        $this->assertSame(100000.00, (float) $tx->balance_before);
        $this->assertSame(79500.00, (float) $tx->balance_after);
    }
}

