<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('custom_apis') && DB::getDriverName() === 'mysql') {
            DB::statement(
                'ALTER TABLE `custom_apis` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT'
            );
        }
    }

    public function down(): void
    {
        // The primary key must remain auto-incrementing for Eloquent inserts.
    }
};
