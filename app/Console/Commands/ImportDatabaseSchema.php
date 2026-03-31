<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Exception;

class ImportDatabaseSchema extends Command
{
    protected $signature = 'db:import-schema {--file= : Specific path to the SQL file} {--silent : Do not prompt for confirmation}';

    protected $description = 'Detects, locates, and imports a .sql schema file into the database';

    public function handle()
    {
        $this->info("Starting Database Schema Import Process...");

        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");

        $this->line("Detected Database Connection: <comment>{$connection}</comment>");
        if (isset($dbConfig['host'])) {
            $this->line("Host: <comment>{$dbConfig['host']}</comment>");
        }
        $this->line("Database: <comment>{$dbConfig['database']}</comment>");
        if (isset($dbConfig['username'])) {
            $this->line("Username: <comment>{$dbConfig['username']}</comment>");
        }

        $sqlFile = $this->option('file');

        if (!$sqlFile) {
            $sqlFile = $this->scanForSqlFiles();
        }

        if (!$sqlFile || !File::exists($sqlFile)) {
            $this->error("No valid .sql files found or specified file does not exist.");
            Log::error("Schema Import Failed: No .sql file found.");
            return 2;
        }

        $this->info("Found SQL file: <comment>{$sqlFile}</comment>");

        if (!$this->validateSqlFile($sqlFile, $connection)) {
            $this->error("SQL file validation failed. Ensure it contains valid SQL and matches your database engine.");
            Log::error("Schema Import Failed: SQL file validation failed for {$sqlFile}.");
            return 1;
        }

        if (!$this->option('silent')) {
            if (!$this->confirm("Are you sure you want to import this schema? This may overwrite existing data.", false)) {
                $this->info("Import cancelled by user.");
                return 0;
            }
        }

        return $this->importSchema($sqlFile) ? 0 : 1;
    }

    private function scanForSqlFiles()
    {
        $directories = [
            base_path('Database'),
            base_path('sql'),
            base_path('database/schema'),
            base_path('scripts'),
        ];

        $foundFiles = [];

        foreach ($directories as $dir) {
            if (File::isDirectory($dir)) {
                $files = File::files($dir);
                foreach ($files as $file) {
                    if ($file->getExtension() === 'sql') {
                        $foundFiles[] = $file->getPathname();
                    }
                }
            }
        }

        if (empty($foundFiles)) {
            return null;
        }

        if (count($foundFiles) === 1) {
            return $foundFiles[0];
        }

        if ($this->option('silent')) {
            return $foundFiles[0];
        }

        return $this->choice(
            'Multiple SQL files found. Which one would you like to import?',
            $foundFiles,
            0
        );
    }

    private function validateSqlFile($path, $connection)
    {
        $content = File::get($path);

        if (empty(trim($content))) {
            $this->error("SQL file is empty.");
            return false;
        }

        $contentUpper = strtoupper($content);
        if ($connection === 'sqlite' && str_contains($contentUpper, 'ENGINE=INNODB')) {
            $this->warn("Warning: SQL file contains MySQL-specific syntax (ENGINE=InnoDB) which may fail on SQLite.");
        }

        if (str_contains($contentUpper, 'DROP DATABASE') && !$this->option('silent')) {
            $this->warn("Warning: SQL file contains DROP DATABASE statements.");
        }

        return true;
    }

    private function importSchema($path)
    {
        $content = File::get($path);
        
        $this->info("Starting import...");
        Log::info("Starting schema import from {$path}");

        DB::beginTransaction();

        try {
            DB::unprepared($content);
            DB::commit();
            
            $this->info("Schema successfully imported!");
            Log::info("Schema import successful.");
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            $this->error("Import failed! Changes have been rolled back.");
            $this->error("Error: " . $e->getMessage());
            Log::error("Schema Import Failed: " . $e->getMessage());
            return false;
        }
    }
}
