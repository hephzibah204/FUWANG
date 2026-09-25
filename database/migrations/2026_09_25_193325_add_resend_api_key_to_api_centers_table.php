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
        Schema::table('api_centers', function (Blueprint $table) {
            $table->text('resend_api_key')->nullable()->after('gemini_api_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_centers', function (Blueprint $table) {
            $table->dropColumn('resend_api_key');
        });
    }
};
