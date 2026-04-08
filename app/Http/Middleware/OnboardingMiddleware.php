<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingMiddleware
{
    public function handle(Request $request, Closure $next, $tour)
    {
        $user = Auth::user();

        if ($user && !$user->hasCompletedTour($tour)) {
            $request->session()->put('start_tour', $tour);
        }

        return $next($request);
    }
}
