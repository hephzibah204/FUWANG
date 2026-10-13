<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\FeatureToggle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminServiceToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_feature_toggle_can_be_created_without_explicit_id(): void
    {
        $toggle = FeatureToggle::firstOrNew(['feature_name' => 'identity_verification']);
        $toggle->is_active = false;
        $toggle->offline_message = 'Under maintenance';
        $toggle->save();

        $this->assertNotNull($toggle->id);
        $this->assertGreaterThan(0, $toggle->id);
        $this->assertDatabaseHas('feature_toggles', [
            'id' => $toggle->id,
            'feature_name' => 'identity_verification',
            'is_active' => false,
            'offline_message' => 'Under maintenance',
        ]);
        $this->assertFalse(FeatureToggle::isActive('identity_verification'));
        $this->assertEquals('Under maintenance', FeatureToggle::getMessage('identity_verification'));
    }

    public function test_sequential_toggles_receive_distinct_ids(): void
    {
        $t1 = FeatureToggle::create([
            'feature_name' => 'service_alpha',
            'is_active' => true,
        ]);

        $t2 = FeatureToggle::create([
            'feature_name' => 'service_beta',
            'is_active' => false,
        ]);

        $this->assertNotNull($t1->id);
        $this->assertNotNull($t2->id);
        $this->assertNotEquals($t1->id, $t2->id);
    }

    public function test_admin_can_toggle_service_via_endpoint(): void
    {
        $admin = Admin::factory()->create([
            'is_super_admin' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.services.toggles.set', ['feature' => 'identity_verification']), [
                'is_active' => '0',
                'offline_message' => 'Identity verification is offline.',
            ]);

        $response->assertSessionHas('success', 'Service availability updated.');

        $this->assertDatabaseHas('feature_toggles', [
            'feature_name' => 'identity_verification',
            'is_active' => 0,
            'offline_message' => 'Identity verification is offline.',
        ]);

        $this->assertFalse(FeatureToggle::isActive('identity_verification'));

        // Toggle back on
        $response2 = $this->actingAs($admin, 'admin')
            ->post(route('admin.services.toggles.set', ['feature' => 'identity_verification']), [
                'is_active' => '1',
            ]);

        $response2->assertSessionHas('success', 'Service availability updated.');

        $this->assertDatabaseHas('feature_toggles', [
            'feature_name' => 'identity_verification',
            'is_active' => 1,
        ]);

        $this->assertTrue(FeatureToggle::isActive('identity_verification'));
    }
}
