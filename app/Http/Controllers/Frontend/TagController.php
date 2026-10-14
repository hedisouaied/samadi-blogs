<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\PublicContentService;
use App\Support\Seo;

class TagController extends Controller
{
    public function __construct(
        protected PublicContentService $content,
    ) {}

    public function __invoke(string $slug)
    {
        $tag = $this->content->tag($slug);

        return view('frontend.tag.show', [
            'tag' => $tag,
            'articles' => $this->content->paginatedForTag($tag),
            'categories' => $this->content->categories(),
            'tags' => $this->content->tags(18),
            'trending' => $this->content->trendingArticles(4),
            'seo' => Seo::make([
                'title' => '#'.$tag->name,
                'description' => $tag->description ?: 'Articles filed under the “'.$tag->name.'” topic.',
                'canonical' => route('tags.show', $tag->slug),
                'schema' => [
                    [
                        '@context' => 'https://schema.org',
                        '@type' => 'CollectionPage',
                        'name' => $tag->name,
                        'url' => route('tags.show', $tag->slug),
                    ],
                ],
            ]),
        ]);
    }
}
