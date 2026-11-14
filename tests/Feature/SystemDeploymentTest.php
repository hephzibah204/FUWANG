<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\SystemDeploymentController;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemDeploymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_repair_migrations_table_executes_safely(): void
    {
        $result = SystemDeploymentController::repairMigrationsTable();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertContains($result['status'], ['ok', 'repaired', 'skipped']);
    }

    public function test_super_admin_can_trigger_migration_endpoint(): void
    {
        $superAdmin = Admin::factory()->create(['is_super_admin' => true]);

        $response = $this->actingAs($superAdmin, 'admin')
            ->post(route('admin.settings.deployment.migrate'));

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'message' => 'Database migrations finished.',
        ]);
    }

    public function test_unauthorized_user_cannot_trigger_migration_endpoint(): void
    {
        $response = $this->post(route('admin.settings.deployment.migrate'));
        $response->assertRedirect();
    }
}
