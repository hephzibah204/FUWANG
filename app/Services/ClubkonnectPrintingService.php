<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class ClubkonnectPrintingService
{
    private function credentials(): array
    {
        $api = Schema::hasTable('api_centers') ? DB::table('api_centers')->first() : null;
        return ['UserID' => $api->clubkonnect_userid ?? '', 'APIKey' => $api->clubkonnect_apikey ?? ''];
    }
    public function configured(): bool
    {
        $keys = $this->credentials();
        return $keys['UserID'] !== '' && $keys['APIKey'] !== '';
    }
    public function products(string $type): array
    {
        if (!$this->configured()) { return []; }
        if ($type === 'vtu_recharge_card') {
            $products = [];
            foreach (['MTN', 'GLO', 'AIRTEL', '9MOBILE'] as $network) {
                foreach ([100, 200, 500] as $amount) {
                    $products[] = ['service_type' => $type, 'code' => (string) $amount, 'label' => $network.' '.$amount.' airtime PIN', 'network' => $network, 'amount' => $amount];
                }
            }
            return $products;
        }
        if ($type !== 'vtu_data_card') { return []; }
        $keys = $this->credentials();
        try {
            $data = Cache::remember('clubkonnect-printing-plans:'.hash('sha256', $keys['UserID']), 3600, function () use ($keys) {
                $response = Http::timeout(20)->get('https://www.nellobytesystems.com/APIDatabundlePlansV2.asp', ['UserID' => $keys['UserID']]);
                if (!$response->successful() || !is_array($response->json('MOBILE_NETWORK'))) { throw new \RuntimeException('Plan catalogue unavailable.'); }
                return $response->json('MOBILE_NETWORK');
            });
            $products = [];
            foreach ($data as $network => $groups) {
                foreach ($groups as $group) {
                    foreach (($group['PRODUCT'] ?? []) as $product) {
                        if (empty($product['PRODUCT_ID']) || (float) ($product['PRODUCT_AMOUNT'] ?? 0) <= 0) { continue; }
                        $products[] = ['service_type' => $type, 'code' => (string) $product['PRODUCT_ID'], 'label' => $product['PRODUCT_NAME'],
                            'network' => strtoupper($network), 'amount' => (float) $product['PRODUCT_AMOUNT']];
                    }
                }
            }
            return $products;
        } catch (\Throwable $e) { return []; }
    }
    public function purchase(string $type, array $payload, string $reference): array
    {
        $network = ['MTN' => '01', 'GLO' => '02', 'AIRTEL' => '04', '9MOBILE' => '03'][$payload['network']] ?? null;
        if (!$network) { return ['status' => false, 'data' => ['status' => 'failed']]; }
        $params = $this->credentials() + ['MobileNetwork' => $network, 'Quantity' => $payload['quantity'], 'RequestID' => $reference];
        $data = $type === 'vtu_data_card';
        $params[$data ? 'DataPlan' : 'Value'] = $payload['plan_type'];
        try {
            $response = Http::timeout(45)->get('https://www.nellobytesystems.com/'.($data ? 'APIDatabundleEPINV1.asp' : 'APIEPINV1.asp'), $params);
            $result = $this->result($response->successful() ? (array) $response->json() : []);
            $result['reference'] = (string) ($result['data']['orderid'] ?? $reference);
            return $result;
        } catch (\Throwable $e) { return ['status' => false, 'pending' => true, 'data' => []]; }
    }
    public function query(string $reference): array
    {
        try {
            $response = Http::timeout(30)->get('https://www.nellobytesystems.com/APIQueryV1.asp', $this->credentials() + ['OrderID' => $reference]);
            return $this->result($response->successful() ? (array) $response->json() : []);
        } catch (\Throwable $e) { return ['status' => false, 'pending' => true, 'data' => []]; }
    }
    private function result(array $json): array
    {
        $pins = $json['TXN_EPIN'] ?? $json['TXN_EPIN_DATABUNDLE'] ?? null;
        if (is_array($pins) && $pins) {
            return ['status' => true, 'data' => $json + ['status' => 'success', 'pins' => $pins, '_provider' => 'clubkonnect']];
        }
        $failed = in_array($json['status'] ?? '', ['API_ERROR', 'INVALID_CREDENTIALS', 'MISSING_CREDENTIALS', 'INVALID_DATAPLAN', 'MISSING_DATAPLAN'], true);
        return ['status' => false, 'pending' => !$failed, 'data' => $json + ['_provider' => 'clubkonnect']];
    }
}
