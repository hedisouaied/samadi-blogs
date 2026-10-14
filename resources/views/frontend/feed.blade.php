<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ setting('general.site_name', config('app.name')) }}</title>
        <link>{{ route('home') }}</link>
        <description>{{ setting('general.site_description', '') }}</description>
        <language>{{ str_replace('_', '-', setting('general.default_language', 'en')) }}</language>
        <lastBuildDate>{{ \Illuminate\Support\Carbon::parse($updated)->toRfc2822String() }}</lastBuildDate>
        <atom:link href="{{ route('feed') }}" rel="self" type="application/rss+xml" />

        @foreach ($articles as $article)
            <item>
                <title>{{ $article->title }}</title>
                <link>{{ route('blog.show', $article->slug) }}</link>
                <guid isPermaLink="true">{{ route('blog.show', $article->slug) }}</guid>
                @if ($article->published_at)
                    <pubDate>{{ $article->published_at->toRfc2822String() }}</pubDate>
                @endif
                @if ($article->author)
                    <author>{{ $article->author->email }} ({{ $article->author->name }})</author>
                @endif
                @foreach ($article->categories as $category)
                    <category>{{ $category->name }}</category>
                @endforeach
                <description>{{ \Illuminate\Support\Str::limit(strip_tags($article->excerpt ?: (string) $article->content), 320) }}</description>
            </item>
        @endforeach
    </channel>
</rss>
