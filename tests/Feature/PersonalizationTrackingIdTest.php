<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\ApiCenter;
use App\Models\FeatureToggle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PersonalizationTrackingIdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        FeatureToggle::query()->updateOrCreate(
            ['feature_name' => 'identity_verification'],
            ['is_active' => true]
        );
    }

    public function test_personalization_accepts_tracking_id_and_posts_tracking_id_payload(): void
    {
        $user = User::create([
            'fullname' => 'Test User',
            'username' => 'u_pers_001',
            'email' => 'pers@example.com',
            'password' => Hash::make('Password@123'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 100000,
            'api_key' => 'user',
        ]);

        ApiCenter::create([
            'robosttech_api_key' => 'robost_key',
            'robosttech_endpoint_personalization' => 'https://robosttech.com/api',
        ]);

        Http::fake(function ($request) {
            if (str_ends_with($request->url(), '/personalization')) {
                $data = $request->data();
                if (($data['tracking_id'] ?? null) !== 'ABC12345XYZ' || ($data['number'] ?? null) !== 'ABC12345XYZ') {
                    return Http::response(['message' => 'Bad payload'], 422);
                }
                return Http::response(['status' => 'success', 'message' => 'OK'], 200);
            }
            return Http::response(['message' => 'Unexpected request'], 500);
        });

        $response = $this->actingAs($user)
            ->post(route('services.personalization.verify'), ['tracking_id' => 'ABC12345XYZ'])
            ->assertOk()
            ->assertJsonPath('status', true);

        $reportUrl = $response->json('report_url');
        $this->assertNotEmpty($reportUrl);

        // Verify the HTML report renders beautifully with no raw JSON or vendor names
        $reportResponse = $this->actingAs($user)->get($reportUrl);
        $reportResponse->assertOk()
            ->assertSee('ABC12345XYZ')
            ->assertSee('NIMC Identity Personalization Certificate')
            ->assertDontSee('Robosttech')
            ->assertDontSee('Dataverify')
            ->assertDontSee('<pre class="text-white">', false);

        // Verify unauthorized user cannot view report
        $otherUser = User::create([
            'fullname' => 'Other User',
            'username' => 'u_pers_other',
            'email' => 'other@example.com',
            'password' => Hash::make('Password@123'),
        ]);
        $this->actingAs($otherUser)->get($reportUrl)->assertForbidden();

        // Verify transaction history show page works seamlessly for Personalization (no VTU crash)
        $tx = \App\Models\Transaction::where('order_type', 'Personalization')->latest()->firstOrFail();
        $historyResp = $this->actingAs($user)->get(route('history.show', $tx->transaction_id));
        $historyResp->assertOk()
            ->assertSee($tx->transaction_id)
            ->assertSee('ABC12345XYZ')
            ->assertSee('View / Print Official Report');
    }
}

