<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bvn_retrieval_requests')) {
            Schema::create('bvn_retrieval_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('phone_number', 30)->index();
                $table->string('full_name', 190);
                $table->string('dob', 30)->nullable();
                $table->string('transaction_id', 100)->unique();
                $table->string('provider_transaction_id', 150)->nullable()->index();
                $table->string('provider', 50)->default('dataverify');
                $table->decimal('amount', 12, 2)->default(0.00);
                $table->string('status', 30)->default('pending')->index(); // pending, completed, failed, refunded
                $table->string('retrieved_bvn', 20)->nullable()->index();
                $table->json('provider_response')->nullable();
                $table->text('failure_reason')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('refunded_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bvn_retrieval_requests');
    }
};
