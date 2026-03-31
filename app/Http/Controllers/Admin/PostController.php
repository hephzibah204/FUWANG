<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Services\HtmlSanitizer;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->paginate(20);
        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.posts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'status' => 'required|in:draft,published',
            'featured_image' => 'nullable|image|max:2048',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string|max:255',
        ]);

        $data = $request->except('featured_image');
        $sanitizer = app(HtmlSanitizer::class);
        $data['content'] = $sanitizer->sanitize((string) $request->input('content'));
        $data['excerpt'] = $request->filled('excerpt') ? $sanitizer->sanitize((string) $request->input('excerpt')) : null;
        $data['slug'] = Str::slug($request->title) . '-' . time();

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = Storage::url($request->file('featured_image')->store('posts', 'public'));
        }

        Post::create($data);

        return redirect()->route('admin.posts.index')->with('success', 'Post created successfully.');
    }

    public function edit(Post $post)
    {
        return view('admin.posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'status' => 'required|in:draft,published',
            'featured_image' => 'nullable|image|max:2048',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string|max:255',
        ]);

        $data = $request->except('featured_image');
        $sanitizer = app(HtmlSanitizer::class);
        $data['content'] = $sanitizer->sanitize((string) $request->input('content'));
        $data['excerpt'] = $request->filled('excerpt') ? $sanitizer->sanitize((string) $request->input('excerpt')) : null;
        
        // Only update slug if title changed significantly
        if (Str::slug($request->title) !== Str::slug($post->title)) {
            $data['slug'] = Str::slug($request->title) . '-' . time();
        }

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = Storage::url($request->file('featured_image')->store('posts', 'public'));
        }

        $post->update($data);

        return redirect()->route('admin.posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('admin.posts.index')->with('success', 'Post deleted successfully.');
    }
}
