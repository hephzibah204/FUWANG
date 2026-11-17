<?php

namespace App\Services;

use App\Models\CustomApi;
use App\Models\VtuTransaction;
use App\Support\VtuProviderStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class VtuReconciliationService
{
    public function reconcile(VtuTransaction $tx): VtuTransaction
    {
        if ($tx->status !== 'pending') { return $tx; }
        $lock = Cache::lock('vtu-reconcile:'.$tx->id, 90);
        if (!$lock->get()) { return $tx; }
        try {
            if (($tx->response_payload['_provider'] ?? '') === 'clubkonnect') {
                $result = app(ClubkonnectPrintingService::class)->query($tx->provider_reference ?: $tx->transaction_id);
                $state = ($result['pending'] ?? false) ? 'pending' : (($result['status'] ?? false) ? 'success' : 'failed');
                return app(VtuSettlementService::class)->settle($tx, $state, $result['data'] ?? []);
            }
            $provider = $tx->custom_api_id ? CustomApi::find($tx->custom_api_id) : null;
            $cfg = (array) ($provider?->config ?? []);
            $datasub = $provider?->provider_identifier === 'datasub247' || !$provider;
            $url = $cfg['status_endpoint'] ?? ($datasub ? config('services.datasub247.status_endpoint') : null);
            if (!$url) { return $tx; }
            $key = $provider?->api_key ?: ($datasub ? app(Datasub247Service::class)->getApiKey() : null);
            $params = (array) ($cfg['status_static_payload'] ?? []);
            $params[$cfg['status_reference_key'] ?? ($datasub ? 'request-id' : 'request_id')] = $tx->provider_reference ?: $tx->transaction_id;
            $headers = array_merge(['Accept' => 'application/json', 'Authorization' => ($datasub ? 'Token ' : 'Bearer ').$key], (array) ($provider?->headers ?? []));
            $method = strtoupper($cfg['status_method'] ?? 'GET');
            $response = Http::withHeaders($headers)->timeout(30)->send($method, $url, [$method === 'GET' ? 'query' : 'json' => $params]);
            if (!$response->successful() || !is_array($json = $response->json())) { return $tx; }
            $json = app(VtuHubService::class)->normalizeResponse($json, $cfg);
            return app(VtuSettlementService::class)->settle($tx, VtuProviderStatus::state($json, $cfg), $json);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('VTU reconciliation deferred', ['reference' => $tx->transaction_id]);
            return $tx->fresh();
        } finally { $lock->release(); }
    }
}
