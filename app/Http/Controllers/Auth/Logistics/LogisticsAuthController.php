<?php

namespace App\Http\Controllers\Auth\Logistics;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\SSOBridgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LogisticsAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('logistics.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('logistics.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegisterForm()
    {
        return view('logistics.auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'fullname' => $validated['fullname'],
            'email' => $validated['email'],
            'username' => User::generateUniqueUsername($validated['email']),
            'password' => Hash::make($validated['password']),
            'user_status' => 'active',
            'email_verified_at' => now(),
        ]);

        Auth::login($user);

        return redirect()->route('logistics.dashboard');
    }

    public function ssoLogin(Request $request, SSOBridgeService $ssoService)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated'], 401);
        }

        $token = $ssoService->generateServiceToken($user, 'logistics');

        return response()->json([
            'status' => true,
            'sso_token' => $token,
            'redirect_url' => route('logistics.dashboard', ['sso_token' => $token]),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('logistics.home');
    }
}
