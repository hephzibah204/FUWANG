<?php

namespace App\Http\Middleware;

use App\Models\LogisticsStaff;
use App\Services\Logistics\LogisticsStaffJwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogisticsStaffJwtAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if (! $token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authorization token required.',
            ], 401);
        }

        try {
            $jwt = app(LogisticsStaffJwtService::class)->decode($token);
        } catch (\Throwable) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid token.',
            ], 401);
        }

        $jti = isset($jwt->jti) ? (string) $jwt->jti : '';
        $sub = isset($jwt->sub) ? (string) $jwt->sub : '';
        if ($jti === '' || $sub === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid token payload.',
            ], 401);
        }

        $service = app(LogisticsStaffJwtService::class);
        if (! $service->isActiveSession($jti)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token expired or revoked.',
            ], 401);
        }

        $staff = LogisticsStaff::query()->find((int) $sub);
        if (! $staff || ! $staff->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account not active.',
            ], 403);
        }

        Auth::guard('logistics_staff')->setUser($staff);

        $request->attributes->set('jwt', $jwt);
        $request->attributes->set('logistics_staff', $staff);

        return $next($request);
    }
}

