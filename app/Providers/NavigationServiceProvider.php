<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\NavigationService;

class NavigationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(NavigationService::class, function ($app) {
            return new NavigationService();
        });
    }

    public function boot()
    {
        //
    }
}
