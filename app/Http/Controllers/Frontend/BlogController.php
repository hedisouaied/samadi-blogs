<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\BannerPlacement;
use App\Http\Controllers\Controller;
use App\Services\PublicContentService;
use App\Support\ArticleContent;
use App\Support\Seo;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(
        protected PublicContentService $content,
    ) {}

    public function index(Request $request)
    {
        $articles = $this->content->paginatedArticles();

        return view('frontend.blog.index', [
            'articles' => $articles,
            'categories' => $this->content->categories(),
            'trending' => $this->content->trendingArticles(5),
            'tags' => $this->content->tags(18),
            'middleBanner' => $this->content->banners(BannerPlacement::Sidebar, 1)->first(),
            'seo' => Seo::make([
                'title' => 'Journal',
                'description' => 'Essays, field notes and long-form reporting from the '.setting('general.site_name', config('app.name')).' desk.',
                'canonical' => route('blog.index'),
                'schema' => [
                    $this->collectionSchema($articles->total()),
                ],
            ]),
        ]);
    }

    public function show(string $slug)
    {
        $article = $this->content->publishedArticle($slug);

        $this->content->incrementViewCount($article);

        $rendered = ArticleContent::render($article->content);
        $related = $this->content->relatedArticles($article, 3);

        $previous = $this->content->previousArticle($article);
        $next = $this->content->nextArticle($article);

        $commentsEnabled = $article->allow_comments
            && setting('content.comment_moderation', 'manual') !== 'disabled';

        return view('frontend.blog.show', [
            'article' => $article,
            'contentHtml' => $rendered['html'],
            'headings' => $rendered['headings'],
            'related' => $related,
            'previous' => $previous,
            'next' => $next,
            'comments' => $commentsEnabled ? $this->content->approvedComments($article) : collect(),
            'commentsEnabled' => $commentsEnabled,
            'commentCount' => $commentsEnabled ? $this->content->commentCount($article) : 0,
            'inlineBanner' => $this->content->banners(BannerPlacement::ArticleInline, 1)->first(),
            'seo' => Seo::make([
                'title' => $article->seo_title_display,
                'description' => $article->seo_description ?: $article->excerpt,
                'canonical' => $article->canonical_url ?: route('blog.show', $article->slug),
                'robots' => $article->seo_noindex ? 'noindex,follow' : null,
                'type' => 'article',
                'image' => $article->featured_image_url,
                'image_alt' => $article->featured_image_alt ?: $article->title,
                'published_at' => $article->published_at,
                'updated_at' => $article->updated_at,
                'author' => $article->author?->name,
                'section' => $article->categories->first()?->name,
                'tags' => $article->tags->pluck('name')->all(),
                'schema' => [
                    $this->articleSchema($article),
                    $this->breadcrumbSchema($article),
                ],
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function articleSchema($article): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $article->title,
            'description' => $article->seo_description ?: $article->excerpt,
            'image' => $article->featured_image_url ? [$article->featured_image_url] : null,
            'datePublished' => $article->published_at?->toIso8601String(),
            'dateModified' => $article->updated_at?->toIso8601String(),
            'author' => $article->author ? [
                '@type' => 'Person',
                'name' => $article->author->name,
                'url' => route('authors.show', $article->author->username ?: $article->author->getKey()),
            ] : null,
            'publisher' => [
                '@type' => 'Organization',
                'name' => setting('general.site_name', config('app.name')),
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('blog.show', $article->slug),
            ],
            'keywords' => $article->tags->pluck('name')->implode(', ') ?: null,
            'wordCount' => str_word_count(strip_tags((string) $article->content)) ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function breadcrumbSchema($article): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Journal', 'item' => route('blog.index')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $article->title, 'item' => route('blog.show', $article->slug)],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function collectionSchema(?int $count = null): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Journal',
            'url' => route('blog.index'),
            'description' => 'Essays, field notes and long-form reporting from the '.setting('general.site_name', config('app.name')).' desk.',
        ]);
    }
}
