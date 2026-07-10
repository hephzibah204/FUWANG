<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('logistics_requests')) {
            Schema::create('logistics_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('sender_name')->nullable();
                $table->text('sender_address')->nullable();
                $table->string('recipient_name')->nullable();
                $table->text('recipient_address')->nullable();
                $table->decimal('weight', 10, 2)->nullable();
                $table->text('description')->nullable();
                $table->string('delivery_type')->nullable();
                $table->decimal('amount', 10, 2)->nullable();
                $table->string('tracking_id')->unique();
                $table->string('status')->default('processing')->index();
                $table->string('waybill_path')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('logistics_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('logistics_requests', 'assigned_manager_id')) {
                $table->foreignId('assigned_manager_id')->nullable()->index();
            }
            if (! Schema::hasColumn('logistics_requests', 'assigned_officer_id')) {
                $table->foreignId('assigned_officer_id')->nullable()->index();
            }
            if (! Schema::hasColumn('logistics_requests', 'assigned_delivery_agent_id')) {
                $table->foreignId('assigned_delivery_agent_id')->nullable()->index();
            }
            if (! Schema::hasColumn('logistics_requests', 'scheduled_pickup_at')) {
                $table->timestamp('scheduled_pickup_at')->nullable()->index();
            }
            if (! Schema::hasColumn('logistics_requests', 'route_code')) {
                $table->string('route_code')->nullable()->index();
            }
            if (! Schema::hasColumn('logistics_requests', 'last_status_updated_at')) {
                $table->timestamp('last_status_updated_at')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('logistics_requests')) {
            return;
        }

        Schema::table('logistics_requests', function (Blueprint $table) {
            if (Schema::hasColumn('logistics_requests', 'assigned_delivery_agent_id')) {
                $table->dropColumn('assigned_delivery_agent_id');
            }
            if (Schema::hasColumn('logistics_requests', 'assigned_officer_id')) {
                $table->dropColumn('assigned_officer_id');
            }
            if (Schema::hasColumn('logistics_requests', 'assigned_manager_id')) {
                $table->dropColumn('assigned_manager_id');
            }
            if (Schema::hasColumn('logistics_requests', 'scheduled_pickup_at')) {
                $table->dropColumn('scheduled_pickup_at');
            }
            if (Schema::hasColumn('logistics_requests', 'route_code')) {
                $table->dropColumn('route_code');
            }
            if (Schema::hasColumn('logistics_requests', 'last_status_updated_at')) {
                $table->dropColumn('last_status_updated_at');
            }
        });
    }
};
