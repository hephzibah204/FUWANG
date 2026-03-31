<?php

namespace Tests\Feature\Emails;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

class EmailVolumePerformanceTest extends TestCase
{
    use RefreshDatabase;

    #[Group('performance')]
    public function test_can_queue_many_welcome_emails_quickly(): void
    {
        Mail::fake();

        $users = User::factory()->count(250)->create();

        $start = microtime(true);
        foreach ($users as $user) {
            event(new \Illuminate\Auth\Events\Registered($user));
        }
        $elapsed = microtime(true) - $start;

        $this->assertLessThan(2.5, $elapsed);
    }
}

