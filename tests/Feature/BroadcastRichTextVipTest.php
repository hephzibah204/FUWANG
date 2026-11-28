<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Broadcast;
use App\Models\EnrollmentAgent;
use App\Models\User;
use App\Notifications\GeneralBroadcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Owner request 2026-11-28: Word-style formatting in broadcasts, and
 * the VIP audience fixed (it previously applied no filter at all, so
 * VIP broadcasts went to every user).
 *
 * VIP = paid license holder OR total completed wallet funding of at
 * least the broadcast_vip_min_funding setting (default 50000).
 */
class BroadcastRichTextVipTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            \Laravel\Telescope\Telescope::stopRecording();
        }

        $this->admin = Admin::factory()->create(['is_super_admin' => true]);
    }

    private function stopTelescope(): void
    {
        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            \Laravel\Telescope\Telescope::stopRecording();
        }
    }

    private function makeAgent(User $user, array $overrides = []): EnrollmentAgent
    {
        return EnrollmentAgent::create(array_merge([
            'user_id' => $user->id,
            'full_name' => 'Test Agent ' . $user->id,
            'phone_number' => '080' . random_int(10000000, 99999999),
            'residential_address' => 'Sample Residential 1',
            'office_address' => 'Sample Office 1',
            'bvn' => '12345678901',
            'nin' => '12345678901',
            'state' => 'Lagos',
            'status' => 'approved',
        ], $overrides));
    }

    private function fund(string $email, float $amount): void
    {
        DB::table('fundings')->insert([
            'email' => $email,
            'amount' => $amount,
            'reference' => 'TEST-' . uniqid(),
            'description' => 'Wallet funding',
            'funding_type' => 'wallet',
            'fullname' => 'Test User',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_vip_broadcast_reaches_only_license_holders_and_big_funders(): void
    {
        Notification::fake();

        $licenseHolder = User::factory()->create();
        $this->makeAgent($licenseHolder, ['license_status' => 'paid', 'license_fee_paid' => 200000]);

        $bigFunder = User::factory()->create();
        $this->fund($bigFunder->email, 30000);
        $this->fund($bigFunder->email, 35000);

        $smallFunder = User::factory()->create();
        $this->fund($smallFunder->email, 5000);

        $plain = User::factory()->create();

        $unpaidAgent = User::factory()->create();
        $this->makeAgent($unpaidAgent, ['license_status' => 'unpaid']);

        $this->stopTelescope();
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.broadcasts.store'), [
                'subject' => 'VIP Notice',
                'message' => '<p>For our valued users.</p>',
                'target_audience' => 'vip',
                'send_now' => '1',
            ])
            ->assertSessionHas('success');

        Notification::assertSentTo($licenseHolder, GeneralBroadcast::class);
        Notification::assertSentTo($bigFunder, GeneralBroadcast::class);
        Notification::assertNotSentTo($smallFunder, GeneralBroadcast::class);
        Notification::assertNotSentTo($plain, GeneralBroadcast::class);
        Notification::assertNotSentTo($unpaidAgent, GeneralBroadcast::class);

        $broadcast = Broadcast::where('subject', 'VIP Notice')->firstOrFail();
        $this->assertSame(2, (int) data_get($broadcast->meta, 'recipient_count'));
    }

    public function test_broadcast_message_is_sanitised_on_save(): void
    {
        $this->stopTelescope();
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.broadcasts.store'), [
                'subject' => 'Formatted',
                'message' => '<p>Hello <strong>Agents</strong></p><script>alert(1)</script>',
                'target_audience' => 'all',
            ])
            ->assertSessionHas('success');

        $broadcast = Broadcast::where('subject', 'Formatted')->firstOrFail();
        $this->assertStringContainsString('<strong>Agents</strong>', $broadcast->message);
        $this->assertStringNotContainsString('<script>', $broadcast->message);
    }

    public function test_broadcast_mail_renders_the_formatting(): void
    {
        $broadcast = Broadcast::create([
            'subject' => 'Formatted Mail',
            'message' => '<p>Hello <strong>Agents</strong></p><script>alert(1)</script>',
            'target_audience' => 'all',
            'status' => 'sent',
        ]);

        $user = User::factory()->create();
        $mail = (new GeneralBroadcast($broadcast))->toMail($user);

        $this->assertSame('Formatted Mail', $mail->subject);
        $html = view('emails.broadcast', $mail->viewData)->render();
        $this->assertStringContainsString('<strong>Agents</strong>', $html);
        $this->assertStringNotContainsString('<script>', $html);

        $data = (new GeneralBroadcast($broadcast))->toArray($user);
        $this->assertSame('html', $data['format']);
    }

    public function test_editor_is_on_both_broadcast_forms(): void
    {
        $this->stopTelescope();
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.broadcasts.create'))
            ->assertOk()
            ->assertSee('contenteditable', false)
            ->assertSee('VIP / High Value (paid license holders or ₦50,000+ funded)', false);

        $this->stopTelescope();
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.agents.notifications.index'))
            ->assertOk()
            ->assertSee('contenteditable', false);
    }

    public function test_agent_broadcast_is_sanitised_on_save(): void
    {
        $this->stopTelescope();
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.notifications.store'), [
                'subject' => 'Agent Notice',
                'message' => '<p>Read <strong>this</strong></p><img src="https://tracker.example/p.gif">',
                'target_status' => 'all',
                'target_state' => 'all',
            ])
            ->assertSessionHas('success');

        $broadcast = Broadcast::where('subject', 'Agent Notice')->firstOrFail();
        $this->assertStringContainsString('<strong>this</strong>', $broadcast->message);
        $this->assertStringNotContainsString('<img', $broadcast->message);
    }
}
