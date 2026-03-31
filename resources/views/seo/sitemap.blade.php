<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($extraUrls as $extra)
    <url>
        <loc>{{ $extra['loc'] }}</loc>
        @if(isset($extra['lastmod']))
        <lastmod>{{ $extra['lastmod'] }}</lastmod>
        @endif
        @if(isset($extra['changefreq']))
        <changefreq>{{ $extra['changefreq'] }}</changefreq>
        @endif
        @if(isset($extra['priority']))
        <priority>{{ $extra['priority'] }}</priority>
        @endif
    </url>
    @endforeach
    @foreach($posts as $p)
    <url>
        <loc>{{ route('blog.show', $p->slug) }}</loc>
        @if($p->updated_at)
        <lastmod>{{ $p->updated_at->toAtomString() }}</lastmod>
        @endif
    </url>
    @endforeach
    @foreach($pages as $pg)
    <url>
        <loc>{{ route('pages.show', $pg->slug) }}</loc>
        @if($pg->updated_at)
        <lastmod>{{ $pg->updated_at->toAtomString() }}</lastmod>
        @endif
    </url>
    @endforeach
</urlset>
