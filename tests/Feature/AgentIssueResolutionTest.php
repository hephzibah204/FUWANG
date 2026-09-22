<?php

namespace Tests\Feature;

use App\Models\EnrollmentAgent;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AgentIssueResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_submit_issue_with_screenshot(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'user_status' => 'active',
        ]);

        $agent = EnrollmentAgent::create([
            'user_id' => $user->id,
            'full_name' => 'Agent Terminal One',
            'phone_number' => '08011112222',
            'residential_address' => 'Res Addr',
            'office_address' => 'Off Addr',
            'bvn' => '12345678901',
            'nin' => '10987654321',
            'machine_imei' => '864201049999999',
            'picture_path' => 'agent_kyc/profile.jpg',
            'status' => 'approved',
        ]);

        $file = UploadedFile::fake()->image('error_proof.png');

        $response = $this->actingAs($user)->post(route('agent.issues.store'), [
            'category' => 'terminal_hardware',
            'subject' => 'Biometric Scanner Read Error',
            'message' => 'The optical sensor returns error code 403 on thumbprints.',
            'priority' => 'high',
            'machine_imei' => '864201049999999',
            'attachment' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'agent_id' => $agent->id,
            'subject' => 'Biometric Scanner Read Error',
            'category' => 'terminal_hardware',
            'machine_imei' => '864201049999999',
            'priority' => 'high',
            'status' => 'open',
        ]);

        $ticket = Ticket::first();
        $this->assertNotNull($ticket->attachment_path);
        Storage::disk('public')->assertExists($ticket->attachment_path);
    }

    public function test_admin_can_reply_and_resolve_agent_issue(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $agent = EnrollmentAgent::create([
            'user_id' => $user->id,
            'full_name' => 'Agent Terminal Two',
            'phone_number' => '08033334444',
            'residential_address' => 'Res Addr',
            'office_address' => 'Off Addr',
            'bvn' => '12345678901',
            'nin' => '10987654321',
            'machine_imei' => '864201047777777',
            'status' => 'approved',
        ]);

        $ticket = Ticket::create([
            'user_email' => $user->email,
            'agent_id' => $agent->id,
            'subject' => 'NIMC Gateway Requery Timeout',
            'category' => 'nin_verification',
            'machine_imei' => '864201047777777',
            'priority' => 'medium',
            'status' => 'open',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.agents.issues.reply', $ticket->id), [
            'message' => 'Gateway endpoint updated. Please reboot your terminal.',
            'status' => 'resolved',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'resolved',
        ]);

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'sender_type' => 'admin',
            'message' => 'Gateway endpoint updated. Please reboot your terminal.',
        ]);
    }
}
