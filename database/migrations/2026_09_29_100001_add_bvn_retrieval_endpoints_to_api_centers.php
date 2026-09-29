<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('api_centers')) {
            Schema::table('api_centers', function (Blueprint $table) {
                if (!Schema::hasColumn('api_centers', 'dataverify_endpoint_bvn_retrieval')) {
                    $table->text('dataverify_endpoint_bvn_retrieval')->nullable()->after('dataverify_endpoint_bvn');
                }
                if (!Schema::hasColumn('api_centers', 'dataverify_endpoint_bvn_retrieval_status')) {
                    $table->text('dataverify_endpoint_bvn_retrieval_status')->nullable()->after('dataverify_endpoint_bvn_retrieval');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('api_centers')) {
            Schema::table('api_centers', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('api_centers', 'dataverify_endpoint_bvn_retrieval')) {
                    $columns[] = 'dataverify_endpoint_bvn_retrieval';
                }
                if (Schema::hasColumn('api_centers', 'dataverify_endpoint_bvn_retrieval_status')) {
                    $columns[] = 'dataverify_endpoint_bvn_retrieval_status';
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
