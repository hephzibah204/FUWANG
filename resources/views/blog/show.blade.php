@extends('layouts.app')

@section('title', $post->seo_title ?: $post->title)
@section('meta_description', $post->seo_description ?: ($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 160)))
@section('meta_keywords', $post->seo_keywords ?: \App\Models\SystemSetting::get('seo_keywords', ''))

@section('content')
<div class="mb-4">
    <a href="{{ route('blog.index') }}" class="btn btn-outline-secondary mb-3">
        <i class="fa-solid fa-arrow-left mr-2"></i> Back to Blog
    </a>
    <h1 class="text-white mb-2">{{ $post->title }}</h1>
    <div class="text-white-50 small">
        {{ optional($post->created_at)->format('M d, Y') }}
    </div>
</div>

@if($post->featured_image)
    <div class="mb-4">
        <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="img-fluid rounded" style="max-height: 420px; width: 100%; object-fit: cover;">
    </div>
@endif

<div class="card border-0" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
    <div class="card-body">
        <div class="text-white" style="line-height: 1.8;">
            {!! strip_tags($post->content, '<h1><h2><h3><h4><h5><h6><p><ul><li><ol><a><img><br><strong><em><blockquote><code><pre>') !!}
        </div>
    </div>
</div>
@endsection
