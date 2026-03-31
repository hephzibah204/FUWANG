<?php

namespace Tests\Feature;

use App\Models\FeatureToggle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureToggleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_feature_toggle_allows_access(): void
    {
        $user = User::create([
            'fullname' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $response = $this->actingAs($user)->get(route('services.nin'));

        $response->assertOk();
    }

    public function test_disabled_feature_toggle_redirects_to_dashboard(): void
    {
        FeatureToggle::create([
            'feature_name' => 'nin_verification',
            'is_active' => false,
            'offline_message' => 'NIN is offline',
        ]);

        $user = User::create([
            'fullname' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $response = $this->actingAs($user)->get(route('services.nin'));

        $response->assertRedirect(route('dashboard'));
    }
}

