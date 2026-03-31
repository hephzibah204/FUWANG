<?php

namespace Tests\Unit\Emails;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomeUserMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_html_and_text_with_user_data(): void
    {
        $user = User::factory()->create([
            'fullname' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $mail = new WelcomeUserMail($user);

        $html = $mail->render();
        $this->assertStringContainsString('Jane Doe', $html);
        $this->assertStringContainsString('Go to dashboard', $html);
        $this->assertStringContainsString('Reference:', $html);

        $text = view('emails.user.welcome_text', $mail->content()->with)->render();
        $this->assertStringContainsString('Jane Doe', $text);
        $this->assertStringContainsString('/dashboard', $text);
    }
}

