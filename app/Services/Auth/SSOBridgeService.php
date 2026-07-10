<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\ServiceSession;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SSOBridgeService
{
    private const SERVICE_TOKEN_EXPIRY = 3600;

    public function generateServiceToken(User $user, string $service, array $scopes = []): string
    {
        $token = Str::random(64);

        ServiceSession::create([
            'user_id' => $user->id,
            'service' => $service,
            'token' => hash('sha256', $token),
            'scopes' => json_encode($scopes),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'expires_at' => now()->addSeconds(self::SERVICE_TOKEN_EXPIRY),
        ]);

        return $token;
    }

    public function validateServiceToken(string $token, string $service): ?array
    {
        $hashedToken = hash('sha256', $token);
        $session = ServiceSession::where('token', $hashedToken)
            ->where('service', $service)
            ->where('expires_at', '>', now())
            ->first();

        if (!$session) {
            Log::channel('security')->warning('Invalid or expired service token.', [
                'service' => $service,
                'ip' => request()->ip(),
                'token_hash_prefix' => substr($hashedToken, 0, 12),
            ]);
            return null;
        }

        return [
            'user_id' => $session->user_id,
            'scopes' => json_decode($session->scopes, true),
            'session_id' => $session->id,
        ];
    }

    public function authenticateWithCredentials(string $email, string $password, string $service): ?array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        if ($user->user_status !== 'active') {
            return null;
        }

        $token = $this->generateServiceToken($user, $service, ['read', 'profile']);

        return [
            'token' => $token,
            'user' => $this->filterUserForService($user, $service),
            'redirect' => $this->getServiceRedirect($service),
        ];
    }

    public function authenticateExistingUser(User $user, string $service): ?array
    {
        if ($user->user_status !== 'active') {
            return null;
        }

        $token = $this->generateServiceToken($user, $service, ['read', 'profile']);

        return [
            'token' => $token,
            'user' => $this->filterUserForService($user, $service),
            'redirect' => $this->getServiceRedirect($service),
        ];
    }

    public function revokeServiceToken(string $token, string $service): bool
    {
        $hashedToken = hash('sha256', $token);
        $revoked = ServiceSession::where('token', $hashedToken)
            ->where('service', $service)
            ->delete() > 0;

        if (! $revoked) {
            Log::channel('security')->warning('Service token revocation missed.', [
                'service' => $service,
                'ip' => request()->ip(),
                'token_hash_prefix' => substr($hashedToken, 0, 12),
            ]);
        }

        return $revoked;
    }

    public function revokeAllUserServiceTokens(int $userId, string $service): int
    {
        return ServiceSession::where('user_id', $userId)
            ->where('service', $service)
            ->delete();
    }

    private function filterUserForService(User $user, string $service): array
    {
        $baseData = [
            'id' => $user->id,
            'email' => $user->email,
            'fullname' => $user->fullname,
            'username' => $user->username,
        ];

        return match($service) {
            'logistics' => array_merge($baseData, [
                'logistics_profile' => $user->logisticsProfile,
            ]),
            default => $baseData,
        };
    }

    private function getServiceRedirect(string $service): string
    {
        return match($service) {
            'logistics' => '/logistics/dashboard',
            default => '/dashboard',
        };
    }

    public function getUserByServiceToken(string $token, string $service): ?User
    {
        $sessionData = $this->validateServiceToken($token, $service);

        if (!$sessionData) {
            return null;
        }

        return User::find($sessionData['user_id']);
    }
}
