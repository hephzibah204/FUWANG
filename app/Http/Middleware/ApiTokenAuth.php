<?php

namespace App\Http\Middleware;

use App\Exceptions\AuthenticationException;
use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractToken($request);
        if (!$token) {
            throw new AuthenticationException('Missing API token.', 'Missing API token.');
        }

        $tokenHash = hash('sha256', $token);
        $apiToken = ApiToken::query()->where('token_hash', $tokenHash)->first();

        if (!$apiToken) {
            throw new AuthenticationException('Invalid API token.', 'Invalid API token.');
        }

        if ($apiToken->revoked_at) {
            throw new AuthenticationException('API token revoked.', 'API token revoked.');
        }

        if ($apiToken->expires_at && $apiToken->expires_at->isPast()) {
            throw new AuthenticationException('API token expired.', 'API token expired.');
        }

        $user = $apiToken->user;
        if (!$user) {
            throw new AuthenticationException('API token user not found.', 'API token user not found.');
        }

        $minBalance = (float) \App\Models\SystemSetting::get('api_min_wallet_balance', 100.0);
        $userBalance = (float) ($user->balance->user_balance ?? 0.0);

        if ($userBalance < $minBalance) {
            // Do NOT revoke the token — the user may top up and retry.
            // Notify them via a 402 so they know to fund their wallet.
            return response()->json([
                'status'  => false,
                'message' => 'Insufficient wallet balance. Please fund your account to continue using the API.',
                'error'   => 'fund_not_sufficient',
                'minimum' => $minBalance,
            ], 402);
        }

        Auth::setUser($user);
        $request->attributes->set('api_token', $apiToken);
        $apiToken->forceFill(['last_used_at' => now()])->save();

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $header = (string) ($request->header('Authorization') ?? '');
        if (stripos($header, 'Bearer ') === 0) {
            $value = trim(substr($header, 7));
            return $this->normalizeToken($value);
        }

        $value = (string) ($request->header('X-Api-Token') ?? $request->header('X-API-Token') ?? '');
        $value = trim($value);
        if ($value !== '') {
            return $this->normalizeToken($value);
        }

        return null;
    }

    private function normalizeToken(string $token): string
    {
        $token = trim($token);
        if (str_starts_with($token, 'nx_')) {
            return substr($token, 3);
        }
        return $token;
    }
}

