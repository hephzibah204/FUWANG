<?php

namespace App\Database;

use Illuminate\Database\Migrations\DatabaseMigrationRepository;

class SafeDatabaseMigrationRepository extends DatabaseMigrationRepository
{
    /**
     * Record that a migration was run.
     *
     * @param  string  $file
     * @param  int  $batch
     * @return void
     */
    public function log($file, $batch)
    {
        $record = ['migration' => $file, 'batch' => $batch];

        try {
            $this->table()->insert($record);
        } catch (\Throwable $e) {
            // Check for MySQL error 1364: Field 'id' doesn't have a default value
            $message = $e->getMessage();
            if (str_contains($message, '1364') || str_contains($message, "Field 'id' doesn't have a default value")) {
                $maxId = (int) $this->table()->max('id');
                $record['id'] = $maxId + 1;
                $this->table()->insert($record);

                // Asynchronously attempt to restore AUTO_INCREMENT on id so subsequent operations are self-healing
                try {
                    $connection = $this->getConnection();
                    if ($connection->getDriverName() === 'mysql') {
                        \App\Http\Controllers\Admin\SystemDeploymentController::repairMigrationsTable();
                    }
                } catch (\Throwable $ignored) {}
            } else {
                throw $e;
            }
        }
    }
}
