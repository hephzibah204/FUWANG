<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeveloperPortalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tokens = ApiToken::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $baseUrl = $request->getSchemeAndHttpHost() . '/api/v1';

        return view('developer.portal', compact('tokens', 'baseUrl'));
    }

    public function createToken(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $user = $request->user();
        $plain = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $hash = hash('sha256', $plain);
        $lastFour = substr($plain, -4);

        $token = ApiToken::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'token_hash' => $hash,
            'last_four' => $lastFour,
            'abilities' => ['*'],
            'rate_limit_per_minute' => 60,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'API token created. Copy it now; it will not be shown again.',
            'token' => 'nx_' . $plain,
            'token_id' => $token->id,
        ]);
    }

    public function revokeToken(Request $request, int $id)
    {
        $user = $request->user();
        $token = ApiToken::query()
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        if ($token->revoked_at) {
            return response()->json(['status' => true, 'message' => 'Token already revoked.']);
        }

        $token->forceFill(['revoked_at' => now()])->save();

        return response()->json(['status' => true, 'message' => 'Token revoked.']);
    }

    public function openapiV1()
    {
        $path = public_path('api-docs/openapi.yaml');
        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'application/yaml; charset=utf-8',
        ]);
    }

    public function docs()
    {
        return view('developer.docs');
    }
}

