<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\NotarySetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLegalCatalogApiTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): Admin
    {
        return Admin::create([
            'username' => 'root',
            'email' => 'root@example.com',
            'password' => 'secret',
            'is_super_admin' => true,
        ]);
    }

    public function test_super_admin_can_create_catalog_item(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin, 'admin');

        $resp = $this->postJson(route('admin.legal_catalog.store'), [
            'document_type' => 'tenancy_agreement',
            'category' => 'Property & Rental',
            'price' => 5000,
            'requires_court_stamp' => false,
            'description' => 'Tenancy agreement',
        ]);

        $resp->assertStatus(201)->assertJsonPath('success', true);
        $this->assertDatabaseHas('notary_settings', ['document_type' => 'tenancy_agreement']);
    }

    public function test_branding_is_rejected(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin, 'admin');

        $resp = $this->postJson(route('admin.legal_catalog.store'), [
            'document_type' => 'branding',
            'category' => 'System',
            'price' => 0,
            'requires_court_stamp' => false,
        ]);

        $resp->assertStatus(422);
    }

    public function test_super_admin_can_update_catalog_item(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin, 'admin');

        $item = NotarySetting::create([
            'document_type' => 'nda',
            'category' => 'Business Agreements',
            'price' => 2000,
            'requires_court_stamp' => false,
            'description' => 'NDA',
        ]);

        $resp = $this->putJson(route('admin.legal_catalog.update', $item), [
            'price' => 2500,
            'requires_court_stamp' => true,
        ]);

        $resp->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('notary_settings', ['document_type' => 'nda', 'price' => 2500]);
    }
}

