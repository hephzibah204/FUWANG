<?php

namespace App\Services;

use App\Models\CustomApi;
use Illuminate\Support\Facades\Http;

class SmsService
{
    public function send(CustomApi $provider, string $phone, string $message, ?string $senderId = null): array
    {
        $headers = is_array($provider->headers) ? $provider->headers : [];
        $config = is_array($provider->config) ? $provider->config : [];

        $payload = is_array($config['payload'] ?? null) ? $config['payload'] : [];
        $toKey = $config['to_key'] ?? 'to';
        $msgKey = $config['message_key'] ?? 'message';
        $senderKey = $config['sender_key'] ?? 'from';

        $payload[$toKey] = $phone;
        $payload[$msgKey] = $message;
        if ($senderId && $senderKey) {
            $payload[$senderKey] = $senderId;
        }
        if (!empty($provider->api_key) && !empty($config['api_key_key'] ?? null)) {
            $payload[$config['api_key_key']] = $provider->api_key;
        }
        if (!empty($provider->secret_key) && !empty($config['secret_key_key'] ?? null)) {
            $payload[$config['secret_key_key']] = $provider->secret_key;
        }

        $timeout = (int) ($provider->timeout_seconds ?? 60);
        $http = Http::timeout($timeout);
        if (!empty($headers)) {
            $http = $http->withHeaders($headers);
        }

        $resp = $http->post($provider->endpoint, $payload);

        return [
            'ok' => $resp->successful(),
            'status_code' => $resp->status(),
            'body' => $resp->body(),
            'json' => $resp->json(),
        ];
    }
}

