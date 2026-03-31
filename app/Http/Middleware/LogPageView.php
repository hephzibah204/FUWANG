<?php

namespace App\Http\Middleware;

use App\Models\AbEvent;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogPageView
{
    public function handle(Request $request, Closure $next, string $surface = 'public'): Response
    {
        $response = $next($request);

        try {
            $experiment = null;
            $variant = null;

            $abVariants = $request->attributes->get('ab_variants');
            if (is_array($abVariants) && !empty($abVariants)) {
                $experiment = array_key_first($abVariants);
                $variant = (string) ($abVariants[$experiment] ?? '');
            } elseif ($request->path() === '/' || $request->path() === '') {
                $experiment = 'home_hero';
                $variant = (string) $request->cookie('ab_home_hero', '');
                $abVariants = $experiment && $variant ? [$experiment => $variant] : [];
            } else {
                $abVariants = [];
            }

            AbEvent::create([
                'user_id' => Auth::id(),
                'session_id' => $request->session()?->getId(),
                'experiment' => $experiment,
                'variant' => $variant,
                'event_name' => 'page_view',
                'page' => $request->path() . ($request->getQueryString() ? ('?' . $request->getQueryString()) : ''),
                'meta' => [
                    'surface' => $surface,
                    'ref' => $request->headers->get('referer'),
                    'ab_variants' => $abVariants,
                ],
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);
        } catch (\Throwable $e) {
        }

        return $response;
    }
}
