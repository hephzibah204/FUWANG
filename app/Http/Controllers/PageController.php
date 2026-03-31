<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::query()
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        return view('pages.show', compact('page'));
    }
}
