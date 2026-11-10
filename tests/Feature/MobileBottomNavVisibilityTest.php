<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MobileBottomNavVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'fullname' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@fuwa.ng',
            'password' => Hash::make('Password@123'),
            'number' => '08012345678',
            'email_verified_at' => now(),
        ]);
    }

    #[Test]
    public function test_bottom_mobile_nav_does_not_show_on_home_page_for_guests(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('nexus-mobile-bottom-nav');
        $response->assertDontSee('mobile-nav-link');
    }

    #[Test]
    public function test_bottom_mobile_nav_does_not_show_on_home_page_for_authenticated_users(): void
    {
        $response = $this->actingAs($this->user)->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('nexus-mobile-bottom-nav');
        $response->assertDontSee('mobile-nav-link');
    }

    #[Test]
    public function test_bottom_mobile_nav_does_not_show_on_public_agents_page_for_authenticated_users(): void
    {
        $response = $this->actingAs($this->user)->get(route('agent.landing'));

        $response->assertOk();
        $response->assertDontSee('nexus-mobile-bottom-nav');
        $response->assertDontSee('mobile-nav-link');
    }

    #[Test]
    public function test_bottom_mobile_nav_does_not_show_on_auth_login_page(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertDontSee('nexus-mobile-bottom-nav');
        $response->assertDontSee('mobile-nav-link');
    }

    #[Test]
    public function test_bottom_mobile_nav_shows_on_authenticated_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('nexus-mobile-bottom-nav');
        $response->assertSee('mobile-nav-link');
    }
}
