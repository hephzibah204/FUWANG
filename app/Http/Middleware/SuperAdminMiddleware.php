<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if ($admin && $admin->is_super_admin) {
            // Ensure a matching User account exists for testing
            $this->ensureUserForAdmin($admin);
            return $next($request);
        }

        $message = 'Unauthorized access. Only Super Admins can perform this action.';

        if ($request->expectsJson()) {
            return response()->json(['status' => false, 'message' => $message], 403);
        }

        return redirect()->route('admin.dashboard')->with('error', $message);
    }

    private function ensureUserForAdmin($admin)
    {
        return User::firstOrCreate(
            ['email' => $admin->email],
            [
                'fullname' => $admin->fullname ?? $admin->username,
                'username' => $admin->username ?? explode('@', $admin->email)[0],
                'password' => Hash::make(Str::random(16)),
                'user_status' => 'active',
                'kyc_tier' => 3,
            ]
        );
    }
}
