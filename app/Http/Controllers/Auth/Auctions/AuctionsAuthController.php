<?php

namespace App\Http\Controllers\Auth\Auctions;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuctionsAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auctions.auth.login');
    }

    public function showRegisterForm()
    {
        return view('auctions.auth.register');
    }

    public function login(Request $request)
    {
        $request->merge([
            'service' => 'auctions',
        ]);

        return app(LoginController::class)->login($request);
    }

    public function register(Request $request)
    {
        $request->merge([
            'service' => 'auctions',
        ]);

        return app(RegisterController::class)->register($request);
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

        return redirect('/auction');
    }
}

