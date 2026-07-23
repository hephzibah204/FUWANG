<?php

namespace App\Http\Controllers\Api\LogisticsOps;

use App\Http\Controllers\Controller;
use App\Models\LogisticsStaff;
use App\Services\Logistics\LogisticsStaffJwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffAuthApiController extends Controller
{
    protected $jwtService;

    public function __construct(LogisticsStaffJwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $staff = LogisticsStaff::where('email', $request->email)->first();

        if (!$staff || !Hash::check($request->password, $staff->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (!$staff->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account is inactive',
            ], 403);
        }

        $issued = $this->jwtService->issueToken($staff);

        return response()->json([
            'status' => 'success',
            'token' => $issued['token'],
            'expires_at' => date('c', $issued['expires_at']),
            'role' => $staff->getRoleNames()->first(),
        ]);
    }

    public function logout(Request $request)
    {
        $session = $request->attributes->get('logistics_jwt_session');
        if ($session && isset($session->jti)) {
            $this->jwtService->revoke($session->jti);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ]);
    }
}
