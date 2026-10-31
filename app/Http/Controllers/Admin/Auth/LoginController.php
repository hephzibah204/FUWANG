<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $throttleKey = 'admin-login:' . $request->ip() . '|' . $request->email;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $admin = Admin::where('email', $request->email)->first();

            AdminAuditLog::create([
                'admin_id' => $admin?->id,
                'action' => 'admin.login.throttled',
                'meta' => ['email' => $request->email, 'seconds' => $seconds],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            throw ValidationException::withMessages([
                'email' => [trans('auth.throttle', ['seconds' => $seconds])],
            ]);
        }

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $admin = Auth::guard('admin')->user();

            if ($admin->two_factor_enabled) {
                $request->session()->put('admin_2fa_pending', true);
                $request->session()->put('admin_2fa_id', $admin->id);
                Auth::guard('admin')->logout();
                return redirect()->route('admin.2fa.index');
            }

            AdminAuditLog::create([
                'admin_id' => $admin->id,
                'action' => 'admin.login.success',
                'meta' => ['email' => $request->email],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.dashboard');
        }

        RateLimiter::hit($throttleKey, 600); // 10 minutes lock if exceeded

        $admin = Admin::where('email', $request->email)->first();

        AdminAuditLog::create([
            'admin_id' => $admin?->id,
            'action' => 'admin.login.failed',
            'meta' => ['email' => $request->email],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function show2faForm(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        if (!$request->session()->get('admin_2fa_pending') || !$request->session()->get('admin_2fa_id')) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.2fa');
    }

    public function verify2fa(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        if (!$request->session()->get('admin_2fa_pending') || !$request->session()->get('admin_2fa_id')) {
            return redirect()->route('admin.login');
        }

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $admin = Admin::find($request->session()->get('admin_2fa_id'));

        if (!$admin || !$admin->two_factor_enabled || empty($admin->two_factor_secret)) {
            $request->session()->forget(['admin_2fa_pending', 'admin_2fa_id']);
            return redirect()->route('admin.login')->withErrors(['email' => 'Session expired or 2FA not configured.']);
        }

        if (!\App\Utils\TOTP::verify($admin->two_factor_secret, $request->code)) {
            return back()->withErrors(['code' => 'The provided authentication code is invalid.'])->withInput();
        }

        $request->session()->forget(['admin_2fa_pending', 'admin_2fa_id']);
        $request->session()->regenerate();

        Auth::guard('admin')->login($admin);

        AdminAuditLog::create([
            'admin_id' => $admin->id,
            'action' => 'admin.login.2fa_success',
            'meta' => ['email' => $admin->email],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.dashboard');
    }
}
