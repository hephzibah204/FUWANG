<?php

namespace App\Services\LegalDrafting;

class LegalDraftRequest
{
    public function __construct(
        public string $documentType,
        public string $category,
        public array $formData,
        public ?string $systemPrompt = null,
    ) {
    }
}

