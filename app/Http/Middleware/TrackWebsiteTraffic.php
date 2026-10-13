<?php

namespace App\Http\Middleware;

use App\Services\Analytics\TrafficTrackerService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackWebsiteTraffic
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Track in web traffic service
        try {
            $tracker = app(TrafficTrackerService::class);
            $visitorId = $tracker->trackRequest($request);

            if ($visitorId && !$request->hasCookie('_fuwa_vid')) {
                // Set 1-year visitor tracking cookie
                $response->headers->setCookie(
                    cookie('_fuwa_vid', $visitorId, 60 * 24 * 365, null, null, false, false)
                );
            }
        } catch (\Throwable $e) {
            // Never disrupt user flow
        }

        return $response;
    }
}
