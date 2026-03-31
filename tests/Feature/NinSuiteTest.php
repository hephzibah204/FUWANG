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
