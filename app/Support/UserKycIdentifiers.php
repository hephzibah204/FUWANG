<?php

namespace App\Support;

use App\Models\User;
use App\Models\VerificationResult;

/**
 * Resolves BVN / NIN from successful identity verifications for payment APIs.
 */
final class UserKycIdentifiers
{
    private const BVN_SERVICE_TYPES = [
        'bvn',
        'bvn_verification',
        'bvn_matching',
        'bvn_nin_phone_verification',
        'kyc_tier_bvn',
    ];

    private const NIN_SERVICE_TYPES = [
        'nin',
        'nin_verification',
        'nin_face_verification',
        'kyc_tier_nin',
    ];

    /**
     * @return array{bvn: ?string, nin: ?string} 11-digit values only
     */
    public static function forPaymentKyc(User $user): array
    {
        return [
            'bvn' => self::verifiedBvn($user),
            'nin' => self::verifiedNin($user),
        ];
    }

    /**
     * Returns the most recent successful identity to send to payment gateways.
     *
     * @return array{type: 'bvn'|'nin', value: string}|null
     */
    public static function preferredPaymentIdentity(User $user): ?array
    {
        $rows = VerificationResult::query()
            ->where('user_id', $user->id)
            ->where('status', 'success')
            ->whereIn('service_type', array_merge(self::BVN_SERVICE_TYPES, self::NIN_SERVICE_TYPES))
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        foreach ($rows as $row) {
            if (in_array($row->service_type, self::BVN_SERVICE_TYPES, true)) {
                $value = self::extractDigits11FromPayload($row->response_data, ['bvn', 'BVN']);
                if ($value === null) {
                    $value = self::digits11($row->identifier);
                }
                if ($value !== null) {
                    return ['type' => 'bvn', 'value' => $value];
                }
            }

            if (in_array($row->service_type, self::NIN_SERVICE_TYPES, true)) {
                $value = self::extractDigits11FromPayload($row->response_data, [
                    'nin', 'NIN', 'nationalId', 'national_id', 'nimc_nin', 'nin_number',
                ]);
                if ($value === null) {
                    $value = self::digits11($row->identifier);
                }
                if ($value !== null && ! self::isLikelyNgPhone11($value)) {
                    return ['type' => 'nin', 'value' => $value];
                }
            }
        }

        $bvn = self::verifiedBvn($user);
        if ($bvn !== null) {
            return ['type' => 'bvn', 'value' => $bvn];
        }
        $nin = self::verifiedNin($user);
        if ($nin !== null) {
            return ['type' => 'nin', 'value' => $nin];
        }

        return null;
    }

    public static function verifiedBvn(User $user): ?string
    {
        $rows = VerificationResult::query()
            ->where('user_id', $user->id)
            ->where('status', 'success')
            ->whereIn('service_type', self::BVN_SERVICE_TYPES)
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        foreach ($rows as $row) {
            $fromPayload = self::extractDigits11FromPayload($row->response_data, ['bvn', 'BVN']);
            if ($fromPayload !== null) {
                return $fromPayload;
            }

            $strictTypes = ['bvn', 'bvn_verification', 'bvn_matching'];
            if (in_array($row->service_type, $strictTypes, true)) {
                $id = self::digits11($row->identifier);
                if ($id !== null) {
                    return $id;
                }
            }
        }

        return null;
    }

    public static function verifiedNin(User $user): ?string
    {
        $rows = VerificationResult::query()
            ->where('user_id', $user->id)
            ->where('status', 'success')
            ->whereIn('service_type', self::NIN_SERVICE_TYPES)
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        foreach ($rows as $row) {
            $fromPayload = self::extractDigits11FromPayload($row->response_data, [
                'nin', 'NIN', 'nationalId', 'national_id', 'nimc_nin', 'nin_number',
            ]);
            if ($fromPayload !== null) {
                return $fromPayload;
            }

            $id = self::digits11($row->identifier);
            if ($id !== null && ! self::isLikelyNgPhone11($id)) {
                return $id;
            }
        }

        return null;
    }

    private static function isLikelyNgPhone11(string $digits): bool
    {
        return strlen($digits) === 11 && $digits[0] === '0';
    }

    private static function digits11(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $d = preg_replace('/\D/', '', $value);

        return strlen($d) === 11 ? $d : null;
    }

    /**
     * @param  array<string, mixed>|null  $data
     * @param  list<string>  $keys
     */
    private static function extractDigits11FromPayload(?array $data, array $keys): ?string
    {
        if ($data === null || $data === []) {
            return null;
        }

        foreach ($keys as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }
            $raw = $data[$key];
            if (is_string($raw) || is_int($raw) || is_float($raw)) {
                $d = self::digits11((string) $raw);
                if ($d !== null) {
                    return $d;
                }
            }
        }

        foreach (['response', 'data', 'result'] as $nested) {
            if (isset($data[$nested]) && is_array($data[$nested])) {
                $d = self::extractDigits11FromPayload($data[$nested], $keys);
                if ($d !== null) {
                    return $d;
                }
            }
        }

        return null;
    }
}
