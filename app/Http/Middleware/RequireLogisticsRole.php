<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireLogisticsRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = Auth::guard('logistics_staff')->user();
        if (! $user || ! ($user instanceof \App\Models\LogisticsStaff) || ! $user->is_active) {
            return redirect()->route('logistics.ops.login');
        }

        if (! $user->hasRole($role)) {
            abort(403);
        }

        return $next($request);
    }
}

