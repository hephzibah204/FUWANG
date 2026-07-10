<?php

namespace App\Support;

/**
 * Short, safe snippets from payment gateway JSON for end-user / admin messages.
 */
final class PaymentApiUserMessage
{
    public static function shorten(string $message, int $max = 220): string
    {
        $message = preg_replace('/\s+/', ' ', trim($message));

        return strlen($message) > $max ? substr($message, 0, $max - 1).'…' : $message;
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    public static function monnify(?array $json, string $fallback): string
    {
        if (! is_array($json)) {
            return $fallback;
        }
        if (array_key_exists('requestSuccessful', $json) && $json['requestSuccessful'] === false) {
            $msg = (string) ($json['responseMessage'] ?? $json['message'] ?? $fallback);

            return self::shorten($msg !== '' ? $msg : $fallback);
        }

        // HTTP error bodies sometimes omit requestSuccessful
        $msg = (string) ($json['responseMessage'] ?? $json['message'] ?? '');
        if ($msg !== '') {
            return self::shorten($msg);
        }

        return $fallback;
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    public static function flutterwave(?array $json, string $fallback): string
    {
        if (! is_array($json)) {
            return $fallback;
        }
        $data = $json['data'] ?? null;
        $msg = (string) ($json['message'] ?? (is_array($data) ? ($data['message'] ?? '') : ''));
        if ($msg !== '') {
            return self::shorten($msg);
        }

        return $fallback;
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    public static function paystack(?array $json, string $fallback): string
    {
        if (! is_array($json)) {
            return $fallback;
        }
        $msg = (string) ($json['message'] ?? '');
        if ($msg !== '') {
            return self::shorten($msg);
        }

        return $fallback;
    }
}
