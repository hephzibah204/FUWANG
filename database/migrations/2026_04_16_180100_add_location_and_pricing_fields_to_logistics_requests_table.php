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
            if (! Schema::hasColumn('logistics_requests', 'sender_state')) {
                $table->string('sender_state')->nullable()->index();
            }
            if (! Schema::hasColumn('logistics_requests', 'sender_city')) {
                $table->string('sender_city')->nullable()->index();
            }
            if (! Schema::hasColumn('logistics_requests', 'recipient_state')) {
                $table->string('recipient_state')->nullable()->index();
            }
            if (! Schema::hasColumn('logistics_requests', 'recipient_city')) {
                $table->string('recipient_city')->nullable()->index();
            }
            if (! Schema::hasColumn('logistics_requests', 'pickup_method')) {
                $table->string('pickup_method')->default('center_dropoff')->index();
            }
            if (! Schema::hasColumn('logistics_requests', 'delivery_method')) {
                $table->string('delivery_method')->default('home_delivery')->index();
            }
            if (! Schema::hasColumn('logistics_requests', 'pickup_center_id')) {
                $table->foreignId('pickup_center_id')->nullable()->index();
            }
            if (! Schema::hasColumn('logistics_requests', 'dropoff_center_id')) {
                $table->foreignId('dropoff_center_id')->nullable()->index();
            }
            if (! Schema::hasColumn('logistics_requests', 'sender_lat')) {
                $table->decimal('sender_lat', 10, 7)->nullable();
            }
            if (! Schema::hasColumn('logistics_requests', 'sender_lng')) {
                $table->decimal('sender_lng', 10, 7)->nullable();
            }
            if (! Schema::hasColumn('logistics_requests', 'recipient_lat')) {
                $table->decimal('recipient_lat', 10, 7)->nullable();
            }
            if (! Schema::hasColumn('logistics_requests', 'recipient_lng')) {
                $table->decimal('recipient_lng', 10, 7)->nullable();
            }
            if (! Schema::hasColumn('logistics_requests', 'distance_km')) {
                $table->decimal('distance_km', 10, 2)->nullable()->index();
            }
            if (! Schema::hasColumn('logistics_requests', 'package_length_cm')) {
                $table->decimal('package_length_cm', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('logistics_requests', 'package_width_cm')) {
                $table->decimal('package_width_cm', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('logistics_requests', 'package_height_cm')) {
                $table->decimal('package_height_cm', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('logistics_requests', 'price_breakdown')) {
                $table->json('price_breakdown')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('logistics_requests')) {
            return;
        }

        Schema::table('logistics_requests', function (Blueprint $table) {
            if (Schema::hasColumn('logistics_requests', 'dropoff_center_id')) {
                $table->dropColumn('dropoff_center_id');
            }
            if (Schema::hasColumn('logistics_requests', 'pickup_center_id')) {
                $table->dropColumn('pickup_center_id');
            }

            $cols = [
                'sender_state',
                'sender_city',
                'recipient_state',
                'recipient_city',
                'pickup_method',
                'delivery_method',
                'sender_lat',
                'sender_lng',
                'recipient_lat',
                'recipient_lng',
                'distance_km',
                'package_length_cm',
                'package_width_cm',
                'package_height_cm',
                'price_breakdown',
            ];

            $drop = [];
            foreach ($cols as $col) {
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
