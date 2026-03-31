<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Utils\TOTP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorSetupController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        
        if ($admin->two_factor_enabled) {
            return view('admin.auth.2fa_setup', [
                'enabled' => true
            ]);
        }

        // Generate new secret if not exists
        if (!$admin->two_factor_secret) {
            $admin->two_factor_secret = TOTP::generateSecret();
            $admin->save();
        }

        $appName = config('app.name');
        $provisioningUrl = TOTP::getProvisioningUrl($appName, $admin->email, $admin->two_factor_secret);
        $qrImage = 'https://chart.googleapis.com/chart?chs=240x240&cht=qr&chl=' . urlencode($provisioningUrl) . '&choe=UTF-8';

        return view('admin.auth.2fa_setup', [
            'enabled' => false,
            'secret' => $admin->two_factor_secret,
            'qrImage' => $qrImage
        ]);
    }

    public function enable(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $admin = Auth::guard('admin')->user();

        if (TOTP::verify($admin->two_factor_secret, $request->code)) {
            $admin->two_factor_enabled = true;
            $admin->save();

            return back()->with('success', 'Two-Factor Authentication has been enabled successfully.');
        }

        return back()->withErrors(['code' => 'The provided code was invalid. Please try again.']);
    }

    public function disable(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $admin->two_factor_enabled = false;
        $admin->two_factor_secret = null;
        $admin->save();

        return back()->with('success', 'Two-Factor Authentication has been disabled.');
    }
}
