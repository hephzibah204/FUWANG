<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class LoginController extends Controller
{
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

        if (Auth::attempt($credentials, $remember)) {
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

            // Update online status
            $user->update(['online_status' => 'online']);

            $redirect = route('dashboard');
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
        if ($user) {
            $user->update(['online_status' => 'offline']);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
