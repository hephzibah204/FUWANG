<?php

namespace App\Services\LegalCatalog;

use App\Models\NotarySetting;
use Illuminate\Support\Collection;

class LegalCatalogService
{
    public function groupedCatalog(): Collection
    {
        return NotarySetting::query()
            ->whereNotIn('document_type', ['branding'])
            ->orderBy('category')
            ->orderBy('document_type')
            ->get()
            ->groupBy('category');
    }

    public function findByType(string $documentType): ?NotarySetting
    {
        return NotarySetting::query()->where('document_type', $documentType)->first();
    }
}

