<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatbotSession;
use App\Models\ChatbotFeedback;

class ChatbotAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin', 'super_admin']);
    }

    public function dashboardMetrics()
    {
        $totalSessions = ChatbotSession::count();
        $activeSessions = ChatbotSession::where('status', 'active')->count();
        $handedOffSessions = ChatbotSession::where('status', 'handed_off')->count();
        
        $totalFeedbacks = ChatbotFeedback::count();
        $positiveFeedbacks = ChatbotFeedback::where('is_accurate', true)->count();
        
        $accuracyRate = $totalFeedbacks > 0 ? round(($positiveFeedbacks / $totalFeedbacks) * 100, 2) : 100;
        
        $averageScore = ChatbotFeedback::whereNotNull('score')->avg('score') ?? 5.0;

        $requiresRetrainingCount = ChatbotFeedback::where('requires_retraining', true)->count();

        $requiresRetrainingSample = ChatbotFeedback::query()
            ->select(['id', 'session_id', 'score', 'created_at'])
            ->where('requires_retraining', true)
            ->latest('id')
            ->limit(10)
            ->get();

        return response()->json([
            'status' => true,
            'metrics' => [
                'total_sessions' => $totalSessions,
                'active_sessions' => $activeSessions,
                'human_handoff_rate' => $totalSessions > 0 ? round(($handedOffSessions / $totalSessions) * 100, 2) : 0,
                'accuracy_rate' => $accuracyRate,
                'average_satisfaction_score' => round($averageScore, 2),
                'needs_attention_count' => $requiresRetrainingCount,
            ],
            'needs_attention_sample' => $requiresRetrainingSample,
        ]);
    }
}
