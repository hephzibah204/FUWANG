<?php

namespace Tests\Unit;

use App\Services\GeminiService;
use App\Services\LegalDrafting\LegalDraftRequest;
use App\Services\LegalDrafting\LegalDraftingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeminiServiceAdapterTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_legal_document_delegates_to_legal_drafting_service(): void
    {
        $fake = new class extends LegalDraftingService {
            public function __construct() {}
            public function draftHtml(LegalDraftRequest $req): array
            {
                return ['ok' => true, 'provider' => 'fake', 'html' => '<p>draft</p>'];
            }
        };

        $this->app->instance(LegalDraftingService::class, $fake);

        $svc = new GeminiService();
        $res = $svc->draftLegalDocument('nda', ['category' => 'Business', 'x' => 1]);

        $this->assertTrue($res['status']);
        $this->assertEquals('<p>draft</p>', $res['text']);
        $this->assertEquals('fake', $res['provider']);
    }
}

