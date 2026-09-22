<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgentOnboardingComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $agent = $user->enrollmentAgent;

        if (! $agent) {
            return redirect()->route('agent.register')->with('error', 'Please submit your basic agent registration details first.');
        }

        if ($agent->onboarding_step !== 'submitted' || ! $agent->isApproved()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Agency KYC onboarding incomplete or pending approval.',
                    'redirect' => route('agent.onboarding.index'),
                ], 403);
            }

            return redirect()->route('agent.onboarding.index');
        }

        return $next($request);
    }
}
