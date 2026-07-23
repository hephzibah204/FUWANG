<?php

namespace Tests\Unit;

use App\Models\CustomApi;
use App\Services\DataVerify\DataVerifyClient;
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
}
