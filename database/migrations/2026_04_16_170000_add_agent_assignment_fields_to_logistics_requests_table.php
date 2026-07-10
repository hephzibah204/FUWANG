<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('logistics_requests')) {
            return;
        }

        Schema::table('logistics_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('logistics_requests', 'agent_assignment_status')) {
                $table->string('agent_assignment_status')->default('pending')->index();
            }
            if (! Schema::hasColumn('logistics_requests', 'agent_assignment_responded_at')) {
                $table->timestamp('agent_assignment_responded_at')->nullable()->index();
            }
            if (! Schema::hasColumn('logistics_requests', 'agent_fee_amount')) {
                $table->decimal('agent_fee_amount', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('logistics_requests', 'agent_commission_amount')) {
                $table->decimal('agent_commission_amount', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('logistics_requests', 'agent_paid_at')) {
                $table->timestamp('agent_paid_at')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('logistics_requests')) {
            return;
        }

        Schema::table('logistics_requests', function (Blueprint $table) {
            $drop = [];
            foreach ([
                'agent_assignment_status',
                'agent_assignment_responded_at',
                'agent_fee_amount',
                'agent_commission_amount',
                'agent_paid_at',
            ] as $col) {
                if (Schema::hasColumn('logistics_requests', $col)) {
                    $drop[] = $col;
                }
            }
            if ($drop) {
                $table->dropColumn($drop);
            }
        });
    }
};

