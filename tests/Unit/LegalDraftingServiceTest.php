<?php

namespace Tests\Unit;

use App\Services\LegalDrafting\LegalDraftRequest;
use App\Services\LegalDrafting\LegalDraftingService;
use App\Services\LegalDrafting\Providers\DraftingProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalDraftingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_first_available_provider(): void
    {
        $p1 = new class implements DraftingProvider {
            public function canDraft(): bool { return true; }
            public function name(): string { return 'p1'; }
            public function draftHtml(LegalDraftRequest $req): string { return '<p>one</p>'; }
        };
        $p2 = new class implements DraftingProvider {
            public function canDraft(): bool { return true; }
            public function name(): string { return 'p2'; }
            public function draftHtml(LegalDraftRequest $req): string { return '<p>two</p>'; }
        };

        $svc = new LegalDraftingService([$p1, $p2]);
        $res = $svc->draftHtml(new LegalDraftRequest('nda', 'Business', ['x' => 1]));

        $this->assertTrue($res['ok']);
        $this->assertEquals('p1', $res['provider']);
        $this->assertEquals('<p>one</p>', $res['html']);
    }

    public function test_falls_back_when_provider_throws(): void
    {
        $bad = new class implements DraftingProvider {
            public function canDraft(): bool { return true; }
            public function name(): string { return 'bad'; }
            public function draftHtml(LegalDraftRequest $req): string { throw new \RuntimeException('fail'); }
        };
        $good = new class implements DraftingProvider {
            public function canDraft(): bool { return true; }
            public function name(): string { return 'good'; }
            public function draftHtml(LegalDraftRequest $req): string { return '<p>ok</p>'; }
        };

        $svc = new LegalDraftingService([$bad, $good]);
        $res = $svc->draftHtml(new LegalDraftRequest('nda', 'Business', []));

        $this->assertTrue($res['ok']);
        $this->assertEquals('good', $res['provider']);
        $this->assertEquals('<p>ok</p>', $res['html']);
    }
}

