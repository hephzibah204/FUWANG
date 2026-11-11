<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\TestimonialAvatars;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserProfileAndThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_profile_page()
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'fullname' => 'Test User',
            'number' => '08012345678',
        ]);

        $response = $this->actingAs($user)->get(route('profile'));

        $response->assertStatus(200);
        $response->assertSee('My Profile');
        $response->assertSee('testuser');
        $response->assertSee('Test User');
        $response->assertSee('Upload Photo');
    }

    public function test_user_can_update_profile_details()
    {
        $user = User::factory()->create([
            'username' => 'oldusername',
            'fullname' => 'Old Name',
            'number' => '08011111111',
        ]);

        $response = $this->actingAs($user)->post(route('profile.update'), [
            'fullname' => 'New Fullname',
            'number' => '08099998888',
            'username' => 'newusername',
        ]);

        $response->assertSessionHas('success');
        $user->refresh();
        $this->assertEquals('New Fullname', $user->fullname);
        $this->assertEquals('08099998888', $user->number);
        $this->assertEquals('newusername', $user->username);
    }

    public function test_user_can_upload_and_remove_avatar()
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'username' => 'avataruser',
        ]);

        $file = UploadedFile::fake()->image('custom_avatar.jpg', 200, 200);

        $response = $this->actingAs($user)->post(route('profile.update'), [
            'fullname' => $user->fullname,
            'number' => $user->number,
            'username' => $user->username,
            'avatar' => $file,
        ]);

        $response->assertSessionHas('success');
        $user->refresh();

        $this->assertNotNull($user->avatar);
        $this->assertStringStartsWith('avatars/', $user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
        $this->assertStringContainsString($user->avatar, $user->avatar_url);

        // Now remove avatar
        $removeResponse = $this->actingAs($user)->post(route('profile.update'), [
            'fullname' => $user->fullname,
            'number' => $user->number,
            'username' => $user->username,
            'remove_avatar' => '1',
        ]);

        $removeResponse->assertSessionHas('success');
        $user->refresh();
        $this->assertNull($user->avatar);
    }

    public function test_testimonial_avatars_for_chioma_and_yusuf_are_distinct()
    {
        $chiomaAvatar = TestimonialAvatars::webPath('Chioma Adeyemi');
        $yusufAvatar = TestimonialAvatars::webPath('Yusuf Ibrahim');

        $this->assertNotEmpty($chiomaAvatar);
        $this->assertNotEmpty($yusufAvatar);
        $this->assertNotEquals($chiomaAvatar, $yusufAvatar, 'Chioma and Yusuf must not have the same testimonial avatar');
        $this->assertStringContainsString('testimonial-03.jpg', $chiomaAvatar);
        $this->assertStringContainsString('testimonial-05.jpg', $yusufAvatar);
    }

    public function test_nexus_layout_contains_theme_toggle_and_history_in_account_navigation()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        // Theme toggle presence
        $response->assertSee('id="themeToggle"', false);
        // History route presence
        $response->assertSee(route('history'), false);
    }

    public function test_user_dashboard_has_no_ai_slop()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('id="aiFabToggle"', false);
        $response->assertDontSee('id="aiChatCard"', false);
        $response->assertDontSee('nexus-ai.js', false);
        $response->assertDontSee('hero-bg-accent', false);
        $response->assertDontSee('hw-glow', false);
        $response->assertDontSee('secure command center', false);
        $response->assertDontSee('fa-crown', false);
    }
}
