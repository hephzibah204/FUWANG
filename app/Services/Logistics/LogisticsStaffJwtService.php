<?php

namespace App\Services\Logistics;

use App\Models\LogisticsStaff;
use App\Models\LogisticsStaffJwtSession;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;

class LogisticsStaffJwtService
{
    public function issueToken(LogisticsStaff $staff): array
    {
        $ttl = (int) (config('services.logistics.jwt_ttl_seconds') ?? 3600);
        if ($ttl < 60) {
            $ttl = 60;
        }

        $now = time();
        $exp = $now + $ttl;
        $jti = Str::uuid()->toString();

        $payload = [
            'iss' => config('app.url'),
            'aud' => 'logistics_ops',
            'sub' => (string) $staff->id,
            'iat' => $now,
            'exp' => $exp,
            'jti' => $jti,
            'role' => $staff->getRoleNames()->first(),
        ];

        $token = JWT::encode($payload, $this->secret(), 'HS256');

        LogisticsStaffJwtSession::query()->create([
            'logistics_staff_id' => $staff->id,
            'jti' => hash('sha256', $jti),
            'expires_at' => now()->setTimestamp($exp),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return [
            'token' => $token,
            'expires_at' => $exp,
        ];
    }

    public function decode(string $token): object
    {
        return JWT::decode($token, new Key($this->secret(), 'HS256'));
    }

    public function secret(): string
    {
        $raw = (string) (config('services.logistics.jwt_secret') ?? '');
        if ($raw !== '') {
            return $raw;
        }

        $key = (string) config('app.key', '');
        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);
            if (is_string($decoded) && $decoded !== '') {
                return hash('sha256', $decoded);
            }
        }

        return hash('sha256', $key);
    }

    public function isActiveSession(string $jti): bool
    {
        $hash = hash('sha256', $jti);

        return LogisticsStaffJwtSession::query()
            ->where('jti', $hash)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function revoke(string $jti): void
    {
        LogisticsStaffJwtSession::query()
            ->where('jti', hash('sha256', $jti))
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }
}

