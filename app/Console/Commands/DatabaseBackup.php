<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DatabaseBackup extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Backup the database to a SQL file';

    public function handle()
    {
        $filename = "backup-" . Carbon::now()->format('Y-m-d_H-i-s') . ".sql";
        $path = storage_path('app/backups/' . $filename);

        if (!is_dir(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");

        if ($connection === 'mysql') {
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s %s > %s',
                escapeshellarg($dbConfig['username']),
                escapeshellarg($dbConfig['password']),
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['database']),
                escapeshellarg($path)
            );

            // Try to find mysqldump in XAMPP if not in path
            $xamppPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
            if (file_exists($xamppPath)) {
                $command = str_replace('mysqldump', "\"$xamppPath\"", $command);
            }

            exec($command, $output, $returnVar);

            if ($returnVar === 0) {
                $this->info("Backup successfully created at: {$path}");

                // Upload to cloud if S3 configured
                if (config('filesystems.disks.s3.key')) {
                    $this->info("Uploading to cloud storage...");
                    Storage::disk('s3')->put("backups/{$filename}", file_get_contents($path));
                }

                // Cleanup old backups (older than 7 days)
                $backups = glob(storage_path('app/backups/backup-*.sql'));
                $threshold = now()->subDays(7)->getTimestamp();
                foreach ($backups as $file) {
                    if (filemtime($file) < $threshold) {
                        unlink($file);
                        $this->comment("Deleted old backup: " . basename($file));
                    }
                }
            } else {
                $this->error("Backup failed with exit code: {$returnVar}");
            }
        } else {
            $this->error("Backup currently only supports MySQL.");
        }
    }
}
