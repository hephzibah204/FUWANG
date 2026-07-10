<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Activitylog\Facades\LogBatch;

class ActivityLogMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        LogBatch::startBatch();

        $response = $next($request);

        LogBatch::endBatch();

        return $response;
    }
}
