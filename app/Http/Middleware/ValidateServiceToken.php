<?php

namespace App\Http\Middleware;

use App\Models\ServiceSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateServiceToken
{
    public function handle(Request $request, Closure $next, string $service): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service token required.',
            ], 401);
        }

        $ssoBridge = app(\App\Services\Auth\SSOBridgeService::class);
        $sessionData = $ssoBridge->validateServiceToken($token, $service);

        if (!$sessionData) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired service token.',
            ], 401);
        }

        $request->merge([
            'service_user_id' => $sessionData['user_id'],
            'service_session_id' => $sessionData['session_id'],
            'service_scopes' => $sessionData['scopes'],
        ]);

        return $next($request);
    }
}