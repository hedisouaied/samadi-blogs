<?php

namespace App\Services;

use App\Enums\BannerPlacement;
use App\Enums\MenuItemType;
use App\Models\Article;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Centralises every public (front-end) content query.
 *
 * Only ever returns content that is eligible for public display: published
 * articles, active categories/tags and displayable banners. Draft, pending,
 * archived and future-scheduled records are never exposed.
 */
class PublicContentService
{
    public const PER_PAGE = 9;

    /* -----------------------------------------------------------------
     | Articles
     | ----------------------------------------------------------------- */

    /**
     * @return Collection<int, Article>
     */
    public function featuredArticles(int $limit = 4): Collection
    {
        return $this->articleQuery()
            ->featured()
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Article>
     */
    public function latestArticles(int $limit = 6): Collection
    {
        return $this->articleQuery()
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Trending articles by view count.
     *
     * @return Collection<int, Article>
     */
    public function trendingArticles(int $limit = 5): Collection
    {
        return $this->articleQuery()
            ->orderByDesc('view_count')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    public function featuredOrLatest(): ?Article
    {
        return $this->featuredArticles(1)->first() ?? $this->latestArticles(1)->first();
    }

    public function publishedArticle(string $slug): Article
    {
        return $this->articleQuery()
            ->with(['categories', 'tags', 'author'])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /**
     * @return Collection<int, Article>
     */
    public function relatedArticles(Article $article, int $limit = 3): Collection
    {
        $categoryIds = $article->categories->pluck('id');

        $related = collect();

        if ($categoryIds->isNotEmpty()) {
            $related = $this->articleQuery()
                ->whereKeyNot($article->getKey())
                ->whereHas('categories', fn ($query) => $query->whereIn('categories.id', $categoryIds))
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get();
        }

        if ($related->count() < $limit) {
            $excluded = $related->pluck('id')->push($article->getKey());

            $fallback = $this->articleQuery()
                ->whereKeyNot($excluded->all())
                ->orderByDesc('published_at')
                ->limit($limit - $related->count())
                ->get();

            $related = $related->concat($fallback);
        }

        return $related->values();
    }

    public function paginatedArticles(int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return $this->articleQuery()
            ->orderByDesc('published_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return LengthAwarePaginator<int, Article>
     */
    public function paginatedForCategory(Category $category, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return $this->articleQuery()
            ->whereHas('categories', fn ($query) => $query->whereKey($category->getKey()))
            ->orderByDesc('published_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return LengthAwarePaginator<int, Article>
     */
    public function paginatedForTag(Tag $tag, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return $this->articleQuery()
            ->whereHas('tags', fn ($query) => $query->whereKey($tag->getKey()))
            ->orderByDesc('published_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return LengthAwarePaginator<int, Article>
     */
    public function paginatedForAuthor(User $author, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return $this->articleQuery()
            ->where('author_id', $author->getKey())
            ->orderByDesc('published_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return LengthAwarePaginator<int, Article>
     */
    public function search(string $term, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        $term = trim($term);

        $query = $this->articleQuery()->orderByDesc('published_at');

        if ($term !== '') {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

            $query->where(function ($query) use ($like): void {
                $query->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like)
                    ->orWhere('content', 'like', $like)
                    ->orWhereHas('categories', fn ($q) => $q->where('name', 'like', $like))
                    ->orWhereHas('tags', fn ($q) => $q->where('name', 'like', $like));
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /* -----------------------------------------------------------------
     | Taxonomy & authors
     | ----------------------------------------------------------------- */

    /**
     * @return Collection<int, Category>
     */
    public function categories(bool $featuredOnly = false): Collection
    {
        return Category::query()
            ->active()
            ->ordered()
            ->when($featuredOnly, fn ($query) => $query->featured())
            ->withCount(['articles as published_articles_count' => fn ($query) => $query->published()])
            ->get();
    }

    public function category(string $slug): Category
    {
        return Category::query()->active()->where('slug', $slug)->firstOrFail();
    }

    /**
     * @return Collection<int, Tag>
     */
    public function tags(int $limit = 24): Collection
    {
        return Tag::query()
            ->active()
            ->withCount(['articles as published_articles_count' => fn ($query) => $query->published()])
            ->having('published_articles_count', '>', 0)
            ->orderByDesc('published_articles_count')
            ->limit($limit)
            ->get();
    }

    public function tag(string $slug): Tag
    {
        return Tag::query()->active()->where('slug', $slug)->firstOrFail();
    }

    /**
     * Active authors who have at least one published article.
     *
     * @return Collection<int, User>
     */
    public function authors(int $limit = 12): Collection
    {
        return User::query()
            ->active()
            ->whereHas('articles', fn ($query) => $query->published())
            ->withCount(['articles as published_articles_count' => fn ($query) => $query->published()])
            ->orderByDesc('published_articles_count')
            ->limit($limit)
            ->get();
    }

    public function author(string $key): User
    {
        return User::query()
            ->active()
            ->whereHas('articles', fn ($query) => $query->published())
            ->where(function ($query) use ($key): void {
                $query->where('username', $key)
                    ->orWhere('id', is_numeric($key) ? (int) $key : 0);
            })
            ->firstOrFail();
    }

    /* -----------------------------------------------------------------
     | Navigation, banners & misc
     | ----------------------------------------------------------------- */

    /**
     * @return Collection<int, MenuItem>
     */
    /**
     * Normalized navigation tree for the header.
     *
     * Falls back to featured categories when no menu is configured.
     *
     * @return array<int, array{label: string, url: ?string, new_tab: bool, children: array<int, array<string, mixed>>}>
     */
    public function navigation(string $menuSlug = 'primary', int $fallbackLimit = 6): array
    {
        $menu = Menu::query()
            ->where('slug', $menuSlug)
            ->where('is_active', true)
            ->with(['rootItems.children'])
            ->first();

        if ($menu && $menu->rootItems->isNotEmpty()) {
            return $menu->rootItems
                ->map(fn (MenuItem $item): array => $this->normalizeMenuItem($item))
                ->all();
        }

        return $this->categories(true)
            ->take($fallbackLimit)
            ->map(fn (Category $category): array => [
                'label' => $category->name,
                'url' => route('categories.show', $category->slug),
                'new_tab' => false,
                'children' => $category->children()
                    ->active()
                    ->ordered()
                    ->get()
                    ->map(fn (Category $child): array => [
                        'label' => $child->name,
                        'url' => route('categories.show', $child->slug),
                        'new_tab' => false,
                        'children' => [],
                    ])
                    ->all(),
            ])
            ->all();
    }

    /**
     * @return array{label: string, url: ?string, new_tab: bool, children: array<int, array<string, mixed>>}
     */
    protected function normalizeMenuItem(MenuItem $item): array
    {
        $url = match ($item->type) {
            MenuItemType::Category => $item->category
                ? route('categories.show', $item->category->slug)
                : null,
            MenuItemType::Article => $item->article
                ? route('blog.show', $item->article->slug)
                : null,
            default => $item->url,
        };

        return [
            'label' => (string) $item->label,
            'url' => $url,
            'new_tab' => (bool) $item->open_in_new_tab,
            'children' => $item->children
                ->where('is_active', true)
                ->map(fn (MenuItem $child): array => $this->normalizeMenuItem($child))
                ->values()
                ->all(),
        ];
    }

    public function menuItems(string $menuSlug): Collection
    {
        $menu = Menu::query()->where('slug', $menuSlug)->where('is_active', true)->first();

        if (! $menu) {
            return new Collection;
        }

        $menu->load([
            'rootItems' => fn ($query) => $query->where('is_active', true)->with('children'),
        ]);

        return $menu->rootItems->filter(fn ($item) => $item->is_active);
    }

    /**
     * @return Collection<int, Banner>
     */
    public function banners(BannerPlacement $placement, int $limit = 1): Collection
    {
        return Banner::query()
            ->displayable()
            ->placement($placement)
            ->ordered()
            ->limit($limit)
            ->get();
    }

    public function banner(BannerPlacement $placement): ?Banner
    {
        return $this->banners($placement, 1)->first();
    }

    public function incrementViewCount(Article $article): void
    {
        $key = 'viewed_article_'.$article->getKey();

        if (session()->has($key)) {
            return;
        }

        session()->put($key, true);
        Article::query()->whereKey($article->getKey())->increment('view_count');
    }

    public function previousArticle(Article $article): ?Article
    {
        if ($article->published_at === null) {
            return null;
        }

        return $this->articleQuery()
            ->whereKeyNot($article->getKey())
            ->where('published_at', '<', $article->published_at)
            ->orderByDesc('published_at')
            ->first();
    }

    public function nextArticle(Article $article): ?Article
    {
        if ($article->published_at === null) {
            return null;
        }

        return $this->articleQuery()
            ->whereKeyNot($article->getKey())
            ->where('published_at', '>', $article->published_at)
            ->orderBy('published_at')
            ->first();
    }

    /**
     * @return Collection<int, Comment>
     */
    public function approvedComments(Article $article): Collection
    {
        return $article->comments()
            ->approved()
            ->roots()
            ->with([
                'user',
                'replies' => fn ($query) => $query->approved()->with('user')->orderBy('created_at'),
            ])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();
    }

    public function commentCount(Article $article): int
    {
        return $article->comments()->approved()->count();
    }

    /* -----------------------------------------------------------------
     | Internal
     | ----------------------------------------------------------------- */

    /**
     * @return Builder<Article>
     */
    protected function articleQuery(): Builder
    {
        return Article::query()
            ->published()
            ->with(['author', 'categories', 'tags']);
    }
}
