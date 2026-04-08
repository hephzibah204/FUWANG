<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use RobThree\Auth\TwoFactorAuth;

class TwoFactorController extends Controller
{
    public function enable(Request $request)
    {
        $user = Auth::user();
        $tfa = new TwoFactorAuth(config('app.name'));

        if ($user->two_factor_secret) {
            return response()->json(['status' => false, 'message' => '2FA is already enabled.'], 400);
        }

        $secret = $tfa->createSecret();
        $user->two_factor_secret = $secret;
        $user->save();

        $qrCodeUrl = $tfa->getQRCodeImageAsDataUri($user->email, $secret);

        return response()->json([
            'status' => true,
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
            'message' => '2FA secret generated. Please scan the QR code and verify.'
        ]);
    }

    public function disable(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Auth::validate(['email' => $user->email, 'password' => $request->password])) {
            return response()->json(['status' => false, 'message' => 'Invalid password.'], 401);
        }

        $user->two_factor_secret = null;
        $user->save();

        return response()->json(['status' => true, 'message' => '2FA has been disabled.']);
    }
}
