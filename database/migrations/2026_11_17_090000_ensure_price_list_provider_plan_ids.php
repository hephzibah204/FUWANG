<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('price_list') && Schema::hasColumn('price_list', 'provider_plan_id')) {
            DB::table('price_list')
                ->whereNull('provider_plan_id')
                ->orWhere('provider_plan_id', '')
                ->update(['provider_plan_id' => DB::raw('id')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed for data backfill
    }
};
