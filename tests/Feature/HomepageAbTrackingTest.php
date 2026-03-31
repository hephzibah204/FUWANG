<?php

namespace Tests\Feature;

use App\Models\AbEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageAbTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_and_respects_ab_cookie_variant(): void
    {
        $this->withCookie('ab_home_hero', 'B')
            ->get('/')
            ->assertOk()
            ->assertSeeText("Nigeria's Most Powerful Verification & Fintech OS");

        $this->withCookie('ab_home_hero', 'A')
            ->get('/')
            ->assertOk()
            ->assertSeeText("Nigeria's Most Powerful Verification & Fintech OS");
    }

    public function test_ab_event_endpoint_persists_event(): void
    {
        $this->postJson(route('ab.event'), [
            'event_name' => 'page_view',
            'page' => '/',
            'experiment' => 'home_hero',
            'variant' => 'A',
            'session_id' => 'sid',
            'meta' => ['ref' => null],
        ])->assertOk()->assertJson(['status' => true]);

        $this->assertDatabaseCount('ab_events', 1);
        $event = AbEvent::first();
        $this->assertSame('page_view', $event->event_name);
        $this->assertSame('home_hero', $event->experiment);
        $this->assertSame('A', $event->variant);
        $this->assertSame('/', $event->page);
    }
}
