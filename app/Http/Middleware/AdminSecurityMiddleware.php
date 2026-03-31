<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminSecurityMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return redirect()->route('admin.login');
        }

        $isSuperadmin = ($admin->is_super_admin ?? false) || (($admin->role ?? null) === 'superadmin');
        $isSingleAdminInstance = Admin::count() <= 1;

        if (!$isSuperadmin && !$isSingleAdminInstance) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => false, 'message' => 'Forbidden'], 403);
            }

            return redirect()->back()->with('error', 'Forbidden');
        }

        return $next($request);
    }
}
