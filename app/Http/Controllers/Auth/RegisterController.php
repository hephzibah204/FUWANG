<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\Referrals\ReferralService;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:20', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'transaction_pin' => ['required', 'string', 'min:4', 'max:4'],
        ]);

        $referrals = app(ReferralService::class);
        $code = $referrals->normalizeCode(
            $request->input('referral_code')
            ?? $request->query('ref')
            ?? $request->input('reseller_id')
        );

        $referrer = null;
        if ($code) {
            $referrer = $referrals->findReferrerByCode($code);
            if (!$referrer) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid referral code.',
                    ], 422);
                }
                return back()->withErrors(['referral_code' => 'Invalid referral code.'])->withInput();
            }
            if (strtolower((string) $referrer->email) === strtolower((string) $request->email)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Referral code cannot be used for the same email.',
                    ], 422);
                }
                return back()->withErrors(['referral_code' => 'Referral code cannot be used for the same email.'])->withInput();
            }
        }

        $user = DB::transaction(function () use ($request, $referrer, $referrals, $code) {
            $user = User::create([
                'fullname' => $request->fullname,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'transaction_pin' => $request->transaction_pin,
                'referral_id' => $referrals->generateReferralCode(),
                'reseller_id' => $code ?: ($request->reseller_id ?? 'default'),
                'referred_user_id' => $referrer?->id,
            ]);

            if ($referrer && $code) {
                $referral = $referrals->recordRegistration($referrer, $user, $code, [
                    'ip' => (string) request()->ip(),
                    'user_agent' => substr((string) request()->userAgent(), 0, 250),
                ]);
                $referrals->notifyReferrerRegistered($referrer, $referral);
            }

            return $user;
        });

        Auth::login($user);

        $redirect = route('dashboard');

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful! Redirecting to dashboard...',
                'redirect' => $redirect,
            ]);
        }

        return redirect()->to($redirect);
    }
}
