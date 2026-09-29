<?php

namespace Tests\Unit;

use App\Models\CustomApi;
use App\Services\DataVerify\DataVerifyClient;
use Illuminate\Support\Facades\Http;
use ReflectionMethod;
use Tests\TestCase;

class DataVerifyClientEndpointTest extends TestCase
{
    public function test_invalid_api_subdomain_is_rewritten_to_the_documented_tls_host(): void
    {
        $provider = new CustomApi([
            'provider_identifier' => 'dataverify',
            'endpoint' => 'https://api.dataverify.com.ng/nin',
        ]);

        $method = new ReflectionMethod(DataVerifyClient::class, 'resolveEndpoint');
        $url = $method->invoke(new DataVerifyClient($provider), 'https://api.dataverify.com.ng/nin', 'nin_premium');

        $this->assertSame(
            'https://dataverify.com.ng/developers/nin_slips/nin_premium',
            $url
        );
    }

    public function test_insufficient_balance_is_a_terminal_provider_error(): void
    {
        Http::fake([
            'https://dataverify.com.ng/*' => Http::response([
                'status' => 'error',
                'message' => 'Insufficient balance',
            ], 400),
        ]);

        $provider = new CustomApi([
            'provider_identifier' => 'dataverify',
            'endpoint' => 'https://dataverify.com.ng/developers/nin_slips/nin_premium',
            'api_key' => 'test-key',
            'headers' => [],
            'timeout_seconds' => 10,
        ]);

        $result = (new DataVerifyClient($provider))->verify('nin', [
            'number' => '12345678901',
        ]);

        $this->assertFalse($result['ok']);
        $this->assertTrue($result['terminal']);
        $this->assertSame('Insufficient balance', $result['message']);
    }

    public function test_validation_type_is_included_in_payload_when_provided(): void
    {
        Http::fake([
            'https://dataverify.com.ng/*' => function ($request) {
                $data = $request->data();
                if (($data['validation_type'] ?? null) === 'sim_validation' && ($data['nin'] ?? null) === '12345678901') {
                    return Http::response([
                        'status' => 'success',
                        'message' => 'SIM validation completed',
                        'data' => ['validation_status' => 'success'],
                    ], 200);
                }
                return Http::response(['status' => 'error', 'message' => 'Invalid request'], 400);
            },
        ]);

        $provider = new CustomApi([
            'provider_identifier' => 'dataverify',
            'endpoint' => 'https://dataverify.com.ng/developers/validation.php',
            'api_key' => 'test-key',
            'headers' => [],
        ]);

        $result = (new DataVerifyClient($provider))->verify('nin', [
            'number' => '12345678901',
            'validation_type' => 'sim_validation',
        ]);

        $this->assertTrue($result['ok']);
        $this->assertSame('SIM validation completed', $result['message']);
    }
}
