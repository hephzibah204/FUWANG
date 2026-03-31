<?php

namespace App\Services;

use App\Models\ApiCenter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\LegalDrafting\LegalDraftingService;
use App\Services\LegalDrafting\LegalDraftRequest;

class GeminiService
{
    protected ?string $apiKey;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

    public function __construct()
    {
        // Try to get API key from CustomApi first (new controllable way)
        $customApi = \App\Models\CustomApi::where('service_type', 'gemini_ai')->where('status', true)->first();
        if ($customApi) {
            $this->apiKey = $customApi->api_key;
        } else {
            // Fallback to legacy ApiCenter
            $apiCenter = ApiCenter::first();
            $this->apiKey = $apiCenter->gemini_api_key ?? null;
        }
    }

    /**
     * Generate content using Gemini Pro
     */
    public function generateResponse(string $prompt, string $systemInstruction = ''): array
    {
        if (!$this->apiKey) {
            return ['status' => false, 'message' => 'Gemini API key not configured in Admin CMS.'];
        }

        try {
            $fullPrompt = $systemInstruction ? "Instructions: $systemInstruction\n\nUser Request: $prompt" : $prompt;

            $response = Http::post($this->baseUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $fullPrompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 2048,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response generated.';
                return [
                    'status' => true,
                    'text' => $text
                ];
            }

            Log::error('Gemini API Error', ['response' => $response->json()]);
            return ['status' => false, 'message' => 'AI Service currently unavailable.'];

        } catch (\Exception $e) {
            Log::error('Gemini Service Exception', ['error' => $e->getMessage()]);
            return ['status' => false, 'message' => 'An error occurred while connecting to AI service.'];
        }
    }

    /**
     * Specialized prompt for Legal Drafting
     */
    public function draftLegalDocument(string $type, array $details): array
    {
        $category = (string) ($details['category'] ?? $details['document_category'] ?? 'General');
        $systemMsg = "You are an expert Nigerian Legal Draftsman. Draft a professional, legally binding $type document based on the provided details. Use formal legal language and ensure it complies with Nigerian law. Format the output with clear headings and professional structure.";

        $svc = app(LegalDraftingService::class);
        $res = $svc->draftHtml(new LegalDraftRequest($type, $category, $details, $systemMsg));

        if (!($res['ok'] ?? false)) {
            return [
                'status' => false,
                'message' => $res['message'] ?? 'AI Service currently unavailable.',
            ];
        }

        return [
            'status' => true,
            'text' => (string) $res['html'],
            'provider' => $res['provider'] ?? null,
        ];
    }

    /**
     * Specialized prompt for Site Support
     */
    public function siteSupportChat(string $userQuestion): array
    {
        $siteInfo = "You are the AI assistant for " . config('app.name') . ", a comprehensive Nigerian identity verification and legal services platform.
        Services offered:
        - NIN Verification (Standard, Phone, Demography)
        - BVN Verification (BVN Match, API lookups)
        - Identity Validation (Official document checks)
        - IPE Clearance (Background vetting)
        - Personalization (Identity profile setup)
        - AI Legal Hub (Drafting NDAs, Sales Agreements, etc.)
        - VTU Hub (Airtime and Data subscriptions)
        - Notary Services (Electronic stamping and signing)
        - Education (WAEC PINs)
        - Insurance (Motor Insurance)
        
        Always be professional, helpful, and concise. If you don't know the answer, ask the user to contact support at support@fuwa.ng.";

        return $this->generateResponse($userQuestion, $siteInfo);
    }

    /**
     * Context-Aware Chat with RAG Pipeline
     */
    public function contextAwareChat(string $userQuestion, \App\Models\ChatbotSession $session): array
    {
        // 1. Get embedding for user question
        $questionEmbedding = $this->generateEmbedding($userQuestion);

        $contextChunks = [];
        if ($questionEmbedding) {
            // 2. Retrieve relevant Knowledge Base chunks
            $kbs = \App\Models\ChatbotKnowledgeBase::where('is_active', true)->whereNotNull('embedding')->get();
            $scoredKbs = [];
            foreach ($kbs as $kb) {
                if (is_array($kb->embedding)) {
                    $score = \App\Models\ChatbotKnowledgeBase::cosineSimilarity($questionEmbedding, $kb->embedding);
                    if ($score > 0.75) { // Threshold
                        $scoredKbs[] = ['score' => $score, 'text' => $kb->content_chunk];
                    }
                }
            }

            usort($scoredKbs, fn($a, $b) => $b['score'] <=> $a['score']);
            $topK = array_slice($scoredKbs, 0, 3);
            $contextChunks = array_column($topK, 'text');
        }

        // 3. Build the prompt with Context and History
        $contextString = implode("\n\n---\n\n", $contextChunks);
        $history = $session->conversation_history ?? [];
        
        $systemMsg = "You are the expert AI assistant for " . config('app.name') . ", a comprehensive Nigerian identity verification and digital services platform.
        You must use the provided Knowledge Base context to answer accurately. If the context doesn't contain the answer, rely on your general knowledge but clarify it's a general answer.
        Always maintain conversation context. If the user asks for human help, instruct them to use the handoff option.

        KNOWLEDGE BASE CONTEXT:
        $contextString
        ";

        // Format history for prompt
        $historyString = "";
        foreach(array_slice($history, -5) as $msg) {
            $role = $msg['role'] === 'user' ? 'User' : 'Assistant';
            $historyString .= "$role: {$msg['content']}\n";
        }

        $fullPrompt = "Conversation History:\n$historyString\nUser: $userQuestion";

        $response = $this->generateResponse($fullPrompt, $systemMsg);

        if ($response['status']) {
            // Update Session History
            $history[] = ['role' => 'user', 'content' => $userQuestion];
            $history[] = ['role' => 'assistant', 'content' => $response['text']];
            
            // Keep only last 10 messages
            if (count($history) > 10) {
                $history = array_slice($history, -10);
            }
            
            $session->update(['conversation_history' => $history]);
        }

        return $response;
    }

    /**
     * Generate Embedding for a given text
     */
    public function generateEmbedding(string $text): ?array
    {
        if (!$this->apiKey) return null;

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/embedding-001:embedContent?key={$this->apiKey}";
            $response = Http::post($url, [
                'model' => 'models/embedding-001',
                'content' => [
                    'parts' => [
                        ['text' => $text]
                    ]
                ]
            ]);

            if ($response->successful()) {
                return $response->json()['embedding']['values'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('Gemini Embedding Error', ['error' => $e->getMessage()]);
        }
        return null;
    }
}
