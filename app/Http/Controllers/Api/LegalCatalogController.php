<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LegalCatalog\LegalCatalogService;
use App\Services\LegalCatalog\LegalPricingService;
use Illuminate\Http\Request;

class LegalCatalogController extends Controller
{
    public function index(Request $request, LegalCatalogService $catalog, LegalPricingService $pricing)
    {
        $grouped = $catalog->groupedCatalog();

        $out = [];
        foreach ($grouped as $category => $docs) {
            $out[] = [
                'category' => (string) $category,
                'documents' => $docs->values()->map(function ($d) use ($pricing) {
                    return [
                        'document_type' => $d->document_type,
                        'category' => $d->category,
                        'price' => $pricing->priceFor($d->document_type, (float) $d->price),
                        'requires_court_stamp' => (bool) $d->requires_court_stamp,
                        'description' => (string) ($d->description ?? ''),
                    ];
                })->all(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $out,
        ]);
    }

    public function show(string $documentType, LegalCatalogService $catalog, LegalPricingService $pricing)
    {
        $d = $catalog->findByType($documentType);
        if (!$d) {
            return response()->json(['success' => false, 'message' => 'Unknown document type.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'document_type' => $d->document_type,
                'category' => $d->category,
                'price' => $pricing->priceFor($d->document_type, (float) $d->price),
                'requires_court_stamp' => (bool) $d->requires_court_stamp,
                'description' => (string) ($d->description ?? ''),
            ],
        ]);
    }

    public function pricing(string $documentType, LegalCatalogService $catalog, LegalPricingService $pricing)
    {
        $d = $catalog->findByType($documentType);
        if (!$d) {
            return response()->json(['success' => false, 'message' => 'Unknown document type.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'document_type' => $d->document_type,
                'price' => $pricing->priceFor($d->document_type, (float) $d->price),
            ],
        ]);
    }
}

