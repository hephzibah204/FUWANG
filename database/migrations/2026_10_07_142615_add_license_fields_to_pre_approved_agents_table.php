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
        Schema::table('pre_approved_agents', function (Blueprint $table) {
            if (!Schema::hasColumn('pre_approved_agents', 'has_paid_license')) {
                $table->boolean('has_paid_license')->default(false)->after('is_claimed');
            }
            if (!Schema::hasColumn('pre_approved_agents', 'license_payment_method')) {
                $table->string('license_payment_method', 50)->nullable()->after('has_paid_license');
            }
            if (!Schema::hasColumn('pre_approved_agents', 'license_notes')) {
                $table->text('license_notes')->nullable()->after('license_payment_method');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_approved_agents', function (Blueprint $table) {
            $table->dropColumn(['has_paid_license', 'license_payment_method', 'license_notes']);
        });
    }
};
