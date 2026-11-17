<?php

namespace Tests\Unit;

use App\Services\Datasub247Service;
use App\Support\SmsSegments;
use App\Support\VtuProviderStatus;
use PHPUnit\Framework\TestCase;

class VtuProviderStatusTest extends TestCase
{
    public function test_only_explicit_final_provider_responses_are_successful(): void
    {
        foreach ([[], ['status' => 'pending'], ['status' => 'processing'], ['hello' => 'world']] as $response) {
            $this->assertSame('pending', VtuProviderStatus::state($response));
        }
        foreach ([['status' => 'false'], ['success' => 0], ['status' => 'API_ERROR'], ['status' => 'success', 'success' => false]] as $response) {
            $this->assertSame('failed', VtuProviderStatus::state($response));
        }
        $this->assertSame('success', VtuProviderStatus::state(['status' => 'success']));
    }
    public function test_legacy_network_codes_do_not_swap_airtel_and_9mobile(): void
    {
        $adapter = (new \ReflectionClass(Datasub247Service::class))->newInstanceWithoutConstructor();
        $this->assertSame($adapter->resolveNetworkId('AIRTEL'), $adapter->resolveNetworkId('04'));
        $this->assertSame($adapter->resolveNetworkId('9MOBILE'), $adapter->resolveNetworkId('03'));
    }
    public function test_unsupported_exam_cannot_be_sold_as_waec(): void
    {
        $adapter = (new \ReflectionClass(Datasub247Service::class))->newInstanceWithoutConstructor();
        $this->expectException(\InvalidArgumentException::class); $adapter->resolveExamId('jamb');
    }
    public function test_unicode_and_gsm_extension_characters_are_charged_correctly(): void
    {
        $this->assertSame(1, SmsSegments::count(str_repeat('A', 160)));
        $this->assertSame(2, SmsSegments::count(str_repeat('A', 161)));
        $this->assertSame(2, SmsSegments::count(str_repeat('^', 81)));
        $this->assertSame(2, SmsSegments::count(str_repeat("\u{6f22}", 71)));
        $this->assertSame(2, SmsSegments::count(str_repeat("\u{1f600}", 36)));
    }
}
