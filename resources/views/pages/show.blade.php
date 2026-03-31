@extends('layouts.app')

@section('title', $page->seo_title ?: $page->title)
@section('meta_description', $page->seo_description ?: \Illuminate\Support\Str::limit(strip_tags($page->content), 160))
@section('meta_keywords', $page->seo_keywords ?: \App\Models\SystemSetting::get('seo_keywords', ''))

@section('content')
<div class="mb-4">
    <a href="{{ url('/') }}" class="btn btn-outline-secondary mb-3">
        <i class="fa-solid fa-arrow-left mr-2"></i> Home
    </a>
    <h1 class="text-white mb-2">{{ $page->title }}</h1>
</div>

<div class="card border-0" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
    <div class="card-body">
        <div class="text-white" style="line-height: 1.8;">
            {!! strip_tags($page->content, '<h1><h2><h3><h4><h5><h6><p><ul><li><ol><a><img><br><strong><em><blockquote><code><pre>') !!}
        </div>
    </div>
</div>
@endsection
