<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class NavigationService
{
    public function getAdminNavigation()
    {
        $path = database_path('navigation.json');

        if (!File::exists($path)) {
            return [];
        }

        $json = File::get($path);

        return json_decode($json, true);
    }
}
