<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('feature_toggles') && DB::getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE `feature_toggles` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            } catch (\Throwable $e) {
                try {
                    DB::statement('ALTER TABLE `feature_toggles` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)');
                } catch (\Throwable $inner) {
                    \Illuminate\Support\Facades\Log::warning('Feature toggles auto-increment migration notice: ' . $inner->getMessage());
                }
            }
        }
    }

    public function down(): void
    {
        // The primary key must remain auto-incrementing for Eloquent inserts.
    }
};
