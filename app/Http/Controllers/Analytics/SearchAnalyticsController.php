<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Analytics\TrafficTrackerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchAnalyticsController extends Controller
{
    public function log(Request $request, TrafficTrackerService $tracker): JsonResponse
    {
        $keyword = (string) $request->input('keyword', '');
        $source = (string) $request->input('source', 'search_hub');
        $resultsCount = (int) $request->input('results_count', 0);

        if (mb_strlen(trim($keyword)) >= 2) {
            $tracker->logKeyword($keyword, $source, $resultsCount, $request);
        }

        return response()->json(['ok' => true]);
    }
}
