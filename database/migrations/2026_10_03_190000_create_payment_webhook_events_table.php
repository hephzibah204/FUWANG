<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_webhook_events')) {
            Schema::create('payment_webhook_events', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 50)->index();
                $table->string('event_type', 100)->nullable();
                $table->string('provider_event_id', 191)->nullable()->index();
                $table->string('reference', 191)->nullable()->index();
                $table->string('email', 191)->nullable()->index();
                $table->decimal('amount', 18, 2)->nullable();
                $table->string('currency', 10)->nullable();
                $table->boolean('signature_valid')->default(false);
                $table->text('signature')->nullable();
                $table->json('payload')->nullable();
                $table->string('processing_status', 50)->default('pending')->index();
                $table->text('processing_error')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->index(['provider', 'provider_event_id'], 'idx_pwe_provider_event');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhook_events');
    }
};
