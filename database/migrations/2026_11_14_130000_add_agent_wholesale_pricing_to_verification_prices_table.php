<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('verification_prices')) {
            Schema::table('verification_prices', function (Blueprint $table) {
                if (!Schema::hasColumn('verification_prices', 'agent_nin_by_nin_price')) {
                    $table->decimal('agent_nin_by_nin_price', 18, 2)->nullable()->after('nin_by_demography_price');
                }
                if (!Schema::hasColumn('verification_prices', 'agent_nin_by_number_price')) {
                    $table->decimal('agent_nin_by_number_price', 18, 2)->nullable()->after('agent_nin_by_nin_price');
                }
                if (!Schema::hasColumn('verification_prices', 'agent_nin_by_demography_price')) {
                    $table->decimal('agent_nin_by_demography_price', 18, 2)->nullable()->after('agent_nin_by_number_price');
                }
                if (!Schema::hasColumn('verification_prices', 'agent_verify_by_tracking_id')) {
                    $table->decimal('agent_verify_by_tracking_id', 18, 2)->nullable()->after('verify_by_tracking_id');
                }
                if (!Schema::hasColumn('verification_prices', 'agent_validation_price')) {
                    $table->decimal('agent_validation_price', 18, 2)->nullable()->after('validation_price');
                }
                if (!Schema::hasColumn('verification_prices', 'agent_ipe_clearance_price')) {
                    $table->decimal('agent_ipe_clearance_price', 18, 2)->nullable()->after('ipe_clearance_price');
                }
                if (!Schema::hasColumn('verification_prices', 'agent_personalization_price')) {
                    $table->decimal('agent_personalization_price', 18, 2)->nullable()->after('personalization_price');
                }
                if (!Schema::hasColumn('verification_prices', 'agent_bvn_by_bvn')) {
                    $table->decimal('agent_bvn_by_bvn', 18, 2)->nullable()->after('bvn_by_bvn');
                }
                if (!Schema::hasColumn('verification_prices', 'agent_bvn_by_number')) {
                    $table->decimal('agent_bvn_by_number', 18, 2)->nullable()->after('bvn_by_number');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('verification_prices')) {
            Schema::table('verification_prices', function (Blueprint $table) {
                $columns = [
                    'agent_nin_by_nin_price',
                    'agent_nin_by_number_price',
                    'agent_nin_by_demography_price',
                    'agent_verify_by_tracking_id',
                    'agent_validation_price',
                    'agent_ipe_clearance_price',
                    'agent_personalization_price',
                    'agent_bvn_by_bvn',
                    'agent_bvn_by_number',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('verification_prices', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
