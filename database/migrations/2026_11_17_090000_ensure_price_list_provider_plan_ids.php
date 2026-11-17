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
        // Local row IDs are not Datasub plan IDs. The verified catalogue
        // migrations populate explicit mappings without guessing IDs here.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed for data backfill
    }
};
