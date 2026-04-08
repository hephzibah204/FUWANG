<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class EnforceKycTierLimits
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && !$this->isTransactionAllowed($user, $request)) {
            return response()->json(['error' => 'Transaction limit exceeded for your verification tier.'], 403);
        }

        return $next($request);
    }

    protected function isTransactionAllowed(User $user, Request $request)
    {
        // This is a placeholder for the actual logic.
        // In a real application, you would check the user's verification tier
        // and the transaction amount against the defined limits.
        return true;
    }
}
