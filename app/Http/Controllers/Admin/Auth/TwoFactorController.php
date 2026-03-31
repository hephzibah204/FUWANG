<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminAuditLog;
use App\Utils\TOTP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->session()->has('admin_2fa_pending')) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.2fa');
    }

    public function verify(Request $request)
    {
        if (!$request->session()->has('admin_2fa_pending')) {
            return redirect()->route('admin.login');
        }

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $adminId = $request->session()->get('admin_2fa_id');
        $admin = Admin::find($adminId);

        if (!$admin || !$admin->two_factor_enabled) {
            return redirect()->route('admin.login');
        }

        $throttleKey = 'admin-2fa:' . $request->ip() . '|' . $admin->id;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'code' => [trans('auth.throttle', ['seconds' => $seconds])],
            ]);
        }

        if (TOTP::verify($admin->two_factor_secret, $request->code)) {
            RateLimiter::clear($throttleKey);
            
            Auth::guard('admin')->login($admin);
            
            $request->session()->forget('admin_2fa_pending');
            $request->session()->forget('admin_2fa_id');
            $request->session()->regenerate();

            AdminAuditLog::create([
                'admin_id' => $admin->id,
                'action' => 'admin.login.success.2fa',
                'meta' => ['email' => $admin->email],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->intended(route('admin.dashboard'));
        }

        RateLimiter::hit($throttleKey, 300); // 5 minutes lock

        throw ValidationException::withMessages([
            'code' => ['The provided two-factor authentication code was invalid.'],
        ]);
    }
}
