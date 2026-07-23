<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ImportDatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    protected $tempDir;
    protected $tempFile;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->tempDir = base_path('Database');
        if (!File::exists($this->tempDir)) {
            File::makeDirectory($this->tempDir);
        }
        
        $this->tempFile = $this->tempDir . '/test_schema.sql';
    }

    protected function tearDown(): void
    {
        if (File::exists($this->tempFile)) {
            File::delete($this->tempFile);
        }
        parent::tearDown();
    }

    public function test_it_detects_and_imports_valid_sql_file()
    {
        // Arrange
        $sql = "CREATE TABLE IF NOT EXISTS test_table_schema (id INT, name VARCHAR(50)); INSERT INTO test_table_schema (id, name) VALUES (1, 'Test');";
        File::put($this->tempFile, $sql);

        // Act
        $this->artisan('db:import-schema', ['--file' => $this->tempFile, '--yes' => true])
             ->assertExitCode(0);

        // Assert
        $this->assertDatabaseHas('test_table_schema', [
            'name' => 'Test'
        ]);
    }

    public function test_it_fails_gracefully_with_invalid_sql_and_rolls_back()
    {
        // Arrange
        DB::statement('CREATE TABLE IF NOT EXISTS rollback_test (id INT PRIMARY KEY)');
        
        // This SQL selects from a nonexistent table which will fail
        $sql = "INSERT INTO rollback_test (id) VALUES (99); SELECT * FROM nonexistent_table_xyz_abc_99999;";
        File::put($this->tempFile, $sql);

        // Act - expect failure (exit code 1)
        $result = $this->artisan('db:import-schema', ['--file' => $this->tempFile, '--yes' => true]);
        $result->assertExitCode(1);

        // Assert: The insert should be rolled back
        $this->assertDatabaseMissing('rollback_test', [
            'id' => 99
        ]);
    }

    public function test_it_handles_missing_file_gracefully()
    {
        // Act & Assert
        $this->artisan('db:import-schema', [
            '--file' => '/path/to/nonexistent/file.sql',
            '--yes' => true
        ])
        ->assertExitCode(1);
    }
    
    public function test_it_prompts_for_confirmation_when_not_silent()
    {
        // Arrange
        $sql = "CREATE TABLE IF NOT EXISTS test_prompt (id INT);";
        File::put($this->tempFile, $sql);

        // Act & Assert
        $this->artisan('db:import-schema', ['--file' => $this->tempFile])
             ->expectsConfirmation('Are you sure you want to import this schema? This may overwrite existing data.', 'no')
             ->expectsOutputToContain('Import cancelled by user.')
             ->assertExitCode(0);
    }
}