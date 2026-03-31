@extends('layouts.nexus')

@section('title', 'Blog Posts')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-white">Blog Posts</h1>
            <p class="text-muted">Create and manage blog content for the public website.</p>
        </div>
        <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-pen-to-square mr-2"></i> New Post
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card border-0 shadow-sm" style="background: #1e293b;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-white mb-0">
                    <thead style="background: rgba(255,255,255,0.05);">
                        <tr>
                            <th class="border-0 p-3">Title</th>
                            <th class="border-0 p-3">Status</th>
                            <th class="border-0 p-3">Updated</th>
                            <th class="border-0 p-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $post)
                        <tr>
                            <td class="border-0 p-3 align-middle">
                                <div class="font-weight-bold">{{ $post->title }}</div>
                                <div class="small text-muted">{{ $post->slug }}</div>
                            </td>
                            <td class="border-0 p-3 align-middle">
                                @if($post->status === 'published')
                                    <span class="badge badge-success">Published</span>
                                @else
                                    <span class="badge badge-secondary">Draft</span>
                                @endif
                            </td>
                            <td class="border-0 p-3 align-middle">
                                <div class="small">{{ optional($post->updated_at)->format('M d, Y H:i') }}</div>
                            </td>
                            <td class="border-0 p-3 align-middle text-right">
                                <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-sm btn-outline-info mr-1" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this post?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center p-5 text-muted">
                                <i class="fa-regular fa-newspaper fa-3x mb-3 opacity-50"></i>
                                <p class="mb-0">No posts yet. Create your first post.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $posts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
