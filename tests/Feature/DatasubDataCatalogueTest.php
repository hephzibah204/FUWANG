<?php

namespace Tests\Feature;

use App\Models\CustomApi;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\AccountBalance;
use App\Models\SystemSetting;
use App\Models\VtuTransaction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DatasubDataCatalogueTest extends VtuPurchaseSafetyTest
{
    private function documentedCustomer(): User
    {
        $user = User::create(['email' => 'docs@example.com', 'username' => 'docs', 'password' => 'secret']);
        AccountBalance::create(['user_id' => $user->id, 'email' => $user->email, 'user_balance' => 10000]);
        $this->actingAs($user);
        $this->mock(\App\Services\KycService::class, function ($mock) {
            $mock->shouldReceive('canTransact')->andReturn(['allowed' => true]);
        });
        config(['services.datasub247.api_key' => 'configured-test-key']);
        return $user;
    }

    private function documentedSettings(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id(); $table->string('key')->unique(); $table->text('value')->nullable();
            $table->string('group')->nullable(); $table->timestamps();
        });
        SystemSetting::clearLocalCache();
    }

    public function test_documented_cable_catalogue_preserves_existing_prices_on_repeated_load(): void
    {
        $this->documentedSettings();
        config(['services.datasub247.api_key' => 'configured-test-key']);
        SystemSetting::query()->create(['key' => 'vtu_neco_price', 'value' => '2000']);
        $migration = require base_path('database/migrations/2026_11_17_121000_load_documented_datasub_cable_and_exam_prices.php');
        $migration->up();
        $products = app(\App\Services\VtuCatalogService::class)->products('vtu_cable_tv');
        $this->assertCount(5, $products);
        $this->assertSame('dstv', $products[0]['service_id']);
        $products[0]['amount'] = 4999;
        DB::table('system_settings')->where('key', 'vtu_product_catalog')->update(['value' => json_encode($products)]);
        $migration->up();
        $this->assertCount(5, app(\App\Services\VtuCatalogService::class)->products('vtu_cable_tv'));
        $this->assertEquals(4999, app(\App\Services\VtuCatalogService::class)->products('vtu_cable_tv')[0]['amount']);
        $this->assertEquals(2000, SystemSetting::get('vtu_neco_price'));
        $this->assertEquals(4000, SystemSetting::get('vtu_waec_price'));
    }

    public function test_documented_cable_purchase_verifies_customer_and_uses_real_plan_price(): void
    {
        $user = $this->documentedCustomer();
        $this->documentedSettings();
        (require base_path('database/migrations/2026_11_17_121000_load_documented_datasub_cable_and_exam_prices.php'))->up();
        $provider = CustomApi::where('service_type', 'vtu_cable_tv')->first();
        Http::fake(['*/api/cable/cable-validation*' => Http::response(['status' => 'success', 'name' => 'ADEX DEVELOPER']),
            '*/api/cable' => Http::response(['status' => 'success'])]);
        $verification = ['serviceID' => 'dstv', 'smart_card_number' => '0701339708866', 'provider_id' => $provider->id];
        $token = $this->postJson(route('services.vtu.cable.validate'), $verification)->assertOk()->json('validation_token');
        $this->assertNotEmpty($token);
        $purchase = $verification + ['variation_code' => 'datasub-dstv-1', 'amount' => 100, 'phone' => '07032295411',
            'request_id' => 'DOC-CABLE', 'validation_token' => $token];
        $response = $this->postJson(route('services.vtu.cable.buy'), $purchase);
        $this->assertSame(200, $response->status(), $response->getContent());
        $response->assertJson(['status' => true]);
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/api/cable') && $r['cable'] === 2
            && $r['cable_plan'] === 1 && $r['iuc'] === '0701339708866' && $r['bypass'] === false);
        $this->assertSame(5600.0, (float) $user->balance->user_balance);
    }

    public function test_documented_electricity_ids_are_used_for_both_lookup_and_purchase(): void
    {
        config(['services.datasub247.api_key' => 'configured-test-key']);
        Http::fake(['*' => Http::response(['status' => 'success', 'name' => 'ADEX DEVELOPER', 'token' => '1234-5678'])]);
        $adapter = app(\App\Services\Datasub247Service::class);
        foreach (['ikeja-electric' => 1, 'eko-electric' => 2, 'kano-electric' => 3, 'portharcourt-electric' => 4, 'jos-electric' => 5] as $code => $id) {
            $this->assertTrue($adapter->validateMeter($code, '07032295411', 'postpaid')['status']);
            $this->assertTrue($adapter->purchaseElectricity($code, '07032295411', 'prepaid', 500, 'BILL-'.$id)['status']);
            Http::assertSent(fn ($r) => str_contains($r->url(), '/api/bill/bill-validation?') && (int) $r['disco'] === $id && $r['meter_type'] === 'postpaid');
            Http::assertSent(fn ($r) => str_ends_with($r->url(), '/api/bill') && $r['disco'] === $id
                && $r['meter_number'] === '07032295411' && $r['amount'] === 500 && $r['bypass'] === false);
        }
    }

    public function test_documented_airtime_route_sends_face_value_and_discounts_only_once(): void
    {
        $user = $this->documentedCustomer();
        Http::fake(['*' => Http::response(['status' => 'success'])]);
        $this->postJson(route('services.vtu.airtime.buy'), ['network' => 'AIRTEL', 'phone' => '07032295411',
            'amount' => 100, 'request_id' => 'DOC-AIR'])->assertOk()->assertJson(['status' => true]);
        Http::assertSent(fn ($r) => $r['network'] === 2 && $r['amount'] === 100);
        $this->assertSame(9902.0, (float) $user->balance->user_balance);
    }

    public function test_documented_fail_callback_refunds_once_and_rejects_unsigned_callbacks(): void
    {
        $user = $this->documentedCustomer();
        config(['services.datasub247.webhook_secret' => 'callback-test-secret']);
        Http::fake(['*' => Http::response(['status' => 'pending'])]);
        $this->postJson(route('services.vtu.airtime.buy'), ['network' => 'MTN', 'phone' => '07032295411',
            'amount' => 100, 'request_id' => 'DOC-CALLBACK'])->assertStatus(202);
        $callback = ['request-id' => 'DOC-CALLBACK', 'status' => 'fail', 'response' => 'Rejected'];
        $url = route('webhooks.vtu.datasub247');
        $this->postJson($url, $callback)->assertUnauthorized();
        $this->postJson($url.'?token=callback-test-secret', $callback)->assertOk();
        $this->postJson($url.'?token=callback-test-secret', $callback)->assertOk();
        $this->assertSame(10000.0, (float) $user->balance->user_balance);
        $this->assertSame('failed', VtuTransaction::first()->status);
        $this->assertArrayNotHasKey('token', VtuTransaction::first()->response_payload);
        $this->assertSame(1, \App\Models\Transaction::where('transaction_id', 'DOC-CALLBACK-RF')->count());
    }

    public function test_documented_exam_sms_and_printing_payloads_preserve_recipients_and_quantity(): void
    {
        config(['services.datasub247.api_key' => 'configured-test-key']);
        Http::fake(['*' => Http::response(['status' => 'success', 'pin' => 'dummy-pin', 'serial' => 'dummy-serial'])]);
        $adapter = app(\App\Services\Datasub247Service::class);
        foreach (['waec' => 1, 'neco' => 2, 'nabteb' => 3] as $exam => $id) {
            $this->assertTrue($adapter->purchaseExamPin($exam, 2, 'EXAM-'.$id)['status']);
            Http::assertSent(fn ($r) => str_ends_with($r->url(), '/api/exam') && $r['exam'] === $id && $r['quantity'] === 2);
        }
        $this->assertTrue($adapter->sendBulkSms('FUWA', ['07032295411', '07013397088'], 'Hello', 'DOC-SMS')['status']);
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/api/bulksms') && $r['number'] === '07032295411,07013397088'
            && $r['sender'] === 'FUWA' && $r['message'] === 'Hello');
        $this->assertTrue($adapter->purchaseDataCard('GLO', 1, 2, 'fuwang', 'DOC-DC')['status']);
        $this->assertTrue($adapter->purchaseRechargeCard('AIRTEL', 1, 2, 'fuwang', 'DOC-RC')['status']);
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/api/data_card') && $r['network'] === 3 && $r['quantity'] === 2 && $r['card_name'] === 'fuwang');
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/api/recharge_card') && $r['network'] === 2 && $r['quantity'] === 2 && $r['card_name'] === 'fuwang');
        Http::assertSentCount(6);
    }

    public function test_documented_plans_load_without_overwriting_admin_prices(): void
    {
        config(['services.datasub247.api_key' => 'configured-test-key']);
        $migration = require base_path('database/migrations/2026_11_17_120000_load_documented_datasub_data_plans.php');
        $migration->up();
        $plans = app(\App\Services\VtuCatalogService::class)->dataPlans();
        $this->assertCount(5, $plans);
        $this->assertEqualsCanonicalizing(['1', '2', '3', '4', '6'], $plans->pluck('provider_plan_id')->all());
        $this->assertEquals(410, $plans->firstWhere('provider_plan_id', '2')->amount);
        $this->assertSame('7 DAYS', $plans->firstWhere('provider_plan_id', '3')->validate);
        $this->assertSame('30 DAYS', $plans->firstWhere('provider_plan_id', '4')->validate);
        DB::table('price_list')->where('id', $plans->firstWhere('provider_plan_id', '2')->id)->update(['amount' => 499.50]);
        $migration->up();
        $this->assertSame(5, DB::table('price_list')->count());
        $this->assertEquals(499.50, app(\App\Services\VtuCatalogService::class)->dataPlans()->firstWhere('provider_plan_id', '2')->amount);
        Http::assertNothingSent();
    }

    public function test_documented_airtime_and_data_payloads_use_correct_networks_and_fields(): void
    {
        config(['services.datasub247.api_key' => 'configured-test-key']);
        Http::fake(['*' => Http::response(['status' => 'success'])]);
        $adapter = app(\App\Services\Datasub247Service::class);
        foreach (['MTN' => 1, 'GLO' => 3, 'AIRTEL' => 2, '9MOBILE' => 4] as $network => $id) {
            $this->assertTrue($adapter->purchaseAirtime($network, '07032295411', 100, 'VTU', 'AIR-'.$network)['status']);
            $this->assertTrue($adapter->purchaseData($network, '07032295411', 2, 'DATA-'.$network)['status']);
            Http::assertSent(fn ($r) => str_ends_with($r->url(), '/api/topup') && $r['network'] === $id
                && $r['phone'] === '07032295411' && $r['plan_type'] === 'VTU' && $r['amount'] === 100
                && $r['bypass'] === false && $r['request-id'] === 'AIR-'.$network);
            Http::assertSent(fn ($r) => str_ends_with($r->url(), '/api/data') && $r['network'] === $id
                && $r['data_plan'] === 2 && $r['phone'] === '07032295411' && $r['bypass'] === false
                && $r['request-id'] === 'DATA-'.$network);
        }
        Http::assertSentCount(8);
    }

    public function test_documented_checkout_never_treats_an_unmapped_local_id_as_provider_id(): void
    {
        $user = User::create(['email' => 'documented@example.com', 'password' => 'secret']);
        $this->actingAs($user);
        config(['services.datasub247.api_key' => 'configured-test-key']);
        $migration = require base_path('database/migrations/2026_11_17_120000_load_documented_datasub_data_plans.php');
        $migration->up();
        $id = DB::table('price_list')->insertGetId(['network' => 'MTN', 'data_plan' => 'Unmapped old price', 'amount' => 100]);
        $this->postJson(route('services.vtu.quote', ['serviceType' => 'vtu_data']), [
            'network' => 'MTN', 'plan_id' => (string) $id, 'phone' => '07032295411', 'amount' => 100,
        ])->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_catalogue_setup_import_exposes_real_plans_and_updates_prices(): void
    {
        $this->actingAs(User::create(['email' => 'catalogue@example.com', 'password' => 'secret']));
        config(['services.datasub247.api_key' => 'datasub-global']);
        $plans = [['network' => 'mtn', 'data_plan' => 'Configured MTN 1 GB', 'provider_plan_id' => '812', 'amount' => '550.25', 'validate' => '30 Days']];
        $this->postJson(route('admin.vtu.pricing.data-catalogue'), ['data_catalogue' => json_encode($plans)])
            ->assertOk()->assertJson(['status' => true, 'count' => 1]);
        $id = DB::table('price_list')->value('id');
        $this->getJson(route('services.vtu.catalog', ['serviceType' => 'vtu_data']))->assertOk()
            ->assertJsonPath('products.0.code', (string) $id)->assertJsonPath('products.0.amount', 550.25);
        $this->postJson(route('services.vtu.quote', ['serviceType' => 'vtu_data']),
            ['network' => 'MTN', 'plan_id' => (string) $id, 'phone' => '08123456789', 'amount' => 50])
            ->assertOk()->assertJsonPath('breakdown.amount', 550.25);
        Http::assertNothingSent();
        $plans[0]['amount'] = '575.75';
        $this->postJson(route('admin.vtu.pricing.data-catalogue'), ['data_catalogue' => json_encode($plans)])->assertOk();
        $this->assertSame(1, DB::table('price_list')->count());
        $this->assertEquals(575.75, DB::table('price_list')->value('amount'));
    }

    public function test_catalogue_setup_invalid_import_never_creates_partial_bundles(): void
    {
        config(['services.datasub247.api_key' => 'datasub-global']);
        $plans = [['network' => 'MTN', 'data_plan' => '1 GB', 'provider_plan_id' => '812', 'amount' => 500, 'validate' => '30 Days'],
            ['network' => 'MTN', 'data_plan' => '2 GB', 'provider_plan_id' => 'guessed-id', 'amount' => 900, 'validate' => '30 Days']];
        $this->postJson(route('admin.vtu.pricing.data-catalogue'), ['data_catalogue' => json_encode($plans)])->assertUnprocessable();
        $this->assertSame(0, DB::table('price_list')->count());
        $this->assertSame(0, CustomApi::count());
        Http::assertNothingSent();
    }

    public function test_catalogue_setup_manual_bundle_uses_global_datasub_configuration(): void
    {
        config(['services.datasub247.api_key' => 'datasub-global']);
        $this->postJson(route('admin.vtu.pricing.bundle.store'), ['network' => 'MTN', 'data_plan' => '1 GB',
            'provider_plan_id' => '812', 'amount' => 550, 'validate' => '30 Days'])->assertOk();
        $this->assertSame('datasub247', CustomApi::first()->provider_identifier);
        $this->assertCount(1, app(\App\Services\VtuCatalogService::class)->dataPlans());
        Http::assertNothingSent();
    }

    public function test_catalogue_setup_native_form_saves_without_json_or_provider_fields(): void
    {
        config(['services.datasub247.api_key' => 'datasub-global']);
        $this->postJson(route('admin.vtu.pricing.data-catalogue'), ['plans' => [
            ['network' => 'MTN', 'data_plan' => '1 GB', 'provider_plan_id' => '812', 'amount' => '555.50', 'validate' => '30 Days'],
        ]])->assertOk()->assertJsonPath('count', 1);
        $this->assertEquals(555.50, app(\App\Services\VtuCatalogService::class)->dataPlans()->first()->amount);
    }
}
