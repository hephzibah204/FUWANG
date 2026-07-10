<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'email_verified_at')) {
            return;
        }

        $driver = DB::connection()->getDriverName();
        $fallback = $driver === 'sqlite' ? 'CURRENT_TIMESTAMP' : 'NOW()';

        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => DB::raw("COALESCE(created_at, {$fallback})")]);
    }

    public function down(): void
    {
        // Irreversible: we cannot know which users were previously unverified.
    }
};
