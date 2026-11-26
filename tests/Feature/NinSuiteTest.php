<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\SystemSetting;

class NinSuiteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an authenticated user can access the NIN Suite page.
     */
    public function test_authenticated_user_can_access_nin_suite()
    {
        // Set up test user
        $user = User::factory()->create();

        // Ensure NIN service is enabled
        SystemSetting::set('nin_service_enabled', 'true');

        $response = $this->actingAs($user)->get(route('services.nin.suite'));

        $response->assertStatus(200);
        $response->assertViewIs('services.identity.nin_suite');
        
        // Assert it contains the sub-service links
        $response->assertSee(route('services.validation'));
        $response->assertSee(route('services.personalization'));
        $response->assertSee(route('services.clearance'));
        $response->assertSee(route('services.nin')); // NIN Verify and Print Slip
    }

    /**
     * Test that an unauthenticated user is redirected to login.
     */
    public function test_unauthenticated_user_cannot_access_nin_suite()
    {
        $response = $this->get(route('services.nin.suite'));

        $response->assertRedirect(route('login'));
    }

    /**
     * The suite's sub-services (Validation, Clearance, Personalization) are
     * gated by the NIN Suite toggle (nin_verification), not by
     * identity_verification. Regression test for the owner's 2026-11-26
     * report: with the suite ON and identity_verification OFF, Validation
     * wrongly said the feature was unavailable.
     */
    public function test_suite_sub_services_follow_the_nin_suite_toggle_not_identity_verification()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        \App\Models\FeatureToggle::create(['feature_name' => 'identity_verification', 'is_active' => false]);
        \App\Models\FeatureToggle::create(['feature_name' => 'nin_verification', 'is_active' => true]);
        \Illuminate\Support\Facades\Cache::flush();

        foreach (['services.validation', 'services.clearance', 'services.personalization'] as $routeName) {
            $response = $this->actingAs($user)->get(route($routeName));
            $response->assertStatus(200);
        }

        // With the suite itself OFF, the sub-services go down with it.
        \App\Models\FeatureToggle::where('feature_name', 'nin_verification')->update(['is_active' => false]);
        \Illuminate\Support\Facades\Cache::flush();

        $response = $this->actingAs($user)->get(route('services.validation'));
        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Test that the dashboard displays the NIN Suite link.
     */
    public function test_dashboard_displays_nin_suite_link()
    {
        $user = User::factory()->create();
        SystemSetting::set('nin_service_enabled', 'true');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee(route('services.nin.suite'));
        $response->assertSee('NIN Suite');
    }
}
