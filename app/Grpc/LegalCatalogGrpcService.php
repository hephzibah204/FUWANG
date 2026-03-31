<?php

namespace App\Grpc;

use App\Services\LegalCatalog\LegalCatalogService;
use App\Services\LegalCatalog\LegalPricingService;

class LegalCatalogGrpcService
{
    public function __construct(private LegalCatalogService $catalog, private LegalPricingService $pricing)
    {
    }

    public function getCatalog(): array
    {
        $grouped = $this->catalog->groupedCatalog();
        $cats = [];
        foreach ($grouped as $category => $docs) {
            $cats[] = [
                'category' => (string) $category,
                'documents' => $docs->values()->map(function ($d) {
                    return [
                        'document_type' => $d->document_type,
                        'category' => $d->category,
                        'price' => $this->pricing->priceFor($d->document_type, (float) $d->price),
                        'requires_court_stamp' => (bool) $d->requires_court_stamp,
                        'description' => (string) ($d->description ?? ''),
                    ];
                })->all(),
            ];
        }
        return ['categories' => $cats];
    }

    public function getDocument(string $documentType): ?array
    {
        $d = $this->catalog->findByType($documentType);
        if (!$d) return null;
        return [
            'document_type' => $d->document_type,
            'category' => $d->category,
            'price' => $this->pricing->priceFor($d->document_type, (float) $d->price),
            'requires_court_stamp' => (bool) $d->requires_court_stamp,
            'description' => (string) ($d->description ?? ''),
        ];
    }

    public function getPricing(string $documentType): ?array
    {
        $d = $this->catalog->findByType($documentType);
        if (!$d) return null;
        return [
            'document_type' => $d->document_type,
            'price' => $this->pricing->priceFor($d->document_type, (float) $d->price),
        ];
    }
}

