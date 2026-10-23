<?php

namespace Tests\Feature;

use App\Services\DataVerify\DataVerifyClient;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationEndpointFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_uses_defaults_without_optional_api_center_columns(): void
    {
        SystemSetting::whereIn('key', ['dataverify_endpoint_validation', 'dataverify_endpoint_validation_status'])->delete();
        \Illuminate\Support\Facades\Cache::flush();
        $client = new DataVerifyClient();
        $this->assertSame('https://dataverify.org/api/developers/validation.php', $client->resolveNinValidationEndpoint());
        $this->assertSame('https://dataverify.org/api/developers/validation_status.php', $client->resolveNinValidationStatusEndpoint());
    }
}
