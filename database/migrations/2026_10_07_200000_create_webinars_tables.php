<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('webinars')) {
            Schema::create('webinars', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('title');
                $table->string('subtitle')->nullable();
                $table->text('description')->nullable();
                $table->string('category')->default('general');
                $table->string('presenter_name');
                $table->string('presenter_title')->nullable();
                $table->string('presenter_avatar')->nullable();
                $table->dateTime('scheduled_at');
                $table->integer('duration_minutes')->default(60);
                $table->enum('status', ['draft', 'upcoming', 'live', 'ended', 'cancelled'])->default('upcoming');
                $table->string('meeting_provider')->default('custom'); // zoom, google_meet, youtube, custom
                $table->text('join_url')->nullable();
                $table->text('recording_url')->nullable();
                $table->decimal('price', 12, 2)->default(0.00); // 0 = free
                $table->integer('max_attendees')->nullable(); // null = unlimited
                $table->string('banner_image')->nullable();
                $table->json('agenda')->nullable();
                $table->json('features')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('webinar_registrations')) {
            Schema::create('webinar_registrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('webinar_id')->constrained('webinars')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('full_name');
                $table->string('email');
                $table->string('phone_number')->nullable();
                $table->string('organization')->nullable();
                $table->string('access_code')->unique();
                $table->enum('status', ['confirmed', 'cancelled', 'attended'])->default('confirmed');
                $table->decimal('amount_paid', 12, 2)->default(0.00);
                $table->string('payment_reference')->nullable();
                $table->dateTime('attended_at')->nullable();
                $table->timestamps();

                $table->unique(['webinar_id', 'email']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('webinar_registrations');
        Schema::dropIfExists('webinars');
    }
};
