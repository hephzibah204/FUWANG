<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Activitylog\Facades\LogBatch;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class ActivityLogMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        LogBatch::startBatch();
        LogBatch::setBatchId(Str::uuid());

        $response = $next($request);

        $batchId = LogBatch::getBatchId();
        if ($batchId) {
            $activities = Activity::query()->where('batch_uuid', $batchId)->get();
            if ($activities->count() > 0) {
                activity()
                    ->causedBy($request->user())
                    ->withProperty('batch_uuid', $batchId)
                    ->log('A batch of activities has been performed.');
            }
        }

        LogBatch::endBatch();

        return $response;
    }
}
