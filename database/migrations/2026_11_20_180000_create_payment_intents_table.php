<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('payment_intents')) {
            return;
        }

        Schema::create('payment_intents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('reference', 120)->unique();
            $table->string('gateway')->nullable();
            $table->decimal('amount_expected', 16, 2);
            $table->char('currency', 3);
            $table->string('status')->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Existing installations may own this table and its payment history.
    }
};
