<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    private const USER_2FA_PENDING = 'user_2fa_pending';
    private const USER_2FA_ID = 'user_2fa_id';
    private const USER_2FA_REMEMBER = 'user_2fa_remember';

    public function show(Request $request)
    {
        if (! $request->session()->get(self::USER_2FA_PENDING)) {
            return redirect()->route('login');
        }

        return view('auth.2fa-challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'one_time_password' => ['required', 'string', 'size:6'],
        ]);

        if (! $request->session()->get(self::USER_2FA_PENDING)) {
            return redirect()->route('login');
        }

        $userId = (int) $request->session()->get(self::USER_2FA_ID, 0);
        $remember = (bool) $request->session()->get(self::USER_2FA_REMEMBER, false);
        $user = User::query()->find($userId);

        if (! $user || empty($user->google2fa_secret)) {
            $this->clearTwoFactorPending($request);

            return redirect()->route('login')->withErrors([
                'email' => 'Your two-factor session expired. Please sign in again.',
            ]);
        }

        $google2fa = new Google2FA;
        if (! $google2fa->verifyKey((string) $user->google2fa_secret, (string) $request->input('one_time_password'))) {
            return back()->withErrors([
                'one_time_password' => 'Invalid authentication code.',
            ])->withInput();
        }

        $this->clearTwoFactorPending($request);
        $request->session()->regenerate();

        Auth::login($user, $remember);
        session([
            'fullname' => $user->fullname,
            'transaction_pin' => $user->transaction_pin,
        ]);
        $user->update(['online_status' => 'online']);

        $redirect = ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            ? route('verification.notice')
            : route('dashboard');

        return redirect()->intended($redirect);
    }

    public function cancel(Request $request): RedirectResponse
    {
        $this->clearTwoFactorPending($request);

        return redirect()->route('login');
    }

    private function clearTwoFactorPending(Request $request): void
    {
        $request->session()->forget(self::USER_2FA_PENDING);
        $request->session()->forget(self::USER_2FA_ID);
        $request->session()->forget(self::USER_2FA_REMEMBER);
    }
}
