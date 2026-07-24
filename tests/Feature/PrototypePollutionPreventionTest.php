<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrototypePollutionPreventionTest extends TestCase
{
    /** @test */
    public function it_allows_safe_requests_to_pass()
    {
        $response = $this->postJson('/api/logistics/ops/login', [
            'username' => 'staff_member',
            'password' => 'password123'
        ]);

        // Should not get blocked by prototype pollution middleware (should get 422/401/404 depending on existence)
        $this->assertNotEquals(400, $response->getStatusCode());
    }

    /** @test */
    public function it_blocks_requests_containing_proto_in_payload_keys()
    {
        $response = $this->postJson('/api/logistics/ops/login', [
            '__proto__' => [
                'polluted' => true
            ],
            'username' => 'staff_member'
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'status' => false,
            'message' => 'Bad Request: Suspected malicious payload.'
        ]);
    }

    /** @test */
    public function it_blocks_requests_containing_proto_in_payload_nested_keys()
    {
        $response = $this->postJson('/api/logistics/ops/login', [
            'nested' => [
                '__proto__' => [
                    'polluted' => true
                ]
            ],
            'username' => 'staff_member'
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function it_blocks_requests_containing_proto_in_string_values()
    {
        $response = $this->postJson('/api/logistics/ops/login', [
            'username' => 'staff_member',
            'comment' => 'attempting __proto__ pollution'
        ]);

        $response->assertStatus(400);
    }
}
