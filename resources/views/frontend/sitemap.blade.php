<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('blog.index') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>

    @foreach ($categories as $category)
        <url>
            <loc>{{ route('categories.show', $category->slug) }}</loc>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach

    @foreach ($tags as $tag)
        <url>
            <loc>{{ route('tags.show', $tag->slug) }}</loc>
            <changefreq>weekly</changefreq>
            <priority>0.5</priority>
        </url>
    @endforeach

    @foreach ($authors as $author)
        <url>
            <loc>{{ route('authors.show', $author->username ?: $author->getKey()) }}</loc>
            <changefreq>weekly</changefreq>
            <priority>0.5</priority>
        </url>
    @endforeach

    @foreach ($articles as $article)
        <url>
            <loc>{{ route('blog.show', $article->slug) }}</loc>
            @if ($article->updated_at)
                <lastmod>{{ $article->updated_at->toAtomString() }}</lastmod>
            @endif
            <changefreq>monthly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach
</urlset>
