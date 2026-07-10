<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckLogisticsPermissionApi
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = Auth::guard('logistics_staff')->user();
        if (! $user || ! ($user instanceof \App\Models\LogisticsStaff) || ! $user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication required.',
            ], 401);
        }

        if (! $user->hasPermission($permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden.',
            ], 403);
        }

        return $next($request);
    }
}

