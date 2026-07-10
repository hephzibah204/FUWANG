<?php

namespace App\Http\Controllers\LogisticsOps;

use App\Http\Controllers\Controller;
use App\Models\LogisticsStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StaffAuthController extends Controller
{
    public function showLogin()
    {
        return view('logistics.ops.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $staff = LogisticsStaff::query()->where('email', $credentials['email'])->first();
        if (! $staff || ! $staff->is_active || ! Hash::check($credentials['password'], (string) $staff->password)) {
            return back()->withErrors([
                'email' => 'Invalid credentials.',
            ])->withInput();
        }

        Auth::guard('logistics_staff')->login($staff, true);
        $staff->forceFill(['last_login_at' => now()])->save();
        $staff->logActivity('logistics_ops.login');

        return redirect()->route('logistics.ops.dashboard');
    }

    public function logout(Request $request)
    {
        $staff = Auth::guard('logistics_staff')->user();
        if ($staff instanceof LogisticsStaff) {
            $staff->logActivity('logistics_ops.logout');
        }

        Auth::guard('logistics_staff')->logout();
        session()->forget('logistics_ops_impersonator_admin_id');
        session()->forget('logistics_ops_impersonator_admin_email');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('logistics.ops.login');
    }

    public function stopImpersonation(Request $request)
    {
        $impersonatorId = session('logistics_ops_impersonator_admin_id');
        Auth::guard('logistics_staff')->logout();
        session()->forget('logistics_ops_impersonator_admin_id');
        session()->forget('logistics_ops_impersonator_admin_email');
        $request->session()->regenerateToken();

        if ($impersonatorId) {
            return redirect()->route('admin.logistics-staff.index');
        }

        return redirect()->route('logistics.ops.login');
    }
}
