<?php

namespace App\Http\Controllers\Api\LogisticsOps;

use App\Http\Controllers\Controller;
use App\Models\LogisticsStaff;
use App\Services\Logistics\LogisticsStaffJwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffAuthApiController extends Controller
{
    public function login(Request $request, LogisticsStaffJwtService $jwt)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $staff = LogisticsStaff::query()->where('email', $validated['email'])->first();
        if (! $staff || ! $staff->is_active || ! Hash::check($validated['password'], (string) $staff->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $staff->forceFill(['last_login_at' => now()])->save();
        $staff->logActivity('logistics_ops.jwt_issued');

        $issued = $jwt->issueToken($staff);

        return response()->json([
            'status' => 'success',
            'token' => $issued['token'],
            'expires_at' => $issued['expires_at'],
            'role' => $staff->getRoleNames()->first(),
        ]);
    }

    public function logout(Request $request, LogisticsStaffJwtService $jwt)
    {
        $payload = $request->attributes->get('jwt');
        if ($payload && isset($payload->jti)) {
            $jwt->revoke((string) $payload->jti);
        }

        $staff = $request->attributes->get('logistics_staff');
        if ($staff instanceof LogisticsStaff) {
            $staff->logActivity('logistics_ops.jwt_revoked');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out.',
        ]);
    }
}

