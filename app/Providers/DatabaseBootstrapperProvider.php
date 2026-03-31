<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class DatabaseBootstrapperProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        if (Cache::get('bootstrap:auction_lots_softdeletes') === true) {
            return;
        }

        if (!Schema::hasTable('auction_lots')) {
            Cache::put('bootstrap:auction_lots_softdeletes', true, 86400);
            return;
        }

        if (Schema::hasColumn('auction_lots', 'deleted_at')) {
            Cache::put('bootstrap:auction_lots_softdeletes', true, 86400);
            return;
        }

        Schema::table('auction_lots', function (Blueprint $table) {
            $table->softDeletes();
        });

        Cache::put('bootstrap:auction_lots_softdeletes', true, 86400);
    }
}
