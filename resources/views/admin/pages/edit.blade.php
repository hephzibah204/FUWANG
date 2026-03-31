@extends('layouts.nexus')

@section('title', 'Edit Page')

@push('styles')
<link href="https://unpkg.com/grapesjs/dist/css/grapes.min.css" rel="stylesheet">
<style>
    #gjs {
        border: 3px solid #444;
    }
    .gjs-cv-canvas {
        top: 0;
        width: 100%;
        height: 100%;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-white">Edit Page</h1>
            <p class="text-muted">{{ $page->title }}</p>
        </div>
        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="card border-0 shadow-sm" style="background: #1e293b;">
        <div class="card-body">
            <form action="{{ route('admin.pages.update', $page) }}" method="POST" id="pageForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="text-white-50 small">Title</label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $page->title) }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="text-white-50 small">Slug</label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $page->slug) }}" required>
                            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="text-white-50 small d-block mb-2">Page Content (Visual Editor)</label>
                    <textarea id="contentInput" name="content" style="display:none;">{{ old('content', $page->content) }}</textarea>
                    <div id="gjs" style="height:600px; overflow:hidden;"></div>
                    @error('content')<div class="invalid-feedback d-block mt-2">{{ $message }}</div>@enderror
                </div>

                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="text-white-50 small">Status</label>
                            <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="draft" {{ old('status', $page->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $page->status) === 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-8 d-flex align-items-end justify-content-end">
                        @if($page->status === 'published')
                            <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="btn btn-outline-success mb-3">
                                <i class="fa-solid fa-arrow-up-right-from-square mr-2"></i> View Live Page
                            </a>
                        @endif
                    </div>
                </div>

                <div class="mt-4 mb-2 p-3 rounded" style="background: rgba(0,0,0,0.2);">
                    <h5 class="text-white mb-3 text-sm">SEO Settings</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-white-50 small">SEO Title</label>
                                <input type="text" name="seo_title" class="form-control @error('seo_title') is-invalid @enderror" value="{{ old('seo_title', $page->seo_title) }}">
                                @error('seo_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label class="text-white-50 small">SEO Keywords</label>
                                <input type="text" name="seo_keywords" class="form-control @error('seo_keywords') is-invalid @enderror" value="{{ old('seo_keywords', $page->seo_keywords) }}" placeholder="keyword1, keyword2, keyword3">
                                @error('seo_keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group h-100">
                                <label class="text-white-50 small">SEO Description</label>
                                <textarea name="seo_description" class="form-control @error('seo_description') is-invalid @enderror" rows="5">{{ old('seo_description', $page->seo_description) }}</textarea>
                                @error('seo_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary px-4 py-2 font-weight-bold">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Update Page
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/grapesjs"></script>
<script src="https://unpkg.com/grapesjs-preset-webpage"></script>
<script src="https://unpkg.com/grapesjs-blocks-basic"></script>
<script>
    const editor = grapesjs.init({
        container: '#gjs',
        fromElement: true,
        height: '600px',
        width: 'auto',
        storageManager: false,
        plugins: ['gjs-preset-webpage', 'gjs-blocks-basic'],
        pluginsOpts: {
            'gjs-preset-webpage': {},
            'gjs-blocks-basic': {}
        },
        assetManager: {
            upload: '{{ route('admin.media.upload') }}',
            uploadName: 'files',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        },
        canvas: {
            styles: [
                'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
            ]
        }
    });

    // Load initial content
    const initialContent = document.getElementById('contentInput').value;
    if (initialContent) {
        editor.setComponents(initialContent);
    }

    // Sync content on form submit
    document.getElementById('pageForm').addEventListener('submit', function(e) {
        // Get HTML and CSS from GrapeJS and combine them
        const html = editor.getHtml();
        const css = editor.getCss();
        
        // Wrap in a container to isolate styles if needed, or just append inline style block
        const fullContent = `<style>${css}</style>\n<div class="gjs-content-wrapper">${html}</div>`;
        document.getElementById('contentInput').value = fullContent;
    });
</script>
@endpush
