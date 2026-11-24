<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repair custom_apis.id AUTO_INCREMENT.
 *
 * Fixed 2026-11-24: the original version ran
 *   ALTER TABLE custom_apis MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT
 * which MySQL rejects with error 1075 ("there can be only one auto column
 * and it must be defined as a key") because the production table's id has
 * no key at all. This migration never completed anywhere, so it is
 * corrected in place: it now adds the PRIMARY KEY in the same statement
 * when id is not already a key, and no-ops when id already auto-increments.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('custom_apis') || DB::getDriverName() !== 'mysql') {
            return;
        }

        $column = DB::selectOne(
            "SELECT COLUMN_TYPE, EXTRA, COLUMN_KEY
               FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'custom_apis'
                AND COLUMN_NAME = 'id'"
        );

        if (!$column || str_contains(strtolower((string) $column->EXTRA), 'auto_increment')) {
            return; // Missing column or already healthy.
        }

        $type = $column->COLUMN_TYPE ?: 'bigint unsigned';

        if ($column->COLUMN_KEY === 'PRI') {
            DB::statement("ALTER TABLE `custom_apis` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT");
        } else {
            DB::statement("ALTER TABLE `custom_apis` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)");
        }
    }

    public function down(): void
    {
        // The primary key must remain auto-incrementing for Eloquent inserts.
    }
};
