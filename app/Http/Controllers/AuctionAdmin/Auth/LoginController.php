<?php

namespace App\Http\Controllers\AuctionAdmin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auction_admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');
        $ok = Auth::guard('auction_admin')->attempt([
            'email' => $request->email,
            'password' => $request->password,
            'is_active' => true,
        ], $remember);

        if (! $ok) {
            return back()
                ->withErrors(['email' => 'Invalid credentials or account disabled.'])
                ->withInput($request->only('email'));
        }

        $admin = Auth::guard('auction_admin')->user();
        if ($admin instanceof \App\Models\AuctionAdmin) {
            $admin->forceFill(['last_login_at' => now()])->save();
        }

        $request->session()->regenerate();

        return redirect()->intended(route('auction.admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('auction_admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auction.admin.login');
    }
}

