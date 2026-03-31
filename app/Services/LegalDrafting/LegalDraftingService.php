<?php

namespace App\Services\LegalDrafting;

use App\Services\LegalDrafting\Providers\DraftingProvider;
use App\Services\LegalDrafting\Providers\FallbackProvider;
use App\Services\LegalDrafting\Providers\GeminiFlashProvider;
use App\Services\LegalDrafting\Providers\OpenAiChatProvider;
use Illuminate\Support\Facades\Log;

class LegalDraftingService
{
    /** @var DraftingProvider[] */
    private array $providers;

    public function __construct(?array $providers = null)
    {
        $this->providers = $providers ?: [
            new GeminiFlashProvider(),
            new OpenAiChatProvider(),
            new FallbackProvider(),
        ];
    }

    public function draftHtml(LegalDraftRequest $req): array
    {
        foreach ($this->providers as $p) {
            if (!$p->canDraft()) {
                continue;
            }
            try {
                $html = $p->draftHtml($req);
                return [
                    'ok' => true,
                    'provider' => $p->name(),
                    'html' => $html,
                ];
            } catch (\Throwable $e) {
                Log::warning('LegalDrafting provider failed', [
                    'provider' => $p->name(),
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        return [
            'ok' => false,
            'provider' => null,
            'html' => null,
            'message' => 'Drafting service unavailable.',
        ];
    }
}
