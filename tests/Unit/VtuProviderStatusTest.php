<?php

namespace Tests\Unit;

use App\Services\Datasub247Service;
use App\Support\SmsSegments;
use App\Support\VtuProviderStatus;
use PHPUnit\Framework\TestCase;

class VtuProviderStatusTest extends TestCase
{
    public function test_confirmed_api_balance_rejections_are_recognized_without_guessing_from_balances(): void
    {
        foreach (['Insufficient Balance', 'Insufficient wallet balance', 'API balance is low', 'Your balance is insufficient', 'Not enough funds', 'LOW_BALANCE', 'INSUFFICIENT_FUNDS'] as $message) {
            $this->assertTrue(\App\Support\VtuCustomerMessages::balanceLow(['status' => 'failed', 'message' => $message]), $message);
        }
        foreach ([['status' => 'success', 'message' => 'Low balance warning'], ['status' => 'pending', 'message' => 'Insufficient balance'],
            ['status' => 'failed', 'message' => 'Invalid plan'], ['oldbal' => 0, 'newbal' => 0], []] as $payload) {
            $this->assertFalse(\App\Support\VtuCustomerMessages::balanceLow($payload));
        }
    }
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

    public function test_documented_datasub_network_ids_match_airtime_and_data_documentation(): void
    {
        $adapter = (new \ReflectionClass(Datasub247Service::class))->newInstanceWithoutConstructor();
        foreach (['MTN' => 1, 'GLO' => 3, 'AIRTEL' => 2, '9MOBILE' => 4,
            '01' => 1, '02' => 3, '04' => 2, '03' => 4] as $network => $id) {
            $this->assertSame($id, $adapter->resolveNetworkId($network));
        }
    }

    public function test_remaining_documentation_page_confirms_all_eight_electricity_ids(): void
    {
        $adapter = (new \ReflectionClass(Datasub247Service::class))->newInstanceWithoutConstructor();
        foreach (['ikeja-electric' => 1, 'eko-electric' => 2, 'kano-electric' => 3, 'portharcourt-electric' => 4,
            'jos-electric' => 5, 'ibadan-electric' => 6, 'kaduna-electric' => 7, 'abuja-electric' => 8] as $code => $id) {
            $this->assertSame($id, $adapter->resolveDiscoId($code));
            $this->assertSame($id, $adapter->resolveDiscoId($id));
        }
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
