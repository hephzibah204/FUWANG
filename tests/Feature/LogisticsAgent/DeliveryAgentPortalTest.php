<?php

namespace Tests\Feature\LogisticsAgent;

use App\Models\DeliveryAgent;
use App\Models\LogisticsRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryAgentPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_agent_can_view_dashboard_and_assigned_orders(): void
    {
        $user = User::factory()->createOne();
        assert($user instanceof User);

        $agent = DeliveryAgent::factory()->approved()->createOne([
            'user_id' => $user->id,
        ]);
        assert($agent instanceof DeliveryAgent);

        $order = LogisticsRequest::factory()->createOne([
            'assigned_delivery_agent_id' => $agent->id,
        ]);
        assert($order instanceof LogisticsRequest);

        $resp = $this->actingAs($user)->get('/logistics/agent/dashboard');
        $resp->assertOk();
        $resp->assertSee($order->tracking_id);
    }

    public function test_agent_cannot_view_unassigned_order(): void
    {
        $user = User::factory()->createOne();
        assert($user instanceof User);

        $agent = DeliveryAgent::factory()->approved()->createOne([
            'user_id' => $user->id,
        ]);
        assert($agent instanceof DeliveryAgent);

        $otherOrder = LogisticsRequest::factory()->createOne([
            'assigned_delivery_agent_id' => null,
        ]);
        assert($otherOrder instanceof LogisticsRequest);

        $this->actingAs($user)->get('/logistics/agent/orders/' . $otherOrder->id)->assertStatus(403);
    }

    public function test_pending_agent_cannot_update_status(): void
    {
        $user = User::factory()->createOne();
        assert($user instanceof User);

        $agent = DeliveryAgent::factory()->createOne([
            'user_id' => $user->id,
            'approval_status' => 'pending',
        ]);
        assert($agent instanceof DeliveryAgent);

        $order = LogisticsRequest::factory()->createOne([
            'assigned_delivery_agent_id' => $agent->id,
        ]);
        assert($order instanceof LogisticsRequest);

        $this->actingAs($user)
            ->post('/logistics/agent/orders/' . $order->id . '/status', ['status' => 'in_transit'])
            ->assertStatus(403);
    }

    public function test_agent_must_accept_assignment_before_updating_status(): void
    {
        $user = User::factory()->createOne();
        assert($user instanceof User);

        $agent = DeliveryAgent::factory()->approved()->createOne([
            'user_id' => $user->id,
        ]);
        assert($agent instanceof DeliveryAgent);

        $order = LogisticsRequest::factory()->createOne([
            'assigned_delivery_agent_id' => $agent->id,
            'agent_assignment_status' => 'pending',
        ]);
        assert($order instanceof LogisticsRequest);

        $this->actingAs($user)
            ->post('/logistics/agent/orders/' . $order->id . '/status', ['status' => 'in_transit'])
            ->assertStatus(403);

        $this->actingAs($user)
            ->post('/logistics/agent/orders/' . $order->id . '/accept')
            ->assertRedirect();

        $this->actingAs($user)
            ->post('/logistics/agent/orders/' . $order->id . '/status', ['status' => 'in_transit'])
            ->assertRedirect();
    }

    public function test_agent_can_update_assigned_order_status_when_approved(): void
    {
        $user = User::factory()->createOne();
        assert($user instanceof User);

        $agent = DeliveryAgent::factory()->approved()->createOne([
            'user_id' => $user->id,
        ]);
        assert($agent instanceof DeliveryAgent);

        $order = LogisticsRequest::factory()->createOne([
            'assigned_delivery_agent_id' => $agent->id,
            'status' => 'in_transit',
            'agent_assignment_status' => 'accepted',
        ]);
        assert($order instanceof LogisticsRequest);

        $this->actingAs($user)
            ->post('/logistics/agent/orders/' . $order->id . '/status', ['status' => 'delivered'])
            ->assertRedirect();

        $this->assertDatabaseHas('logistics_requests', [
            'id' => $order->id,
            'status' => 'delivered',
        ]);
    }

    public function test_earnings_page_shows_delivered_commission(): void
    {
        $user = User::factory()->createOne();
        assert($user instanceof User);

        $agent = DeliveryAgent::factory()->approved()->createOne([
            'user_id' => $user->id,
        ]);
        assert($agent instanceof DeliveryAgent);

        $order = LogisticsRequest::factory()->createOne([
            'assigned_delivery_agent_id' => $agent->id,
            'status' => 'delivered',
            'agent_assignment_status' => 'accepted',
            'agent_commission_amount' => 2500,
        ]);
        assert($order instanceof LogisticsRequest);

        $resp = $this->actingAs($user)->get('/logistics/agent/earnings');
        $resp->assertOk();
        $resp->assertSee($order->tracking_id);
        $resp->assertSee('2,500.00');
    }
}
