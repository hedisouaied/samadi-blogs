<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\PublicContentService;
use App\Support\Seo;

class CategoryController extends Controller
{
    public function __construct(
        protected PublicContentService $content,
    ) {}

    public function __invoke(string $slug)
    {
        $category = $this->content->category($slug);
        $articles = $this->content->paginatedForCategory($category);

        return view('frontend.category.show', [
            'category' => $category,
            'articles' => $articles,
            'children' => $category->children()->active()->ordered()->get(),
            'categories' => $this->content->categories(),
            'trending' => $this->content->trendingArticles(4),
            'tags' => $this->content->tags(14),
            'seo' => Seo::make([
                'title' => $category->seo_title ?: $category->name,
                'description' => $category->seo_description ?: $category->description,
                'canonical' => route('categories.show', $category->slug),
                'image' => $category->image_url,
                'schema' => [
                    [
                        '@context' => 'https://schema.org',
                        '@type' => 'CollectionPage',
                        'name' => $category->name,
                        'description' => $category->description,
                        'url' => route('categories.show', $category->slug),
                    ],
                    $this->breadcrumbs($category),
                ],
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function breadcrumbs($category): array
    {
        $items = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Journal', 'item' => route('blog.index')],
        ];

        $position = 2;

        if ($category->parent) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $category->parent->name,
                'item' => route('categories.show', $category->parent->slug),
            ];
        }

        $items[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $category->name,
            'item' => route('categories.show', $category->slug),
        ];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }
}
