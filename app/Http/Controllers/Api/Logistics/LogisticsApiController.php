<?php

namespace App\Http\Controllers\Api\Logistics;

use App\Http\Controllers\Controller;
use App\Models\LogisticsProfile;
use App\Services\Auth\SSOBridgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class LogisticsApiController extends Controller
{
    public function __construct(
        private SSOBridgeService $ssoBridge
    ) {}

    public function authenticate(Request $request)
    {
        $result = $this->ssoBridge->authenticateWithCredentials(
            $request->input('email'),
            $request->input('password'),
            'logistics'
        );

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials.',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'token' => $result['token'],
            'user' => $result['user'],
        ]);
    }

    public function ssoAuthenticate(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Main platform authentication required.',
            ], 401);
        }

        $result = $this->ssoBridge->authenticateExistingUser(Auth::user(), 'logistics');

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account not active.',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'token' => $result['token'],
            'user' => $result['user'],
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:20', 'unique:users,username'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
            'transaction_pin' => ['required', 'string', 'min:4', 'max:4'],
        ]);

        $user = \App\Models\User::create([
            'fullname' => $validated['fullname'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'transaction_pin' => $validated['transaction_pin'],
            'reseller_id' => 'default',
            'referral_id' => bin2hex(random_bytes(4)),
            'online_status' => 'offline',
            'user_status' => 'active',
        ]);

        LogisticsProfile::create([
            'user_id' => $user->id,
            'contact_person' => $user->fullname,
            'email' => $user->email,
            'is_active' => true,
        ]);

        $result = $this->ssoBridge->authenticateExistingUser($user, 'logistics');

        return response()->json([
            'status' => 'success',
            'token' => $result['token'],
            'user' => $result['user'],
        ], 201);
    }

    public function validateToken(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['valid' => false], 401);
        }

        $sessionData = $this->ssoBridge->validateServiceToken($token, 'logistics');

        if (!$sessionData) {
            return response()->json(['valid' => false], 401);
        }

        return response()->json([
            'valid' => true,
            'user_id' => $sessionData['user_id'],
            'scopes' => $sessionData['scopes'],
        ]);
    }

    public function revokeToken(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['status' => 'error', 'message' => 'Token required.'], 400);
        }

        $this->ssoBridge->revokeServiceToken($token, 'logistics');

        return response()->json(['status' => 'success', 'message' => 'Token revoked.']);
    }

}
