<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function createToken(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'name' => ['nullable', 'string', 'max:120'],
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['status' => false, 'message' => 'Invalid credentials.'], 401);
        }

        $plain = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $hash = hash('sha256', $plain);
        $lastFour = substr($plain, -4);

        ApiToken::create([
            'user_id' => $user->id,
            'name' => $request->input('name') ?: 'default',
            'token_hash' => $hash,
            'last_four' => $lastFour,
            'abilities' => ['*'],
            'rate_limit_per_minute' => 60,
        ]);

        return response()->json([
            'status' => true,
            'token' => 'nx_' . $plain,
            'token_type' => 'Bearer',
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'fullname' => $user->fullname ?? null,
                'username' => $user->username ?? null,
            ],
        ]);
    }

    public function revokeCurrent(Request $request)
    {
        /** @var ApiToken|null $token */
        $token = $request->attributes->get('api_token');
        if (!$token) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $token->forceFill(['revoked_at' => now()])->save();

        return response()->json(['status' => true, 'message' => 'Token revoked.']);
    }
}

