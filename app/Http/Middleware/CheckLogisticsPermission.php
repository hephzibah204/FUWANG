<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckLogisticsPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = Auth::guard('logistics_staff')->user();
        if (! $user || ! ($user instanceof \App\Models\LogisticsStaff) || ! $user->is_active) {
            return redirect()->route('logistics.ops.login');
        }

        if (! $user->hasPermission($permission)) {
            abort(403);
        }

        return $next($request);
    }
}

