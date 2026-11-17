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
        if (Schema::hasTable('price_list')) {
            Schema::table('price_list', function (Blueprint $table) {
                if (!Schema::hasColumn('price_list', 'provider_plan_id')) {
                    $table->string('provider_plan_id', 100)->nullable()->after('validate');
                }
                if (!Schema::hasColumn('price_list', 'custom_api_id')) {
                    $table->unsignedBigInteger('custom_api_id')->nullable()->after('provider_plan_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('price_list')) {
            Schema::table('price_list', function (Blueprint $table) {
                if (Schema::hasColumn('price_list', 'custom_api_id')) {
                    $table->dropColumn('custom_api_id');
                }
                if (Schema::hasColumn('price_list', 'provider_plan_id')) {
                    $table->dropColumn('provider_plan_id');
                }
            });
        }
    }
};
