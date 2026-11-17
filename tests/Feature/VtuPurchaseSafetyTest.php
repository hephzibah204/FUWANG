<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\CustomApi;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VtuTransaction;
use App\Services\Datasub247Service;
use App\Services\VtuHubService;
use App\Services\WalletService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class VtuPurchaseSafetyTest extends VtuEndpointsTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        SystemSetting::clearLocalCache();
        Cache::flush();
        Mail::fake();
        Http::preventStrayRequests();
        config(['services.datasub247.api_key' => null, 'services.datasub247.username' => null, 'services.datasub247.password' => null]);
        Schema::create('price_list', function (Blueprint $t) {
            $t->id(); $t->string('network'); $t->string('data_plan'); $t->decimal('amount', 18, 2);
            $t->string('validate')->nullable(); $t->string('provider_plan_id')->nullable(); $t->unsignedBigInteger('custom_api_id')->nullable(); $t->timestamps();
        });
    }

    private function customer(): User
    {
        $user = User::create(['email' => 'buyer@example.com', 'username' => 'buyer', 'password' => 'secret']);
        AccountBalance::create(['user_id' => $user->id, 'email' => $user->email, 'user_balance' => 2000, 'api_key' => 'user']);
        $this->actingAs($user);
        return $user;
    }

    private function provider(string $type, array $config = [], ?string $identifier = null): CustomApi
    {
        return CustomApi::create(['name' => 'Test band', 'service_type' => $type, 'endpoint' => 'https://provider.test/purchase',
            'api_key' => 'test-key', 'provider_identifier' => $identifier, 'status' => true, 'priority' => 1, 'config' => $config]);
    }

    private function airtime(string $reference = 'AIR-SAFETY'): array
    {
        return app(VtuHubService::class)->processRequest(['service_type' => 'vtu_airtime', 'request_id' => $reference,
            'payload' => ['network' => 'MTN', 'amount' => 100, 'phone' => '08123456789']]);
    }

    public function test_negative_wallet_amounts_never_change_balance(): void
    {
        $user = $this->customer();
        $this->assertFalse(app(WalletService::class)->debit($user, -500, 'Data PIN Printing')['ok']);
        $this->assertFalse(app(WalletService::class)->credit($user, -500, 'Refund', 'NEG')['ok']);
        $this->assertSame(2000.0, (float) $user->balance->user_balance);
        $this->assertSame(0, Transaction::count());
    }

    public function test_airtime_face_value_is_sent_while_discount_only_affects_charge(): void
    {
        $user = $this->customer(); $this->provider('vtu_airtime');
        Http::fake(['*' => Http::response(['status' => 'success'])]);
        $result = $this->airtime();
        $this->assertTrue($result['status']);
        Http::assertSent(fn ($r) => (float) $r['amount'] === 100.0);
        $this->assertSame(1902.0, (float) $user->balance->user_balance);
    }

    public function test_replaying_a_purchase_never_calls_provider_or_debits_twice(): void
    {
        $user = $this->customer(); $this->provider('vtu_airtime');
        Http::fake(['*' => Http::response(['status' => 'success'])]);
        $this->assertTrue($this->airtime()['status']);
        $this->assertTrue($this->airtime()['status']);
        Http::assertSentCount(1);
        $this->assertSame(1902.0, (float) $user->balance->user_balance);
    }

    public function test_ambiguous_upstream_response_keeps_debit_pending(): void
    {
        $user = $this->customer(); $this->provider('vtu_airtime');
        Http::fake(['*' => Http::response(['status' => 'pending'], 200)]);
        $this->assertTrue($this->airtime()['pending']);
        $this->assertSame('pending', VtuTransaction::first()->status);
        $this->assertSame(1902.0, (float) $user->balance->user_balance);
        $this->assertSame(1, Transaction::count());
    }

    public function test_http_error_never_resends_purchase_or_assumes_refund(): void
    {
        $user = $this->customer(); $provider = $this->provider('vtu_airtime'); $provider->update(['retry_count' => 3]);
        Http::fake(['*' => Http::response([], 500)]);
        $this->assertTrue($this->airtime()['pending']); Http::assertSentCount(1);
        $this->assertSame(1902.0, (float) $user->balance->user_balance);
    }

    public function test_repeated_failure_webhooks_refund_only_once(): void
    {
        $user = $this->customer(); $this->provider('vtu_airtime', [], 'datasub247');
        Http::fake(['*' => Http::response(['status' => 'pending'])]);
        $this->assertTrue($this->airtime()['pending']);
        $adapter = app(Datasub247Service::class);
        $callback = ['request-id' => 'AIR-SAFETY', 'status' => 'failed'];
        $this->assertTrue($adapter->handleWebhook($callback)['status']);
        $this->assertTrue($adapter->handleWebhook($callback)['status']);
        $this->assertSame(2000.0, (float) $user->balance->user_balance);
        $this->assertSame(1, Transaction::where('transaction_id', 'AIR-SAFETY-RF')->count());
        $adapter->handleWebhook(['request-id' => 'AIR-SAFETY', 'status' => 'success']);
        $this->assertSame('failed', VtuTransaction::first()->status);
    }

    public function test_unsigned_webhook_is_rejected(): void
    {
        config(['services.datasub247.webhook_secret' => 'server-secret']);
        $this->postJson('/webhooks/vtu/datasub247', ['request-id' => 'ANY', 'status' => 'failed'])->assertStatus(401);
        $this->assertSame(0, Transaction::count());
    }

    public function test_data_price_and_provider_plan_are_taken_from_catalogue(): void
    {
        $user = $this->customer(); $p = $this->provider('vtu_data');
        $id = DB::table('price_list')->insertGetId(['network' => 'MTN', 'data_plan' => '1 GB', 'amount' => 500, 'provider_plan_id' => '812', 'custom_api_id' => $p->id]);
        Http::fake(['*' => Http::response(['status' => 'success'])]);
        $this->postJson('/services/vtu/data/buy', ['network' => 'MTN', 'plan_id' => (string) $id, 'amount' => 50, 'phone' => '08123456789', 'provider_id' => $p->id, 'request_id' => 'DATA-PRICE'])
            ->assertOk()->assertJson(['status' => true]);
        Http::assertSent(fn ($r) => $r['plan_id'] === '812' && (float) $r['amount'] === 500.0);
        $this->assertSame(1500.0, (float) $user->balance->user_balance);
    }

    public function test_data_network_and_band_must_match_catalogue(): void
    {
        $this->customer(); $p = $this->provider('vtu_data');
        $id = DB::table('price_list')->insertGetId(['network' => 'GLO', 'data_plan' => '1 GB', 'amount' => 500, 'provider_plan_id' => '812', 'custom_api_id' => $p->id]);
        $this->postJson('/services/vtu/data/buy', ['network' => 'MTN', 'plan_id' => (string) $id, 'amount' => 500, 'phone' => '08123456789', 'provider_id' => $p->id])->assertStatus(422);
        Http::assertNothingSent(); $this->assertSame(0, Transaction::count());
    }

    public function test_unknown_data_plan_is_rejected_before_wallet_debit(): void
    {
        $this->customer(); $this->provider('vtu_data');
        $this->postJson('/services/vtu/data/buy', ['network' => 'MTN', 'plan_id' => '999', 'amount' => 500, 'phone' => '08123456789'])->assertStatus(422);
        $this->assertSame(0, Transaction::count()); Http::assertNothingSent();
    }

    public function test_quote_does_not_debit_or_send_purchase(): void
    {
        $this->customer(); $this->provider('vtu_airtime');
        $this->postJson('/services/vtu/quote/vtu_airtime', ['network' => 'MTN', 'amount' => 100, 'phone' => '08123456789'])
            ->assertOk()->assertJsonPath('breakdown.total', 98);
        Http::assertNothingSent(); $this->assertSame(0, Transaction::count());
    }

    public function test_order_status_is_only_visible_to_owner(): void
    {
        $user = $this->customer(); $this->provider('vtu_airtime'); Http::fake(['*' => Http::response(['status' => 'success'])]); $this->airtime();
        $other = User::create(['email' => 'other@example.com', 'password' => 'secret']);
        $this->actingAs($other)->getJson('/services/vtu/recharge-printing/query?order_id=AIR-SAFETY')->assertNotFound();
        $this->actingAs($user)->getJson('/services/vtu/recharge-printing/query?order_id=AIR-SAFETY')->assertOk()->assertJson(['status' => true]);
    }

    public function test_invalid_explicit_band_never_falls_back(): void
    {
        $this->customer(); $this->provider('vtu_airtime');
        $result = app(VtuHubService::class)->processRequest(['service_type' => 'vtu_airtime', 'provider_id' => 999,
            'payload' => ['network' => 'MTN', 'amount' => 100, 'phone' => '08123456789']]);
        $this->assertFalse($result['status']); $this->assertSame(0, Transaction::count()); Http::assertNothingSent();
    }

    public function test_disabled_datasub_band_cannot_be_used_as_fallback(): void
    {
        $this->customer(); $p = $this->provider('vtu_airtime', [], 'datasub247'); $p->update(['status' => false]);
        $this->assertNull(app(Datasub247Service::class)->getApiKey());
        $this->assertFalse($this->airtime()['status']); $this->assertSame(0, Transaction::count());
    }

    public function test_printing_rejects_unconfigured_plan_even_with_spoofed_price(): void
    {
        $this->customer(); $this->provider('vtu_data_card', [], 'datasub247');
        $this->postJson('/services/vtu/recharge-printing/generate', ['pin_type' => 'data', 'network' => 'MTN', 'plan_type' => '999', 'amount' => 100, 'data_plan_price' => -500, 'quantity' => 1])
            ->assertStatus(422);
        Http::assertNothingSent(); $this->assertSame(0, Transaction::count());
    }

    public function test_generic_validation_does_not_accept_provider_failure_http_200(): void
    {
        $this->customer(); $this->provider('vtu_cable_tv'); Http::fake(['*' => Http::response(['status' => 'failed'])]);
        $this->postJson('/services/vtu/cable/validate', ['serviceID' => 'gotv', 'smart_card_number' => '12345'])->assertStatus(422);
    }

    public function test_prepaid_payment_without_meter_verification_is_rejected(): void
    {
        $this->customer(); $this->provider('vtu_electricity');
        $this->postJson('/services/vtu/electricity/buy', ['serviceID' => 'eko-electric', 'variation_code' => 'prepaid', 'meter_number' => '1234', 'phone' => '08123456789', 'amount' => 500])->assertStatus(422);
        $this->assertSame(0, Transaction::count()); Http::assertNothingSent();
    }

    public function test_purchase_templates_render_real_forms_for_all_services(): void
    {
        $this->customer();
        app('view')->addLocation(base_path('tests/Fixtures/views'));
        app('view')->share('errors', new \Illuminate\Support\ViewErrorBag());
        $template = str_replace("@extends('layouts.nexus')", "@extends('vtu-test-layout')", file_get_contents(resource_path('views/services/vtu/purchase.blade.php')));
        $pages = [
            'vtu_data' => 'services.vtu.data.buy', 'vtu_airtime' => 'services.vtu.airtime.buy',
            'vtu_cable_tv' => 'services.vtu.cable.buy', 'vtu_electricity' => 'services.vtu.electricity.buy',
            'vtu_epin' => 'services.vtu.epin.buy', 'vtu_internet' => 'services.vtu.internet.buy',
            'vtu_betting' => 'services.vtu.betting.fund', 'vtu_airtime_to_cash' => 'services.vtu.airtime_to_cash.submit',
            'vtu_bulksms' => 'services.vtu.bulksms.send', 'vtu_data_card' => 'services.vtu.recharge_printing.generate',
            'vtu_recharge_card' => 'services.vtu.recharge_printing.generate',
        ];
        $directory = storage_path('framework/testing/vtu-ui');
        if (!is_dir($directory)) { mkdir($directory, 0777, true); }
        foreach ($pages as $type => $route) {
            $html = \Illuminate\Support\Facades\Blade::render($template, ['title' => 'Test purchase', 'type' => $type, 'purchaseRoute' => $route]);
            $this->assertStringContainsString('data-purchase-form', $html);
            $this->assertStringContainsString('data-quote=', $html);
            $this->assertStringContainsString('vtu-purchase.js', $html);
            file_put_contents($directory.'/'.$type.'.html', $html);
        }
    }
}
