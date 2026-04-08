@extends('layouts.nexus')

@section('title', 'Fuwa.NG Blog | Insights on Identity Verification, Business Growth & Tech in Nigeria')
@section('meta_description', 'Explore the Fuwa.NG blog for the latest news, updates, and expert guides on identity verification (NIN, BVN), business growth strategies, and technology trends in Nigeria.')
@section('meta_keywords', 'Fuwa.NG blog, Nigeria business insights, KYC trends, identity verification updates, tech in Nigeria, fintech Nigeria')
@section('canonical', route('blog.index'))

@section('og_title', 'Fuwa.NG Blog | Insights on Identity Verification, Business Growth & Tech in Nigeria')
@section('og_description', 'Explore the Fuwa.NG blog for expert guides on identity verification (NIN, BVN), business growth strategies, and technology trends in Nigeria.')
@section('og_type', 'blog')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="text-white mb-1">Blog</h1>
        <p class="text-white-50 mb-0">News, updates, and helpful guides.</p>
    </div>
    <a href="{{ url('/') }}" class="btn btn-outline-secondary">
        <i class="fa-solid fa-house mr-2"></i> Home
    </a>
</div>

<div class="row">
    @forelse($posts as $post)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-0 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
                @if($post->featured_image)
                    <img src="{{ $post->featured_image }}" class="card-img-top" alt="{{ $post->title }}" style="max-height: 180px; object-fit: cover;">
                @endif
                <div class="card-body">
                    <h5 class="text-white">{{ $post->title }}</h5>
                    <p class="text-white-50 small mb-3">
                        {{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 140) }}
                    </p>
                    <a href="{{ route('blog.show', $post->slug) }}" class="btn btn-primary btn-sm">
                        Read More
                    </a>
                </div>
                <div class="card-footer border-0" style="background: transparent;">
                    <div class="text-white-50 small">
                        {{ optional($post->created_at)->format('M d, Y') }}
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center text-white-50 py-5">
                <i class="fa-regular fa-newspaper fa-3x mb-3 opacity-50"></i>
                <div>No posts yet.</div>
            </div>
        </div>
    @endforelse
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $posts->links() }}
</div>
@endsection

@push('scripts')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Blog",
  "name": "Fuwa.NG Blog",
  "url": "{{ route('blog.index') }}",
  "description": "The latest news, updates, and expert guides on identity verification (NIN, BVN), business growth strategies, and technology trends in Nigeria.",
  "blogPost": [
    @foreach($posts as $post)
    {
      "@type": "BlogPosting",
      "mainEntityOfPage": "{{ route('blog.show', $post->slug) }}",
      "headline": "{{ $post->title }}",
      "description": "{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 155) }}",
      "image": "{{ $post->featured_image ? url($post->featured_image) : \App\Models\SystemSetting::get('seo_default_image_url') }}",
      "author": {
        "@type": "Organization",
        "name": "Fuwa.NG"
      },
      "publisher": {
        "@type": "Organization",
        "name": "Fuwa.NG",
        "logo": {
          "@type": "ImageObject",
          "url": "{{ \App\Models\SystemSetting::get('site_logo_url') }}"
        }
      },
      "datePublished": "{{ optional($post->created_at)->toIso8601String() }}",
      "dateModified": "{{ optional($post->updated_at)->toIso8601String() }}"
    }{{ !$loop->last ? ',' : '' }}
    @endforeach
  ]
}
</script>
@endpush
