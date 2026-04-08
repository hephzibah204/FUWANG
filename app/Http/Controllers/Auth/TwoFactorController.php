<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use RobThree\Auth\TwoFactorAuth;

class TwoFactorController extends Controller
{
    public function showVerifyForm()
    {
        if (!session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }
        return view('auth.2fa_verify');
    }

    public function verify(Request $request)
    {
        $request->validate([
            '2fa_code' => 'required|digits:6',
        ]);

        $userId = $request->session()->get('2fa:user:id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        $tfa = new TwoFactorAuth(config('app.name'));
        $isValid = $tfa->verifyCode($user->two_factor_secret, $request->input('2fa_code'));

        if ($isValid) {
            $remember = $request->session()->get('2fa:user:remember', false);
            Auth::login($user, $remember);
            
            $request->session()->forget('2fa:user:id');
            $request->session()->forget('2fa:user:remember');
            $request->session()->regenerate();
            
            $user->update(['online_status' => 'online']);

            return redirect()->route('dashboard');
        }

        return back()->withErrors(['2fa_code' => 'The 2FA code is invalid.']);
    }
}
