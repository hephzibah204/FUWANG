<?php

namespace App\Services\LegalDrafting\Providers;

use App\Services\LegalDrafting\LegalDraftRequest;

interface DraftingProvider
{
    public function canDraft(): bool;

    public function name(): string;

    public function draftHtml(LegalDraftRequest $req): string;
}

