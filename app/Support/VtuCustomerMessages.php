<?php

namespace App\Support;

final class VtuCustomerMessages
{
    public const LOW_BALANCE = 'System balance is low. Please contact admin.';
    public const PENDING = 'Transaction awaiting confirmation. Do not repeat this purchase.';

    public static function lowBalanceRegex(): string
    {
        return '/\b(?:insufficient|inadequate|low)\s+(?:(?:api|system|account|wallet)\s+)?(?:balance|funds?|wallet)\b|\b(?:balance|funds?|wallet)\s+(?:is\s+|are\s+)?(?:too\s+)?(?:low|insufficient)\b|\b(?:not\s+enough|don\'?t\s+have\s+enough|do\s+not\s+have\s+enough)\s+(?:(?:wallet|account|api)\s+)?(?:balance|funds?)\b|\b(?:fund|top\s*up|recharge)\s+(?:your\s+)?(?:wallet|account)\b|\bcannot\s+(?:fund|cover)\b|\b(?:system|api|wallet)[_-]balance[_-]low\b/i';
    }

    public static function balanceLow(mixed $payload): bool
    {
        if (is_string($payload)) {
            $text = str_replace(['_', '-'], ' ', strtolower($payload));
            return (bool) preg_match(self::lowBalanceRegex(), $text);
        }

        if (!is_array($payload)) {
            return false;
        }

        if (($payload['_failure_code'] ?? '') === 'system_balance_low') {
            return true;
        }

        // Never mark a confirmed successful delivery or an active in-flight processing transaction as a low-balance failure
        foreach (['status', 'Status', 'success', 'data.status', 'data.success'] as $key) {
            $value = data_get($payload, $key);
            if ($value === true || in_array(strtolower(trim(is_scalar($value) ? (string) $value : '')), [
                'true', '1', 'success', 'successful', 'completed', 'delivered', 'pending', 'processing', 'queued',
            ], true)) {
                return false;
            }
        }

        foreach (['code', 'error_code', 'message', 'msg', 'error', 'error_message', 'description', 'detail', 'data.message', 'data.error', 'response', 'raw'] as $key) {
            $value = data_get($payload, $key);
            if (is_array($value)) {
                $value = implode(' ', array_filter(\Illuminate\Support\Arr::flatten($value), 'is_string'));
            }
            if (!is_string($value)) {
                continue;
            }
            $text = str_replace(['_', '-'], ' ', strtolower($value));
            if (preg_match(self::lowBalanceRegex(), $text)) {
                return true;
            }
        }

        return false;
    }

    public static function publicMessage(string $message): string
    {
        if (self::balanceLow($message)) {
            return self::LOW_BALANCE;
        }
        if (preg_match('/datasub(?:247)?/i', $message)) {
            return 'This service is currently unavailable. Please contact admin.';
        }
        return str_ireplace('Awaiting provider confirmation. Do not repeat this purchase.', self::PENDING, $message);
    }

    public static function confirmedBalanceFailure(mixed $payload): bool
    {
        return is_array($payload) && self::balanceLow($payload)
            && (($payload['_failure_code'] ?? '') === 'system_balance_low' || VtuProviderStatus::state($payload) === 'failed');
    }

    public static function deliveryData(array $raw): array
    {
        $data = [];
        foreach (['network', 'dataplan', 'plan_name', 'quantity', 'phone_number', 'iuc', 'meter_number', 'meter_type', 'token', 'pin', 'pins', 'serial', 'load_pin', 'check_balance', 'units'] as $key) {
            $value = $raw[$key] ?? data_get($raw, 'data.'.$key);
            if ($value !== null) $data[$key] = $value;
        }
        return $data;
    }
}
