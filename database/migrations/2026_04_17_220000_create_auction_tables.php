<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('auction_sellers')) {
            Schema::create('auction_sellers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('location')->nullable();
                $table->decimal('rating', 3, 1)->nullable();
                $table->unsignedInteger('reviews_count')->default(0);
                $table->boolean('verified')->default(false);
                $table->string('avatar_url')->nullable();
                $table->text('about')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('auction_lots')) {
            Schema::create('auction_lots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seller_id')->nullable()->constrained('auction_sellers')->nullOnDelete();
                $table->string('lot_code')->unique();
                $table->string('title');
                $table->string('category')->nullable();
                $table->string('location')->nullable();
                $table->text('description')->nullable();
                $table->decimal('starting_price', 12, 2)->default(0);
                $table->decimal('current_price', 12, 2)->default(0);
                $table->decimal('bid_increment', 12, 2)->default(0);
                $table->timestamp('start_at')->nullable();
                $table->timestamp('end_at')->nullable();
                $table->string('status')->default('scheduled');
                $table->boolean('featured')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['status', 'end_at']);
                $table->index(['category']);
                $table->index(['location']);
            });
        }

        if (! Schema::hasTable('auction_lot_images')) {
            Schema::create('auction_lot_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('auction_lot_id')->constrained('auction_lots')->cascadeOnDelete();
                $table->string('url');
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['auction_lot_id', 'sort_order']);
            });
        }

        if (! Schema::hasTable('auction_bids')) {
            Schema::create('auction_bids', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('lot_id');
                $table->string('item_name')->nullable();
                $table->decimal('bid_amount', 12, 2);
                $table->string('status')->default('winning');
                $table->string('reference')->unique();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['lot_id', 'status']);
                $table->index(['user_id']);
            });
        }

        if (! Schema::hasTable('auction_watchlists')) {
            Schema::create('auction_watchlists', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('lot_code');
                $table->timestamps();

                $table->unique(['user_id', 'lot_code']);
                $table->index(['lot_code']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_watchlists');
        Schema::dropIfExists('auction_bids');
        Schema::dropIfExists('auction_lot_images');
        Schema::dropIfExists('auction_lots');
        Schema::dropIfExists('auction_sellers');
    }
};

