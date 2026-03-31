@extends('layouts.nexus')

@section('title', 'Create Post')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-white">Create Post</h1>
            <p class="text-muted">Publish updates, announcements, and articles.</p>
        </div>
        <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="card border-0 shadow-sm" style="background: #1e293b;">
        <div class="card-body">
            <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label class="text-white-50 small">Title</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="text-white-50 small">Excerpt</label>
                    <textarea name="excerpt" class="form-control @error('excerpt') is-invalid @enderror" rows="3">{{ old('excerpt') }}</textarea>
                    @error('excerpt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="text-white-50 small">Content</label>
                    <textarea id="contentInput" name="content" class="form-control @error('content') is-invalid @enderror" rows="12" required style="display:none;">{{ old('content') }}</textarea>
                    <div class="d-flex flex-wrap mb-2" style="gap: 8px;">
                        <button class="btn btn-sm btn-outline-light" type="button" onclick="fmt('bold')"><i class="fa-solid fa-bold"></i></button>
                        <button class="btn btn-sm btn-outline-light" type="button" onclick="fmt('italic')"><i class="fa-solid fa-italic"></i></button>
                        <button class="btn btn-sm btn-outline-light" type="button" onclick="fmt('underline')"><i class="fa-solid fa-underline"></i></button>
                        <button class="btn btn-sm btn-outline-light" type="button" onclick="fmt('insertUnorderedList')"><i class="fa-solid fa-list-ul"></i></button>
                        <button class="btn btn-sm btn-outline-light" type="button" onclick="fmt('insertOrderedList')"><i class="fa-solid fa-list-ol"></i></button>
                        <button class="btn btn-sm btn-outline-light" type="button" onclick="setBlock('h2')">H2</button>
                        <button class="btn btn-sm btn-outline-light" type="button" onclick="setBlock('h3')">H3</button>
                        <button class="btn btn-sm btn-outline-light" type="button" onclick="setLink()"><i class="fa-solid fa-link"></i></button>
                        <button class="btn btn-sm btn-outline-light" type="button" onclick="fmt('removeFormat')"><i class="fa-solid fa-eraser"></i></button>
                    </div>
                    <div id="contentEditor" class="form-control @error('content') is-invalid @enderror" contenteditable="true" style="min-height: 260px; background: rgba(255,255,255,0.04); color: #fff; border: 1px solid rgba(255,255,255,0.1);"></div>
                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="text-white-50 small">Status</label>
                            <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="text-white-50 small">Featured Image</label>
                            <input type="file" name="featured_image" class="form-control @error('featured_image') is-invalid @enderror" accept="image/*">
                            @error('featured_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="mt-4 mb-2">
                    <h5 class="text-white mb-3">SEO</h5>
                    <div class="form-group">
                        <label class="text-white-50 small">SEO Title</label>
                        <input type="text" name="seo_title" class="form-control @error('seo_title') is-invalid @enderror" value="{{ old('seo_title') }}">
                        @error('seo_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="text-white-50 small">SEO Description</label>
                        <textarea name="seo_description" class="form-control @error('seo_description') is-invalid @enderror" rows="3">{{ old('seo_description') }}</textarea>
                        @error('seo_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="text-white-50 small">SEO Keywords</label>
                        <input type="text" name="seo_keywords" class="form-control @error('seo_keywords') is-invalid @enderror" value="{{ old('seo_keywords') }}" placeholder="keyword1, keyword2, keyword3">
                        @error('seo_keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Save Post
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const contentInput = document.getElementById('contentInput');
    const contentEditor = document.getElementById('contentEditor');
    if (contentInput && contentEditor) {
        contentEditor.innerHTML = contentInput.value || '';
        const form = contentInput.closest('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                const txt = (contentEditor.innerText || '').trim();
                if (!txt) {
                    e.preventDefault();
                    contentEditor.focus();
                    return;
                }
                contentInput.value = contentEditor.innerHTML;
            });
        }
    }
    function fmt(cmd) { document.execCommand(cmd, false, null); }
    function setBlock(tag) { document.execCommand('formatBlock', false, tag); }
    function setLink() {
        const url = prompt('Enter URL');
        if (!url) return;
        document.execCommand('createLink', false, url);
    }
</script>
@endpush
