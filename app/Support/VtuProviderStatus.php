<?php

namespace App\Support;

final class VtuProviderStatus
{
    public static function state(mixed $payload, array $config = []): string
    {
        if (!is_array($payload) || !$payload) {
            return 'pending';
        }
        if (!empty($config['status_path'])) {
            $value = data_get($payload, $config['status_path']);
            if (in_array($value, $config['failure_values'] ?? [], true)) { return 'failed'; }
            if (in_array($value, $config['success_values'] ?? [], true)) { return 'success'; }
            return 'pending';
        }
        $states = [];
        foreach (['status', 'Status', 'success'] as $key) {
            if (!array_key_exists($key, $payload)) {
                continue;
            }
            $value = strtolower(trim(is_bool($payload[$key]) ? ($payload[$key] ? 'true' : 'false') : (is_scalar($payload[$key]) ? (string) $payload[$key] : '')));
            if (in_array($value, ['false', '0', 'fail', 'failed', 'failure', 'error', 'api_error', 'declined', 'cancelled', 'rejected', 'invalid_credentials', 'invalid_dataplan'], true)) {
                return 'failed';
            }
            $states[] = in_array($value, ['true', '1', 'success', 'successful', 'completed', 'delivered'], true) ? 'success' : 'pending';
        }
        if (!$states && ($payload['code'] ?? null) === '000') { return 'success'; }
        return $states && !in_array('pending', $states, true) ? 'success' : 'pending';
    }
}
