<?php

namespace App\Services\LegalDrafting\Providers;

use App\Models\ApiCenter;
use App\Models\CustomApi;
use App\Services\LegalDrafting\LegalDraftRequest;
use Illuminate\Support\Facades\Http;

class GeminiFlashProvider implements DraftingProvider
{
    private ?string $apiKey;

    public function __construct()
    {
        $customApi = CustomApi::query()->where('service_type', 'gemini_ai')->where('status', true)->first();
        if ($customApi?->api_key) {
            $this->apiKey = $customApi->api_key;
            return;
        }
        $apiCenter = ApiCenter::first();
        $this->apiKey = $apiCenter?->gemini_api_key ?: config('services.gemini.key');
    }

    public function canDraft(): bool
    {
        return (bool) $this->apiKey;
    }

    public function name(): string
    {
        return 'gemini_flash';
    }

    public function draftHtml(LegalDraftRequest $req): string
    {
        $system = $req->systemPrompt ?: 'You are a legal drafting AI specializing in Nigerian Law.';
        $userPrompt = $this->buildPrompt($req);

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $this->apiKey;
        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $system],
                ],
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $userPrompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 1200,
            ],
        ];

        $resp = Http::timeout(60)->post($url, $payload);
        if (!$resp->successful()) {
            throw new \RuntimeException('Gemini request failed: ' . substr($resp->body(), 0, 500));
        }

        $text = $resp->json('candidates.0.content.parts.0.text');
        if (!is_string($text) || trim($text) === '') {
            throw new \RuntimeException('Gemini returned empty response.');
        }

        return $text;
    }

    private function buildPrompt(LegalDraftRequest $req): string
    {
        $type = $req->documentType ?: 'general';
        $category = $req->category ?: 'Miscellaneous';

        $prompt = "You are a senior Nigerian legal practitioner. Draft a formal and legally binding document for the following:\n";
        $prompt .= "### DOCUMENT DETAILS\n";
        $prompt .= "- Type: " . ucwords(str_replace('_', ' ', $type)) . "\n";
        $prompt .= "- Category: " . $category . "\n";
        $prompt .= "### PROVIDED DATA (JSON)\n";
        $prompt .= json_encode($req->formData, JSON_PRETTY_PRINT) . "\n\n";
        $prompt .= "### INSTRUCTIONS\n";
        $prompt .= "1. Use professional Nigerian legal language (e.g., 'WITNESSETH', 'HEREINAFTER', 'WHEREAS').\n";
        $prompt .= "2. Ensure the document follows standard Nigerian formatting for " . $category . ".\n";
        $prompt .= "3. If it is an Affidavit, start with 'IN THE HIGH COURT OF...'.\n";
        $prompt .= "4. Do NOT use placeholder brackets (e.g., [Name]); use the provided data. If data is missing, use a professional blank line like '____________________'.\n";
        $prompt .= "5. Output ONLY the document content in clean HTML format (using <h1>, <p>, <ul>, <li>, and <strong> tags). No CSS, no head/body tags.\n";

        return $prompt;
    }
}

