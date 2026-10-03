<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $heroVariant = (string) ($request->attributes->get('ab_variants')['home_hero'] ?? 'A');

        return view('marketing.home', [
            'ab' => [
                'home_hero' => $heroVariant,
            ],
        ]);
    }
}
