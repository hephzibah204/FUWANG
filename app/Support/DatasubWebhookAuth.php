<?php

namespace App\Support;

use Illuminate\Http\Request;

final class DatasubWebhookAuth
{
    public static function valid(Request $request): bool
    {
        $secret = (string) config('services.datasub247.webhook_secret');
        if ($secret === '') {
            return true;
        }
        // Dashboard callbacks can use a secret in their URL when custom headers
        // are unavailable. Never trust credentials supplied in the JSON body.
        $provided = $request->header('X-Webhook-Secret') ?: $request->bearerToken() ?: $request->query('token');
        return is_string($provided) && hash_equals($secret, $provided);
    }
}
