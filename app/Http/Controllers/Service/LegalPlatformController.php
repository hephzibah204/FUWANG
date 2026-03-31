<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\LegalDocument;
use App\Models\NotaryRequest;
use Illuminate\Support\Facades\Auth;

class LegalPlatformController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $legalDocs = LegalDocument::query()
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function ($d) {
                return [
                    'source' => 'ai_legal_hub',
                    'id' => $d->id,
                    'reference' => $d->reference_id,
                    'type' => $d->document_type,
                    'status' => $d->status,
                    'created_at' => $d->created_at,
                    'download_url' => $d->file_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($d->file_path) : null,
                ];
            });

        $notary = NotaryRequest::query()
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function ($r) {
                return [
                    'source' => 'notary',
                    'id' => $r->id,
                    'reference' => $r->reference,
                    'type' => $r->document_type,
                    'status' => $r->status,
                    'created_at' => $r->created_at,
                    'download_url' => $r->final_pdf_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($r->final_pdf_path) : ($r->draft_pdf_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($r->draft_pdf_path) : null),
                ];
            });

        $records = $legalDocs->merge($notary)->sortByDesc('created_at')->values();

        return view('services.legal-platform', compact('records'));
    }
}

