<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LegalDocument;
use App\Services\WalletService;
use App\Services\LegalCatalog\LegalCatalogService;
use App\Services\LegalCatalog\LegalPricingService;
use App\Services\LegalDrafting\LegalDraftingService;
use App\Services\LegalDrafting\LegalDraftRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class LegalHubController extends Controller
{
    /**
     * Display the Legal Hub Index Page
     */
    public function index()
    {
        $catalog = app(LegalCatalogService::class)->groupedCatalog();
        $pricing = app(LegalPricingService::class);
        $docTypes = $catalog->map(function ($docs, $category) use ($pricing) {
            return $docs->map(function ($d) use ($pricing) {
                $d->price = $pricing->priceFor($d->document_type, (float) $d->price);
                return $d;
            });
        });

        $myDocuments = LegalDocument::where('user_id', Auth::id())->latest()->get();

        return view('services.legal-hub', compact('docTypes', 'myDocuments'));
    }

    /**
     * Step 1: Generate AI Draft (Free Preview)
     */
    public function generateDraft(Request $request)
    {
        $request->validate([
            'document_type' => 'required|string',
            'category' => 'required|string',
            'principal_name' => 'required|string',
            'document_details' => 'required|string',
        ]);

        $catalog = app(LegalCatalogService::class);
        $setting = $catalog->findByType($request->document_type);
        if (!$setting) {
            return response()->json(['status' => false, 'message' => 'Unknown document type.']);
        }

        $pricing = app(LegalPricingService::class);
        $price = $pricing->priceFor($setting->document_type, (float) $setting->price);

        $drafting = app(LegalDraftingService::class);
        $res = $drafting->draftHtml(new LegalDraftRequest(
            (string) $setting->document_type,
            (string) $setting->category,
            [
                'principal_name' => $request->principal_name,
                'details' => $request->document_details,
                'date' => now()->format('F j, Y'),
                'category' => $request->category,
            ],
            'You are a legal drafting AI specializing in Nigerian Law.'
        ));
        if (!($res['ok'] ?? false)) {
            return response()->json(['status' => false, 'message' => $res['message'] ?? 'AI drafting unavailable.']);
        }
        $content = (string) $res['html'];

        // Create a temporary draft record
        $doc = LegalDocument::create([
            'user_id' => Auth::id(),
            'document_type' => $request->document_type,
            'document_name' => ucwords(str_replace('_', ' ', $request->document_type)),
            'reference_id' => 'DFT-' . strtoupper(Str::random(8)),
            'form_data' => $request->all(),
            'content' => $content,
            'status' => 'draft',
            'price' => $price,
        ]);

        return response()->json([
            'status' => true,
            'content' => $content,
            'doc_id' => $doc->id,
            'reference' => $doc->reference_id
        ]);
    }

    /**
     * Step 2: Pay & Finalize (Remove Watermark, Apply Stamp, Save PDF)
     */
    public function finalize(Request $request)
    {
        $request->validate(['doc_id' => 'required|exists:legal_documents,id']);
        
        $doc = LegalDocument::findOrFail($request->doc_id);
        if ($doc->user_id !== Auth::id()) abort(403);
        if ($doc->status !== 'draft') return response()->json(['status' => false, 'message' => 'Document already finalized.']);

        $user = Auth::user();
        $price = $doc->price;

        // Finalize Document
        $finalRef = str_replace('DFT-', 'NX-LEG-', $doc->reference_id);
        $wallet = app(WalletService::class);
        $debit = $wallet->debit($user, (float) $price, 'Legal Hub: ' . $doc->document_name, 'LEG', $finalRef);
        if (!$debit['ok']) {
            return response()->json(['status' => false, 'message' => $debit['message']]);
        }

        $doc->reference_id = $finalRef;
        $fileName = $doc->reference_id . '.pdf';
        $filePath = 'legal_docs/' . $fileName;

        try {
            $pricing = app(LegalPricingService::class);
            // Render PDF with stamp (No watermark for final)
            $pdf = Pdf::loadView('pdf.legal_document', [
                'content' => $doc->content,
                'reference' => $doc->reference_id,
                'date' => $doc->created_at->format('F j, Y'),
                'is_final' => true,
                'stamp_url' => $pricing->stampAssetPath()
            ]);
            
            Storage::disk('public')->put($filePath, $pdf->output());

            $doc->update([
                'file_path' => $filePath,
                'status' => 'completed',
                'is_stamped' => true
            ]);

            $wallet->markTransactionSuccess($finalRef);

            return response()->json([
                'status' => true,
                'message' => 'Document finalized and certified.',
                'download_url' => Storage::url($filePath)
            ]);
        } catch (\Exception $e) {
            $wallet->failAndRefund($user, (float) $price, 'Legal Hub: ' . $doc->document_name, $finalRef);
            return response()->json(['status' => false, 'message' => 'Error finalizing document: ' . $e->getMessage()]);
        }
    }
}
