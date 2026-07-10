<?php

namespace Tests\Feature\Logistics;

use App\Models\AccountBalance;
use App\Models\DeliveryAgent;
use App\Models\LogisticsCenter;
use App\Models\LogisticsRequest;
use App\Models\LogisticsStaff;
use App\Models\User;
use Database\Seeders\LogisticsCentersSeeder;
use Database\Seeders\LogisticsRbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LogisticsE2EWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_workflow_booking_assign_accept_deliver(): void
    {
        $this->seed(LogisticsRbacSeeder::class);
        $this->seed(LogisticsCentersSeeder::class);

        config()->set('services.google_maps.api_key', 'test-key');
        Http::fake([
            'https://maps.googleapis.com/maps/api/geocode/json*' => Http::response([
                'status' => 'OK',
                'results' => [
                    [
                        'place_id' => 'p1',
                        'formatted_address' => 'Test',
                        'geometry' => ['location' => ['lat' => 6.5, 'lng' => 3.3]],
                    ],
                ],
            ]),
            'https://maps.googleapis.com/maps/api/distancematrix/json*' => Http::response([
                'status' => 'OK',
                'rows' => [
                    [
                        'elements' => [
                            [
                                'status' => 'OK',
                                'distance' => ['value' => 50000],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $user = User::factory()->createOne();
        assert($user instanceof User);
        $user->markEmailAsVerified();
        $user->kyc_tier = 1;
        $user->save();

        AccountBalance::query()->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 500000,
        ]);
        $this->assertDatabaseHas('account_balances', ['email' => $user->email]);

        $pickup = LogisticsCenter::query()->where('state', 'Lagos')->where('type', 'pickup')->first();
        $dropoff = LogisticsCenter::query()->where('state', 'Oyo')->where('type', 'dropoff')->first();
        $this->assertNotNull($pickup);
        $this->assertNotNull($dropoff);

        $book = $this->actingAs($user)->postJson('/logistics/book', [
            'sender_name' => 'Sender',
            'sender_state' => 'Lagos',
            'sender_address' => 'Ikeja',
            'recipient_name' => 'Recipient',
            'recipient_state' => 'Oyo',
            'recipient_address' => 'Ibadan',
            'pickup_method' => 'home_pickup',
            'delivery_method' => 'home_delivery',
            'weight' => 2.0,
            'description' => 'Test package',
            'delivery_type' => 'standard',
        ]);

        $book->assertOk();
        if ($book->json('status') !== true) {
            $this->fail('Booking failed: ' . ($book->json('message') ?? $book->getContent()));
        }
        $tracking = $book->json('tracking_id');
        $this->assertNotEmpty($tracking);

        $order = LogisticsRequest::query()->where('tracking_id', $tracking)->first();
        $this->assertNotNull($order);

        $staff = LogisticsStaff::factory()->createOne(['email' => 'manager@ops.test']);
        assert($staff instanceof LogisticsStaff);
        $staff->assignRole('logistics_manager');

        $agentUser = User::factory()->createOne();
        assert($agentUser instanceof User);
        $agentUser->markEmailAsVerified();

        $agent = DeliveryAgent::factory()->approved()->createOne([
            'user_id' => $agentUser->id,
        ]);
        assert($agent instanceof DeliveryAgent);

        $this->actingAs($staff, 'logistics_staff')->put('/logistics/ops/orders/' . $order->id, [
            'sender_name' => $order->sender_name,
            'sender_address' => $order->sender_address,
            'recipient_name' => $order->recipient_name,
            'recipient_address' => $order->recipient_address,
            'delivery_type' => $order->delivery_type,
            'weight' => $order->weight,
            'amount' => $order->amount,
            'assigned_delivery_agent_id' => $agent->id,
        ])->assertRedirect();

        $this->actingAs($agentUser)->post('/logistics/agent/orders/' . $order->id . '/accept')->assertRedirect();
        $this->actingAs($agentUser)->post('/logistics/agent/orders/' . $order->id . '/status', ['status' => 'delivered'])->assertRedirect();

        $this->assertDatabaseHas('logistics_requests', [
            'id' => $order->id,
            'status' => 'delivered',
            'agent_assignment_status' => 'accepted',
        ]);
    }
}
