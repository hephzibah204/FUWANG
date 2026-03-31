<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VerificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfReportAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_download_another_users_report(): void
    {
        $userA = User::create([
            'fullname' => 'User A',
            'email' => 'a@example.com',
            'password' => 'Password@123',
        ]);

        $userB = User::create([
            'fullname' => 'User B',
            'email' => 'b@example.com',
            'password' => 'Password@123',
        ]);

        $result = VerificationResult::create([
            'user_id' => $userA->id,
            'service_type' => 'nin_verification',
            'identifier' => '12345678901',
            'provider_name' => 'TestProvider',
            'status' => 'success',
            'reference_id' => 'TEST-REF',
            'response_data' => ['ok' => true],
        ]);

        $this->actingAs($userB)
            ->get(route('services.verification.report', $result->id))
            ->assertStatus(403);
    }
}

