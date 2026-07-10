<?php

namespace App\Http\Controllers\Auth\Logistics;

use App\Http\Controllers\Controller;
use App\Support\NigeriaLocations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LogisticsAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('logistics.auth.login');
    }

    public function showRegisterForm()
    {
        return view('logistics.auth.register', [
            'nigeriaStates' => NigeriaLocations::stateNames(),
        ]);
    }

    public function login(Request $request)
    {
        $request->merge([
            'service' => 'logistics',
        ]);

        return app(\App\Http\Controllers\Auth\LoginController::class)->login($request);
    }

    public function register(Request $request)
    {
        $request->merge([
            'service' => 'logistics',
        ]);

        return app(\App\Http\Controllers\Auth\RegisterController::class)->register($request);
    }

    public function ssoLogin(Request $request)
    {
        try {
            if (!Auth::check()) {
                return redirect()->to('/logistics/login');
            }

            $user = Auth::user();

            if (($user?->user_status ?? 'active') !== 'active') {
                Auth::logout();
                return redirect()->to('/logistics/login')->with('error', 'Your account is currently not active. Please contact support.');
            }

            if (Schema::hasTable('logistics_profiles')) {
                \App\Models\LogisticsProfile::query()->firstOrCreate([
                    'user_id' => $user->id,
                ], [
                    'contact_person' => $user->fullname,
                    'email' => $user->email,
                    'is_active' => true,
                ]);
            } else {
                Log::warning('Skipping logistics profile creation during logistics SSO because logistics_profiles table is missing.', [
                    'user_id' => $user->id,
                ]);
            }

            return redirect()->route('logistics.dashboard');
        } catch (\Throwable $e) {
            Log::error('Logistics SSO login error', [
                'user_id' => Auth::id(),
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);
            return redirect()->to('/logistics/login')->with('error', 'SSO authentication failed.');
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user instanceof \App\Models\User) {
            $user->update(['online_status' => 'offline']);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/logistics');
    }
}
