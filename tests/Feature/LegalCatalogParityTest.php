<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LegalCatalogParityTest extends TestCase
{
    use RefreshDatabase;

    public function test_notary_and_legal_hub_share_same_catalog_source(): void
    {
        $this->seed(\Database\Seeders\NotaryCategoriesSeeder::class);

        $u = User::factory()->create();
        $this->actingAs($u);

        $notary = $this->get(route('services.notary'));
        $notary->assertOk();
        $notaryDocTypes = collect($notary->viewData('docTypes'))
            ->flatten(1)
            ->pluck('document_type')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $legalHub = $this->get(route('services.legal-hub'));
        $legalHub->assertOk();
        $hubDocTypes = collect($legalHub->viewData('docTypes'))
            ->flatten(1)
            ->pluck('document_type')
            ->filter()
            ->unique()
            ->values()
            ->all();

        sort($notaryDocTypes);
        sort($hubDocTypes);
        $this->assertEquals($notaryDocTypes, $hubDocTypes);

        $dbTypes = DB::table('notary_settings')
            ->whereNotIn('document_type', ['branding'])
            ->pluck('document_type')
            ->filter()
            ->unique()
            ->values()
            ->all();
        sort($dbTypes);
        $this->assertEquals($dbTypes, $hubDocTypes);
    }
}

