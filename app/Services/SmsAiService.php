<?php

namespace App\Services;

use App\Models\ApiCenter;
use Illuminate\Support\Facades\Http;

class SmsAiService
{
    public function send(string $to, string $message, ?string $sender = null): array
    {
        $apiCenter = ApiCenter::first();
        $key = $apiCenter?->sms_ai_key;
        $endpoint = $apiCenter?->sms_ai_endpoint;
        $sender = $sender ?: ($apiCenter?->sms_ai_sender);

        if (!$key || !$endpoint) {
            return ['ok' => false, 'message' => 'SMS AI is not configured.'];
        }

        try {
            $res = Http::timeout(45)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $key,
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, [
                    'to' => $to,
                    'message' => $message,
                    'sender' => $sender,
                ]);

            if (!$res->successful()) {
                return ['ok' => false, 'message' => 'SMS provider error.'];
            }

            return ['ok' => true, 'data' => $res->json()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'SMS service unavailable.'];
        }
    }
}

