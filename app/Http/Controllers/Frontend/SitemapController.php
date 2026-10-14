<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\PublicContentService;

class SitemapController extends Controller
{
    public function __construct(
        protected PublicContentService $content,
    ) {}

    public function __invoke()
    {
        $articles = Article::query()
            ->published()
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at']);

        $xml = view('frontend.sitemap', [
            'articles' => $articles,
            'categories' => $this->content->categories(),
            'tags' => $this->content->tags(200),
            'authors' => $this->content->authors(100),
        ])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
