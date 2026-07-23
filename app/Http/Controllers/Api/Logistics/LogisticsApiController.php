<?php

namespace App\Http\Controllers\Api\Logistics;

use App\Http\Controllers\Controller;
use App\Models\LogisticsProfile;
use App\Models\User;
use App\Services\Auth\SSOBridgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LogisticsApiController extends Controller
{
    protected SSOBridgeService $ssoBridge;

    public function __construct(SSOBridgeService $ssoBridge)
    {
        $this->ssoBridge = $ssoBridge;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string',
            'username' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'type' => 'validation_error',
                    'message' => 'The given data was invalid.',
                    'reference_id' => (string) Str::uuid(),
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        $user = User::create([
            'fullname' => $request->input('fullname'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'user_status' => 'active',
        ]);

        LogisticsProfile::firstOrCreate(['user_id' => $user->id]);

        $token = $this->ssoBridge->generateServiceToken($user, 'logistics');

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'fullname' => $user->fullname,
                'username' => $user->username,
            ],
        ], 201);
    }

    public function authenticate(Request $request)
    {
        $throttleKey = Str::lower((string) $request->input('email')) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Too many login attempts. Please try again later.',
            ], 429);
        }

        $user = User::where('email', $request->input('email'))->first();

        if (!$user || !Hash::check((string) $request->input('password'), $user->password)) {
            RateLimiter::hit($throttleKey, 60);
            return response()->json(['status' => 'error', 'message' => 'Invalid credentials.'], 401);
        }

        if (($user->user_status ?? 'active') !== 'active') {
            RateLimiter::hit($throttleKey, 60);
            return response()->json(['status' => 'error', 'message' => 'Account is not active.'], 401);
        }

        RateLimiter::clear($throttleKey);

        $token = $this->ssoBridge->generateServiceToken($user, 'logistics');

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'fullname' => $user->fullname,
                'username' => $user->username,
            ],
        ], 200);
    }

    public function validateToken(Request $request)
    {
        $raw = (string) $request->header('Authorization');
        $token = trim(str_ireplace('Bearer', '', $raw));

        $session = $this->ssoBridge->validateServiceToken($token, 'logistics');
        if (!$session) {
            return response()->json(['status' => 'error', 'valid' => false], 401);
        }

        return response()->json(['status' => 'success', 'valid' => true], 200);
    }

    public function revokeToken(Request $request)
    {
        $raw = (string) $request->header('Authorization');
        $token = trim(str_ireplace('Bearer', '', $raw));

        $this->ssoBridge->revokeServiceToken($token, 'logistics');

        return response()->json(['status' => 'success'], 200);
    }
}
