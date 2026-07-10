<?php

namespace App\Http\Middleware;

use App\Models\DeliveryAgent;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureDeliveryAgent
{
    public function handle(Request $request, Closure $next, ?string $mode = null)
    {
        $user = Auth::user();
        if (! $user || ! ($user instanceof \App\Models\User)) {
            abort(403);
        }

        $agent = DeliveryAgent::query()->where('user_id', $user->id)->first();
        if (! $agent) {
            abort(403);
        }

        if ($mode === 'approved' && $agent->approval_status !== 'approved') {
            abort(403);
        }

        $request->attributes->set('delivery_agent', $agent);

        return $next($request);
    }
}

