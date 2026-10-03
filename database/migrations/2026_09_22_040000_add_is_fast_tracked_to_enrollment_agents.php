<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('enrollment_agents', function (Blueprint $table) {
            if (!Schema::hasColumn('enrollment_agents', 'is_fast_tracked')) {
                $table->boolean('is_fast_tracked')->default(false)->after('company_agent_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_agents', function (Blueprint $table) {
            $table->dropColumn(['is_fast_tracked']);
        });
    }
};
