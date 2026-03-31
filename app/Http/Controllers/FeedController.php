<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Page;

class FeedController extends Controller
{
    public function sitemap()
    {
        $posts = Post::query()->where('status', 'published')->latest()->get();
        $pages = Page::query()->where('status', 'published')->latest()->get();
        $extraUrls = [
            ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => route('services.price_list'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => route('public.services.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('public.auctions.index'), 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => route('public.logistics.index'), 'priority' => '0.7', 'changefreq' => 'weekly'],
        ];
        return response()->view('seo.sitemap', compact('posts', 'pages', 'extraUrls'))->header('Content-Type', 'application/xml');
    }

    public function feed()
    {
        $posts = Post::query()->where('status', 'published')->latest()->take(50)->get();
        return response()->view('seo.feed', compact('posts'))->header('Content-Type', 'application/xml');
    }
}
