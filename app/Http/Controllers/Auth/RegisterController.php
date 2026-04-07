<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

        $referralService = app(ReferralService::class);
        $referralCode = $referralService->normalizeCode($request->input('referral_code'));
        $referrer = $referralCode ? $referralService->findReferrerByCode($referralCode) : null;

        $user = User::create([
            'fullname' => $request->fullname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'transaction_pin' => $request->transaction_pin,
            'referral_id' => $referralService->generateReferralCode(),
            'reseller_id' => $request->reseller_id ?? 'default',
        ]);

        if ($referrer && $referralCode) {
            $referralService->recordRegistration($referrer, $user, $referralCode);
            $referralService->notifyReferrerRegistered($referrer, $user->referral()->first());
        }

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
