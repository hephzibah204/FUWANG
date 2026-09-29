<?php

namespace Tests\Feature;

use App\Models\EnrollmentAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentDashboardManagementAndIdCardTest extends TestCase
{
    use RefreshDatabase;

    private function createAgent(array $attributes): EnrollmentAgent
    {
        return EnrollmentAgent::create(array_merge([
            'residential_address' => 'No 12 Ahmadu Bello Way, Kaduna',
            'office_address' => 'Suite 4, Commercial Plaza, Kaduna',
            'bvn' => '22334455667',
            'nin' => '11223344556',
        ], $attributes));
    }

    public function test_approved_agent_dashboard_displays_targets_health_and_support_desk(): void
    {
        $user = User::factory()->create();

        $agent = $this->createAgent([
            'user_id' => $user->id,
            'full_name' => 'Agent Abubakar Sani',
            'phone_number' => '08098765432',
            'state' => 'Kaduna',
            'company_agent_code' => 'KAD-001',
            'status' => 'approved',
            'monthly_enrollments' => 45,
            'total_enrollments' => 120,
            'machine_imei' => '356789012345678',
            'picture_path' => 'agents/photos/test_agent.jpg',
            'nin_verified' => true,
        ]);

        $response = $this->actingAs($user)->get(route('agent.dashboard'));

        $response->assertStatus(200);

        // Target progress & tier
        $response->assertSee('Monthly Target Progress');
        $response->assertSee('Silver Tier');
        $response->assertSee('5% Commission Bonus');
        $response->assertSee('45');

        // Health & Compliance score
        $response->assertSee('Account Compliance Health');
        $response->assertSee('100% Health');
        $response->assertSee('Uploaded');
        $response->assertSee('Verified');

        // Regional Coordinator & WhatsApp Support Desk
        $response->assertSee('Field Operations Desk');
        $response->assertSee('Kaduna State Desk Coordinator');
        $response->assertSee('WhatsApp Desk');
        $response->assertSee('Log Issue Ticket');

        // Verify ID Card button is NOT displayed in public UI as requested
        $response->assertDontSee('href="' . route('agent.id_card') . '"', false);
    }

    public function test_approved_agent_can_preview_digital_id_card(): void
    {
        $user = User::factory()->create();

        $agent = $this->createAgent([
            'user_id' => $user->id,
            'full_name' => 'Agent Abubakar Sani',
            'phone_number' => '08098765432',
            'state' => 'Kaduna',
            'company_agent_code' => 'KAD-001',
            'status' => 'approved',
            'machine_imei' => '356789012345678',
            'nin_verified' => true,
        ]);

        $response = $this->actingAs($user)->get(route('agent.id_card'));

        $response->assertStatus(200);
        $response->assertSee('Agent Abubakar Sani');
        $response->assertSee('Scan QR code with any mobile camera');
        $response->assertSee('Download PDF');
    }

    public function test_approved_agent_can_download_id_card_pdf(): void
    {
        $user = User::factory()->create();

        $agent = $this->createAgent([
            'user_id' => $user->id,
            'full_name' => 'Agent Abubakar Sani',
            'phone_number' => '08098765432',
            'state' => 'Kaduna',
            'company_agent_code' => 'KAD-001',
            'status' => 'approved',
            'machine_imei' => '356789012345678',
            'nin_verified' => true,
        ]);

        $response = $this->actingAs($user)->get(route('agent.id_card.download'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Agent_ID_Card_KAD-001.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_public_qr_code_verification_works(): void
    {
        $user = User::factory()->create();

        $agent = $this->createAgent([
            'user_id' => $user->id,
            'full_name' => 'Agent Fatima Bello',
            'phone_number' => '08011223344',
            'state' => 'Kano',
            'company_agent_code' => 'KAN-990',
            'status' => 'approved',
            'machine_imei' => '998877665544332',
        ]);

        // Access without login (public QR code scan)
        $response = $this->get(route('agent.id_card.verify', ['code' => 'KAN-990']));

        $response->assertStatus(200);
        $response->assertSee('VERIFIED ACTIVE AGENT');
        $response->assertSee('Agent Fatima Bello');
        $response->assertSee('KAN-990');
        $response->assertSee('Kano');

        // Test invalid code scan
        $invalidResponse = $this->get(route('agent.id_card.verify', ['code' => 'FAKE-CODE-123']));
        $invalidResponse->assertStatus(200);
        $invalidResponse->assertSee('UNVERIFIED OR EXPIRED');
        $invalidResponse->assertSee('Credential Not Found');
    }

    public function test_unapproved_agent_cannot_access_id_card(): void
    {
        $user = User::factory()->create();

        $this->createAgent([
            'user_id' => $user->id,
            'full_name' => 'Pending Agent',
            'phone_number' => '08011223344',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('agent.id_card'));
        $response->assertStatus(403);
    }
}
