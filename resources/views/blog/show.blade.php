@extends('layouts.nexus')

@section('title', $post->seo_title ?: $post->title)
@section('meta_description', $post->seo_description ?: ($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 160)))
@section('meta_keywords', $post->seo_keywords ?: \App\Models\SystemSetting::get('seo_keywords', ''))
@section('canonical', route('blog.show', $post->slug))

@section('og_title', $post->seo_title ?: $post->title)
@section('og_description', $post->seo_description ?: ($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 160)))
@section('og_image', $post->featured_image ? url($post->featured_image) : \App\Models\SystemSetting::get('seo_default_image_url'))
@section('og_type', 'article')

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
            {!! $post->content !!}
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $site_name = \App\Models\SystemSetting::get('site_name', config('app.name'));
    $logo_url = \App\Models\SystemSetting::get('site_logo_url');
@endphp
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Article",
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "{{ route('blog.show', $post->slug) }}"
    },
    "headline": "{{ $post->title }}",
    "description": "{{ $post->seo_description ?: ($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 160)) }}",
    "image": "{{ $post->featured_image ? url($post->featured_image) : \App\Models\SystemSetting::get('seo_default_image_url') }}",
    "author": {
        "@type": "Organization",
        "name": "{{ $site_name }}",
        "url": "{{ url('/') }}"
    },
    "publisher": {
        "@type": "Organization",
        "name": "{{ $site_name }}",
        "logo": {
            "@type": "ImageObject",
            "url": "{{ $logo_url }}"
        }
    },
    "datePublished": "{{ optional($post->created_at)->toIso8601String() }}",
    "dateModified": "{{ optional($post->updated_at)->toIso8601String() }}",
    "articleBody": "{{ json_encode(strip_tags($post->content)) }}"
}
</script>
@endpush
