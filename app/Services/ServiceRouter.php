<?php

namespace App\Services;

use App\Models\CustomApi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ServiceRouter
{
    protected string $serviceType;
    protected array $providers = [];

    public function __construct(string $serviceType)
    {
        $this->serviceType = $serviceType;
        $this->loadProviders();
    }

    public static function for(string $serviceType): self
    {
        return new static($serviceType);
    }

    protected function loadProviders(): void
    {
        $this->providers = CustomApi::where('service_type', $this->serviceType)
            ->where('status', true)
            ->orderBy('priority', 'asc')
            ->get()
            ->all();
    }

    /**
     * Get all active providers for this service
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * Attempt to verify using providers in order of priority
     */
    public function verify(array $payload, ?int $providerId = null): array
    {
        if ($providerId) {
            $specificProvider = collect($this->providers)->firstWhere('id', $providerId);
            if ($specificProvider) {
                return $this->callProvider($specificProvider, $payload);
            }
        }

        if (empty($this->providers)) {
            throw new \App\Exceptions\ServiceNotConfiguredException('No active providers found for ' . $this->serviceType);
        }

        $errors = [];
        foreach ($this->providers as $provider) {
            try {
                $response = $this->callProvider($provider, $payload);
                if ($response['status']) {
                    return $response;
                }
                $errors[] = "{$provider->name}: " . ($response['message'] ?? 'Unknown error');
            } catch (\Exception $e) {
                $errors[] = "{$provider->name}: " . $e->getMessage();
                Log::error("Provider Failover: {$provider->name} failed.", ['error' => $e->getMessage()]);
            }
        }

        return [
            'status' => false, 
            'message' => 'All providers failed: ' . implode(' | ', $errors)
        ];
    }

    protected function callProvider(CustomApi $provider, array $payload): array
    {
        $http = Http::timeout($provider->timeout_seconds ?: 45);
        
        $headers = $provider->headers ?: [];
        $apiKey = $provider->api_key;
        $endpoint = $provider->endpoint;

        // Custom provider-specific logic
        if ($provider->provider_identifier === 'robosttech') {
            $headers['api-key'] = $apiKey;
            $headers['Content-Type'] = 'application/json';
        } elseif ($provider->provider_identifier === 'dataverify') {
            $payload['api_key'] = $apiKey;
        }

        if (!empty($headers)) {
            $http = $http->withHeaders($headers);
        }

        $response = $http->post($endpoint, $payload);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'status' => true,
                'data' => $data,
                'provider' => $provider->name,
                'provider_id' => $provider->id
            ];
        }

        return [
            'status' => false,
            'message' => $response->json()['message'] ?? $response->json()['detail'] ?? 'Provider connection error',
            'provider' => $provider->name
        ];
    }
}
