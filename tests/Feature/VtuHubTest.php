<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AccountBalance;
use App\Models\CustomApi;
use App\Services\VtuHubService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VtuHubTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $vtuHub;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['email' => 'test@fuwa.ng']);
        AccountBalance::create([
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'user_balance' => 5000.00,
            'api_key' => 'user'
        ]);

        $this->vtuHub = app(VtuHubService::class);
    }

    /** @test */
    public function it_can_process_a_successful_airtime_request()
    {
        CustomApi::create([
            'name' => 'Test Provider',
            'service_type' => 'vtu_airtime',
            'endpoint' => 'https://api.test.com/vending',
            'api_key' => 'test_key',
            'status' => true,
            'priority' => 1,
            'price' => 100.00
        ]);

        Http::fake([
            'https://api.test.com/vending' => Http::response(['code' => '000', 'message' => 'Success'], 200)
        ]);

        $response = $this->vtuHub->processRequest([
            'service_type' => 'vtu_airtime',
            'amount' => 100.00,
            'order_type' => 'Airtime Purchase',
            'tx_prefix' => 'VTU',
            'payload' => [
                'network' => 'MTN',
                'amount' => 100.00,
                'phone' => '08112233445'
            ]
        ]);

        $this->assertTrue($response['status']);
        $this->assertEquals(4900.00, (float) $this->user->balance->user_balance);
    }

    /** @test */
    public function it_refunds_on_api_failure()
    {
        CustomApi::create([
            'name' => 'Test Provider',
            'service_type' => 'vtu_data',
            'endpoint' => 'https://api.test.com/vending',
            'api_key' => 'test_key',
            'status' => true,
            'priority' => 1,
            'price' => 500.00
        ]);

        Http::fake([
            'https://api.test.com/vending' => Http::response(['error' => 'Internal Server Error'], 500)
        ]);

        $response = $this->vtuHub->processRequest([
            'service_type' => 'vtu_data',
            'amount' => 500.00,
            'order_type' => 'Data Purchase',
            'tx_prefix' => 'DATA',
            'payload' => [
                'network' => 'MTN',
                'plan_id' => '1GB',
                'phone' => '08112233445'
            ]
        ]);

        $this->assertFalse($response['status']);
        $this->assertEquals(5000.00, (float) $this->user->balance->user_balance);
    }
}
