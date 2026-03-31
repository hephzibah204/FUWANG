<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::query()
            ->where('status', 'published')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('blog.index', compact('posts'));
    }

    public function show(string $slug)
    {
        $post = Post::query()
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        return view('blog.show', compact('post'));
    }
}
