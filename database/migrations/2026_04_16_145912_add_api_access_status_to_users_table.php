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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'api_access_status')) {
                $table->string('api_access_status')->default('none')->index();
            }
            if (! Schema::hasColumn('users', 'api_application_details')) {
                $table->json('api_application_details')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'api_access_status')) {
                $table->dropColumn('api_access_status');
            }
            if (Schema::hasColumn('users', 'api_application_details')) {
                $table->dropColumn('api_application_details');
            }
        });
    }
};
