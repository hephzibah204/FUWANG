<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\LegalDrafting\LegalDraftingService;
use App\Services\LegalDrafting\LegalDraftRequest;

class AiController extends Controller
{
    /**
     * Handle incoming chat requests from the frontend AI assistant.
     * We'll build a robust system prompt that feeds into an LLM (Gemini or OpenAI).
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $gemini = new \App\Services\GeminiService();
        $aiResponse = $gemini->siteSupportChat($request->message);

        return response()->json([
            'success' => $aiResponse['status'],
            'response' => $aiResponse['text'] ?? $aiResponse['message']
        ]);
    }

    /**
     * Specialized method to draft legal documents based on provided data.
     */
    public function draftLegalDocument(array $data)
    {
        $type = (string) ($data['document_type'] ?? 'general');
        $category = (string) ($data['category'] ?? 'Miscellaneous');
        $formData = is_array($data['form_data'] ?? null) ? $data['form_data'] : [];

        $svc = app(LegalDraftingService::class);
        $res = $svc->draftHtml(new LegalDraftRequest($type, $category, $formData, 'You are a legal drafting AI specializing in Nigerian Law.'));
        if (!($res['ok'] ?? false)) {
            return "I'm currently experiencing a connection issue with my AI brain. Please try again later or visit the [Support Center](/tickets).";
        }
        return (string) $res['html'];
    }

    /**
     * Builds a comprehensive context about the platform and the current user.
     */
    private function buildSystemContext($user)
    {
        $appName = config('app.name');
        
        $context = "You are FuwaAI, an advanced and helpful assistant baked directly into the $appName platform. ";
        $context .= "Your primary goal is to assist the user with navigating the platform, explaining features, checking their status, and providing guidance.\n\n";

        if ($user) {
            $context .= "### CURRENT USER DATA\n";
            $context .= "- Name: {$user->fullname}\n";
            $context .= "- Email: {$user->email}\n";
            $context .= "- Wallet Balance: ₦" . number_format($user->balance ?? 0, 2) . "\n";
            $context .= "- Account Status: " . ($user->status == 1 ? 'Active' : 'Restricted') . "\n\n";
        }

        $context .= "### PLATFORM FEATURES & NAVIGATION\n";
        $context .= "You MUST direct users to the correct pages using format: [Page Name](/route). Here are the available services:\n";
        $context .= "- **Dashboard**: `/dashboard` (Overview of account)\n";
        $context .= "- **Notary & Legal Services**: `/services/notary` (Get documents legally notarized or drafted online)\n";
        $context .= "  - Categories: Business, Employment, Property, Financial, Personal, Digital/IP, and Education.\n";
        $context .= "- **Airtime Topup**: `/services/airtime`\n";
        $context .= "- **Data Purchase**: `/services/data`\n";
        $context .= "- **NIN Verification**: `/services/nin`\n";
        $context .= "- **BVN Verification**: `/services/bvn`\n";
        $context .= "- **Virtual Cards**: `/services/virtual-card`\n";
        $context .= "- **Agency Banking**: `/services/agency-banking`\n";
        $context .= "- **Auctions**: `/services/auctions`\n";
        $context .= "- **Logistics**: `/services/post-office`\n";
        $context .= "- **Ticketing**: `/services/ticketing`\n";
        $context .= "- **FX Exchange**: `/services/fx`\n";
        $context .= "- **Invoicing**: `/services/invoicing`\n";
        
        $context .= "\n### LEGAL DRAFTING CAPABILITIES\n";
        $context .= "You can help users understand and draft over 40 types of legal documents. ";
        $context .= "If a user asks about a specific document (e.g., 'How do I draft a tenancy agreement?'), explain the process and direct them to the [Notary Page](/services/notary).\n";

        $context .= "### TONE & PERSONALITY\n";
        $context .= "Be extremely professional, concise, and helpful. Use Markdown for formatting.\n";
        
        return $context;
    }

    /**
     * Abstraction to call LLM APIs.
     */
    private function generateResponse($systemPrompt, $userMessage)
    {
        $apiCenter = \App\Models\ApiCenter::first();
        $geminiKey = $apiCenter->gemini_api_key ?? config('services.gemini.key');
        $openaiKey = config('services.openai.key');

        try {
            if ($geminiKey) {
                return $this->callGemini($geminiKey, $systemPrompt, $userMessage);
            } elseif ($openaiKey) {
                return $this->callOpenAI($openaiKey, $systemPrompt, $userMessage);
            } else {
                // Fallback deterministic response if no AI keys are connected.
                return $this->fallbackResponse($userMessage);
            }
        } catch (\Exception $e) {
            Log::error('AI Error: ' . $e->getMessage());
            return "I'm currently experiencing a connection issue with my AI brain. Please try again later or visit the [Support Center](/tickets).";
        }
    }

    private function callGemini($apiKey, $systemPrompt, $userMessage)
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
        
        $payload = [
            "system_instruction" => [
                "parts" => [
                    ["text" => $systemPrompt]
                ]
            ],
            "contents" => [
                [
                    "role" => "user",
                    "parts" => [
                        ["text" => $userMessage]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.7,
                "maxOutputTokens" => 800
            ]
        ];

        $response = Http::post($url, $payload);

        if ($response->successful()) {
            $data = $response->json();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? "Sorry, I couldn't formulate a response.";
        }

        throw new \Exception('Gemini API failed: ' . $response->body());
    }

    private function callOpenAI($apiKey, $systemPrompt, $userMessage)
    {
        $response = Http::withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'temperature' => 0.7,
        ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        throw new \Exception('OpenAI API failed: ' . $response->body());
    }

    private function fallbackResponse($message)
    {
        $lower = strtolower($message);
        if (str_contains($lower, 'balance')) {
            $bal = number_format(auth()->user()->balance ?? 0, 2);
            return "Your current wallet balance is **₦{$bal}**. You can add funds via the [Dashboard](/dashboard) by clicking 'Add Funds'.";
        }
        if (str_contains($lower, 'airtime') || str_contains($lower, 'data')) {
            return "You can purchase Airtime and Data instantly! Head over to the [Airtime Page](/services/airtime) or [Data Page](/services/data).";
        }
        if (str_contains($lower, 'virtual card') || str_contains($lower, 'dollar')) {
            return "We offer USD and NGN virtual cards for global and local payments. Visit the [Virtual Cards](/services/virtual-card) page to get started.";
        }

        return "Hello! I am FuwaAI. It looks like the system administrator hasn't connected my dynamic LLM brain yet (API keys missing). However, I can still guide you to features like [Virtual Cards](/services/virtual-card), [Airtime](/services/airtime), or the [Dashboard](/dashboard). How can I help?";
    }
}
