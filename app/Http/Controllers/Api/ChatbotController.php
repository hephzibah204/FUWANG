<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GeminiService;
use App\Models\ChatbotSession;
use App\Models\ChatbotFeedback;
use App\Models\Ticket;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'session_id' => 'nullable|string',
        ]);

        $sessionId = $request->input('session_id', Str::uuid()->toString());
        $message = $request->input('message');
        
        $session = ChatbotSession::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => auth()->id(),
                'conversation_history' => [],
                'status' => 'active'
            ]
        );

        if ($session->status === 'handed_off') {
            return response()->json([
                'status' => true,
                'session_id' => $sessionId,
                'message' => 'An agent will be with you shortly. You have been transferred to a human agent.',
            ]);
        }

        // Context Aware Chat
        $response = $this->geminiService->contextAwareChat($message, $session);

        return response()->json([
            'status' => true,
            'session_id' => $sessionId,
            'message' => $response['text'],
        ]);
    }

    public function feedback(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string|exists:chatbot_sessions,session_id',
            'query_text' => 'required|string',
            'response_text' => 'required|string',
            'is_accurate' => 'required|boolean',
            'score' => 'nullable|integer|min:1|max:5',
            'user_comments' => 'nullable|string'
        ]);

        $session = ChatbotSession::where('session_id', $request->session_id)->firstOrFail();

        ChatbotFeedback::create([
            'chatbot_session_id' => $session->id,
            'query_text' => $request->query_text,
            'response_text' => $request->response_text,
            'is_accurate' => $request->is_accurate,
            'score' => $request->score,
            'user_comments' => $request->user_comments,
            'requires_retraining' => !$request->is_accurate || ($request->score && $request->score <= 3),
        ]);

        return response()->json(['status' => true, 'message' => 'Feedback submitted successfully.']);
    }

    public function handoff(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string|exists:chatbot_sessions,session_id',
            'reason' => 'nullable|string'
        ]);

        $session = ChatbotSession::where('session_id', $request->session_id)->firstOrFail();
        $session->update(['status' => 'handed_off']);

        // Create a Support Ticket
        Ticket::create([
            'user_id' => $session->user_id ?? 1, // Fallback to 1 or null if guest
            'subject' => 'Chatbot Handoff: User needs assistance',
            'status' => 'Open',
            'priority' => 'High',
            'message' => "User requested human agent. \n\nReason: " . $request->reason . "\n\nChat History: \n" . json_encode($session->conversation_history, JSON_PRETTY_PRINT),
        ]);

        return response()->json(['status' => true, 'message' => 'Transferred to a human agent.']);
    }
}
