<?php

namespace App\Services\LegalDrafting\Providers;

use App\Services\LegalDrafting\LegalDraftRequest;

class FallbackProvider implements DraftingProvider
{
    public function canDraft(): bool
    {
        return true;
    }

    public function name(): string
    {
        return 'fallback';
    }

    public function draftHtml(LegalDraftRequest $req): string
    {
        $type = ucwords(str_replace('_', ' ', $req->documentType));
        $category = $req->category ?: 'Miscellaneous';
        $data = json_encode($req->formData, JSON_PRETTY_PRINT);
        return "<h1>{$type}</h1><p><strong>Category:</strong> {$category}</p><p>AI drafting is not configured. Provided data:</p><pre>{$data}</pre>";
    }
}

