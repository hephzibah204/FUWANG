<?php

$providers = [
    App\Providers\AppServiceProvider::class,
];

if (in_array((string) env('APP_ENV'), ['local', 'testing'], true) && (bool) env('TELESCOPE_ENABLED', false)) {
    $providers[] = App\Providers\TelescopeServiceProvider::class;
}

return $providers;
