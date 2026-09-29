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
            'https://dataverify.org/developers/nin_slips/nin_premium',
            $url
        );
    }

    public function test_legacy_domain_is_automatically_migrated_to_dataverify_org(): void
    {
        $this->assertSame(
            'https://dataverify.org/api/developers/bvn_retrieval.php',
            DataVerifyClient::normalizeDomain('https://dataverify.com.ng/api/developers/bvn_retrieval.php')
        );

        $this->assertSame(
            'https://dataverify.org/developers/nin_slips/nin_premium',
            DataVerifyClient::normalizeDomain('https://api.dataverify.com.ng/developers/nin_slips/nin_premium')
        );

        $this->assertSame(
            'https://dataverify.org/developers/nin_slips/nin_premium',
            DataVerifyClient::normalizeDomain('https://dataverify.ng/developers/nin_slips/nin_premium')
        );
    }

    public function test_insufficient_balance_is_a_terminal_provider_error(): void
    {
        Http::fake([
            'https://dataverify.org/*' => Http::response([
                'status' => 'error',
                'message' => 'Insufficient balance',
            ], 400),
            'https://dataverify.com.ng/*' => Http::response([
                'status' => 'error',
                'message' => 'Insufficient balance',
            ], 400),
        ]);

        $provider = new CustomApi([
            'provider_identifier' => 'dataverify',
            'endpoint' => 'https://dataverify.org/developers/nin_slips/nin_premium',
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
            'https://dataverify.org/*' => function ($request) {
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
            'endpoint' => 'https://dataverify.org/developers/validation.php',
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

    public function test_check_balance_calls_dataverify_org(): void
    {
        Http::fake([
            'https://dataverify.org/api/developers/balance.php*' => Http::response([
                'status' => 'success',
                'balance' => 4500.50,
                'message' => 'Balance fetched',
            ], 200),
        ]);

        $provider = new CustomApi([
            'provider_identifier' => 'dataverify',
            'api_key' => 'test-api-key',
        ]);

        $client = new DataVerifyClient($provider);
        $res = $client->checkBalance();

        $this->assertTrue($res['ok']);
        $this->assertSame(4500.50, $res['balance']);
    }
}
