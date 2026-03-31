<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $google2fa_url = null;
        $secret = null;

        if (!$user->google2fa_secret) {
            $google2fa = app('pragmarx.google2fa');
            $secret = $google2fa->generateSecretKey();
            $google2fa_url = $google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                $secret
            );
        }

        return view('profile.index', [
            'user' => $user,
            'google2fa_url' => $google2fa_url,
            'google2fa_secret' => $secret
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'fullname'         => 'sometimes|string|max:100',
            'number'           => 'sometimes|string|digits:11',
            'current_password' => 'required_with:new_password|string',
            'new_password'     => 'nullable|string|min:6|confirmed',
            'current_pin'      => 'required_with:new_pin|string',
            'new_pin'          => 'nullable|string|digits:4',
        ]);

        $data = [];

        if ($request->filled('fullname'))  $data['fullname'] = $request->fullname;
        if ($request->filled('number'))    $data['number']   = $request->number;

        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['status' => false, 'message' => 'Current password is incorrect.']);
            }
            $data['password'] = Hash::make($request->new_password);
        }

        if ($request->filled('new_pin')) {
            // Always use timing-safe hash comparison — never plaintext
            if (!$user->transaction_pin || !Hash::check($request->current_pin, $user->transaction_pin)) {
                return response()->json(['status' => false, 'message' => 'Current PIN is incorrect.']);
            }
            $data['transaction_pin'] = Hash::make($request->new_pin);
        }

        if (!empty($data)) {
            $user->update($data);
        }

        return response()->json(['status' => true, 'message' => 'Profile updated successfully.']);
    }

    public function enable2fa(Request $request)
    {
        $request->validate([
            'secret' => 'required|string',
            'one_time_password' => 'required|string|size:6'
        ]);

        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($request->secret, $request->one_time_password);

        if (!$valid) {
            return response()->json(['status' => false, 'message' => 'Invalid verification code. Please try again.']);
        }

        Auth::user()->update(['google2fa_secret' => $request->secret]);

        return response()->json(['status' => true, 'message' => '2FA enabled successfully. Your account is now more secure.']);
    }

    public function disable2fa(Request $request)
    {
        $request->validate(['current_password' => 'required|string']);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return response()->json(['status' => false, 'message' => 'Incorrect password.']);
        }

        Auth::user()->update(['google2fa_secret' => null]);

        return response()->json(['status' => true, 'message' => '2FA disabled successfully. We recommend keeping it enabled for better security.']);
    }
}
