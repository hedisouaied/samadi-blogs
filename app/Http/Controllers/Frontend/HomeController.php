<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\BannerPlacement;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\PublicContentService;
use App\Support\Seo;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    public function __construct(
        protected PublicContentService $content,
    ) {}

    public function __invoke()
    {
        $hero = $this->content->featuredOrLatest();

        $featured = $this->content->featuredArticles(5)
            ->reject(fn ($article) => $hero && $article->is($hero))
            ->take(2)
            ->values();

        $heroIds = collect([$hero?->getKey()])
            ->merge($featured->pluck('id'))
            ->filter()
            ->all();

        $latest = $this->content->latestArticles(10)
            ->reject(fn ($article) => in_array($article->getKey(), $heroIds, true))
            ->take(6)
            ->values();

        $trending = $this->content->trendingArticles(5)
            ->reject(fn ($article) => in_array($article->getKey(), $heroIds, true))
            ->values();

        $categories = $this->content->categories();

        return view('frontend.home', [
            'hero' => $hero,
            'featured' => $featured,
            'latest' => $latest,
            'trending' => $trending,
            'categories' => $categories,
            'authors' => $this->content->authors(4),
            'tags' => $this->content->tags(16),
            'middleBanner' => $this->content->banners(BannerPlacement::HomepageMiddle, 1)->first(),
            'seo' => Seo::make([
                'title' => (string) setting('general.site_name'),
                'description' => setting('general.site_description'),
                'image' => $hero?->featured_image_url,
                'type' => 'website',
                'schema' => [$this->websiteSchema(), $this->homeItemList($latest, $hero)],
            ]),
        ]);
    }

    /**
     * @param  Collection<int, Article>  $latest
     * @return array<string, mixed>
     */
    protected function homeItemList($latest, $hero): array
    {
        $items = collect($hero ? [$hero] : [])
            ->concat($latest)
            ->take(6)
            ->values();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => $items->map(fn ($article, $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'url' => route('blog.show', $article->slug),
                'name' => $article->title,
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function websiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => (string) setting('general.site_name', config('app.name')),
            'description' => (string) setting('general.site_description', ''),
            'url' => url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => route('search').'?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }
}
