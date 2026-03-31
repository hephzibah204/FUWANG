@extends('layouts.nexus')

@section('title', 'Website Pages')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-white">Website Pages</h1>
            <p class="text-muted">Create and manage static pages.</p>
        </div>
        <a href="{{ route('admin.pages.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-file-circle-plus mr-2"></i> New Page
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
                            <th class="border-0 p-3">Slug</th>
                            <th class="border-0 p-3">Status</th>
                            <th class="border-0 p-3">Updated</th>
                            <th class="border-0 p-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pages as $page)
                        <tr>
                            <td class="border-0 p-3 align-middle font-weight-bold">{{ $page->title }}</td>
                            <td class="border-0 p-3 align-middle">
                                <span class="text-muted">{{ $page->slug }}</span>
                            </td>
                            <td class="border-0 p-3 align-middle">
                                @if($page->status === 'published')
                                    <span class="badge badge-success">Published</span>
                                @else
                                    <span class="badge badge-secondary">Draft</span>
                                @endif
                            </td>
                            <td class="border-0 p-3 align-middle">
                                <div class="small">{{ optional($page->updated_at)->format('M d, Y H:i') }}</div>
                            </td>
                            <td class="border-0 p-3 align-middle text-right">
                                <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-outline-info mr-1" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                @if($page->status === 'published')
                                    <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="btn btn-sm btn-outline-success mr-1" title="View">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                @endif
                                <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this page?');">
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
                            <td colspan="5" class="text-center p-5 text-muted">
                                <i class="fa-regular fa-file-lines fa-3x mb-3 opacity-50"></i>
                                <p class="mb-0">No pages yet. Create your first page.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $pages->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
