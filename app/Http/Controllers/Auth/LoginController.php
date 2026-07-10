<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class LoginController extends Controller
{
    private const USER_2FA_PENDING = 'user_2fa_pending';
    private const USER_2FA_ID = 'user_2fa_id';
    private const USER_2FA_REMEMBER = 'user_2fa_remember';

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $wantsJson = $request->expectsJson() || $request->ajax() || $request->wantsJson();
        $ipAddress = $request->ip();
        $service = $request->input('service');
        $service = is_string($service) ? $service : null;

        if (! app()->environment('local')) {
            if (Schema::hasTable('login_attempts')) {
                $attempts = DB::table('login_attempts')->where('ip_address', $ipAddress)->first();
                if ($attempts && (int) $attempts->attempts >= 6 && $attempts->last_login && now()->diffInMinutes($attempts->last_login) < 15) {
                    $payload = [
                        'status' => 'error',
                        'message' => 'Your device is temporarily locked due to multiple failed login attempts. Please try again later.',
                    ];

                    return $wantsJson ? response()->json($payload, 429) : back()->withErrors(['email' => $payload['message']])->withInput();
                }
            }
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, false)) {
            $request->session()->regenerate();

            $user = Auth::user();

            $status = $user?->user_status ?? 'active';
            if ($status !== 'active') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $payload = [
                    'status' => 'error',
                    'message' => 'Your account is currently ' . $status . '. Please contact support.',
                ];
                return $wantsJson ? response()->json($payload, 403) : back()->withErrors(['email' => $payload['message']])->withInput();
            }
            
            // Set custom session variables to maintain compatibility
            session([
                'fullname' => $user->fullname,
                'transaction_pin' => $user->transaction_pin,
            ]);

            // Clear login attempts
            if (Schema::hasTable('login_attempts')) {
                DB::table('login_attempts')->where('ip_address', $ipAddress)->delete();
            }

            if (! empty($user->google2fa_secret)) {
                $this->setTwoFactorPending($request, $user->id, $remember);
                Auth::logout();

                if ($wantsJson) {
                    return response()->json([
                        'status' => '2fa_required',
                        'message' => 'Two-factor authentication code required.',
                        'redirect' => route('2fa.challenge'),
                    ]);
                }

                return redirect()->route('2fa.challenge');
            }

            Auth::login($user, $remember);
            Auth::logoutOtherDevices($request->password);

            // Update online status
            if ($user instanceof User) {
                $user->update(['online_status' => 'online']);
            }

            if ($service === 'logistics') {
                if (Schema::hasTable('logistics_profiles')) {
                    \App\Models\LogisticsProfile::query()->firstOrCreate([
                        'user_id' => $user->id,
                    ], [
                        'contact_person' => $user->fullname,
                        'email' => $user->email,
                        'is_active' => true,
                    ]);
                } else {
                    Log::warning('Skipping logistics profile creation during login because logistics_profiles table is missing.', [
                        'user_id' => $user->id,
                        'service' => $service,
                    ]);
                }
                session(['url.intended' => route('logistics.dashboard')]);
            }

            if ($service === 'auctions') {
                session(['url.intended' => route('auction.dashboard')]);
            }

            $redirect = ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                ? route('verification.notice')
                : ($service === 'logistics'
                    ? route('logistics.dashboard')
                    : ($service === 'auctions' ? route('auction.dashboard') : route('dashboard')));
            if ($wantsJson) {
                return response()->json([
                    'status' => 'success',
                    'redirect' => $redirect,
                ]);
            }

            return redirect()->to($redirect);
        }

        // Handle failed login
        if (Schema::hasTable('login_attempts')) {
            $updated = DB::table('login_attempts')
                ->where('ip_address', $ipAddress)
                ->increment('attempts', 1, ['last_login' => now(), 'updated_at' => now()]);

            if ($updated === 0) {
                DB::table('login_attempts')->insert([
                    'ip_address' => $ipAddress,
                    'attempts' => 1,
                    'last_login' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $payload = [
            'status' => 'error',
            'message' => 'Please check your credentials and try again.',
        ];

        return $wantsJson ? response()->json($payload, 422) : back()->withErrors(['email' => $payload['message']])->withInput();
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user instanceof User) {
            $user->update(['online_status' => 'offline']);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function setTwoFactorPending(Request $request, int $userId, bool $remember): void
    {
        $request->session()->put(self::USER_2FA_PENDING, true);
        $request->session()->put(self::USER_2FA_ID, $userId);
        $request->session()->put(self::USER_2FA_REMEMBER, $remember);
    }
}
