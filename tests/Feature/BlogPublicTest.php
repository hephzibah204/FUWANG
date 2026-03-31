<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_index_lists_published_posts(): void
    {
        Post::create([
            'title' => 'Hello World',
            'slug' => 'hello-world',
            'content' => 'Sample content',
            'status' => 'published',
        ]);

        $resp = $this->get('/blog');
        $resp->assertStatus(200);
        $resp->assertSee('Hello World');
    }

    public function test_blog_show_displays_single_post(): void
    {
        Post::create([
            'title' => 'Hello Show',
            'slug' => 'hello-show',
            'content' => 'Post body',
            'status' => 'published',
        ]);

        $resp = $this->get('/blog/hello-show');
        $resp->assertStatus(200);
        $resp->assertSee('Hello Show');
        $resp->assertSee('Post body');
    }
}
