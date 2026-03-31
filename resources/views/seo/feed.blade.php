<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>{{ \App\Models\SystemSetting::get('site_name', config('app.name')) }} Blog</title>
        <link>{{ route('blog.index') }}</link>
        <description>{{ \App\Models\SystemSetting::get('seo_description', 'Latest updates') }}</description>
        @foreach($posts as $p)
        <item>
            <title>{{ $p->title }}</title>
            <link>{{ route('blog.show', $p->slug) }}</link>
            <guid>{{ route('blog.show', $p->slug) }}</guid>
            @if($p->created_at)
            <pubDate>{{ $p->created_at->toRfc2822String() }}</pubDate>
            @endif
            <description><![CDATA[{!! nl2br(e($p->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($p->content), 200))) !!}]]></description>
        </item>
        @endforeach
    </channel>
    </rss>
