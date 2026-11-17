<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VtuPricingPersistenceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        config(['session.driver' => 'array']);
        Cache::flush();
        SystemSetting::clearLocalCache();
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->nullable();
            $table->string('type')->nullable();
            $table->string('label')->nullable();
            $table->timestamps();
        });
    }

    private function figures(): array
    {
        return ['vtu_airtime_discount_percent' => '3.25', 'vtu_bulk_sms_rate' => '5.75',
            'vtu_electricity_fee' => '0', 'vtu_waec_price' => '4301.25',
            'vtu_waec_reg_price' => '19234.50', 'vtu_neco_price' => '1777.75',
            'vtu_nabteb_price' => '1456.25', 'vtu_jamb_price' => '5123.75'];
    }

    public function test_saved_rates_replace_cached_defaults_and_survive_a_fresh_read(): void
    {
        SystemSetting::query()->create(['key' => 'vtu_bulk_sms_rate', 'value' => '4']);
        $this->assertEquals(4, SystemSetting::get('vtu_bulk_sms_rate'));
        $this->postJson(route('admin.vtu.pricing.rates'), $this->figures())
            ->assertOk()->assertJson(['status' => true]);
        $this->assertEquals(5.75, SystemSetting::get('vtu_bulk_sms_rate'));
        SystemSetting::clearLocalCache();
        foreach ($this->figures() as $key => $value) {
            $this->assertDatabaseHas('system_settings', ['key' => $key, 'value' => $value]);
            $this->assertEquals((float) $value, SystemSetting::get($key));
        }
    }

    public function test_failed_write_rolls_back_all_rates_and_catalogue_without_success(): void
    {
        SystemSetting::query()->create(['key' => 'vtu_airtime_discount_percent', 'value' => '2']);
        SystemSetting::query()->create(['key' => 'vtu_product_catalog', 'value' => '[{"code":"keep"}]']);
        DB::unprepared("CREATE TRIGGER fail_pricing BEFORE INSERT ON system_settings WHEN NEW.key = 'vtu_electricity_fee' BEGIN SELECT RAISE(ABORT, 'Simulated pricing write failure'); END");
        $this->postJson(route('admin.vtu.pricing.rates'), $this->figures() + ['product_catalog' => '[]'])
            ->assertStatus(503)->assertJson(['status' => false]);
        $this->assertDatabaseHas('system_settings', ['key' => 'vtu_airtime_discount_percent', 'value' => '2']);
        $this->assertDatabaseHas('system_settings', ['key' => 'vtu_product_catalog', 'value' => '[{"code":"keep"}]']);
        $this->assertDatabaseMissing('system_settings', ['key' => 'vtu_bulk_sms_rate']);
    }

    public function test_failed_html_save_retains_entered_figures(): void
    {
        Schema::drop('system_settings');
        $this->from('/admin/vtu/pricing')->post(route('admin.vtu.pricing.rates'), $this->figures())
            ->assertRedirect('/admin/vtu/pricing')->assertSessionHasErrors('pricing')
            ->assertSessionHasInput('vtu_waec_price', '4301.25')->assertSessionMissing('success');
    }

    public function test_invalid_catalogue_does_not_save_any_rates(): void
    {
        $this->postJson(route('admin.vtu.pricing.rates'), $this->figures() + ['product_catalog' => 'invalid'])
            ->assertUnprocessable();
        $this->assertDatabaseCount('system_settings', 0);
    }

    public function test_pricing_form_renders_saved_decimals_and_bundle_editor(): void
    {
        app('view')->addLocation(base_path('tests/Fixtures/views'));
        app('view')->share('errors', new \Illuminate\Support\ViewErrorBag());
        $template = str_replace("@extends('layouts.nexus')", "@extends('vtu-test-layout')", file_get_contents(resource_path('views/admin/vtu/pricing.blade.php')));
        $html = \Illuminate\Support\Facades\Blade::render($template, ['rates' => $this->figures(), 'bundles' => collect(),
            'providers' => collect(), 'bandNames' => collect(), 'productCatalog' => '[]']);
        $this->assertStringContainsString('value="4301.25"', $html);
        $this->assertStringContainsString('name="plans[0][provider_plan_id]"', $html);
        $directory = storage_path('framework/testing/vtu-ui');
        if (!is_dir($directory)) mkdir($directory, 0777, true);
        file_put_contents($directory.'/admin-pricing-current.html', $html);
    }
}
