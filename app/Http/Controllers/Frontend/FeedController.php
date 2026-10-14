<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\PublicContentService;

class FeedController extends Controller
{
    public function __construct(
        protected PublicContentService $content,
    ) {}

    public function __invoke()
    {
        $articles = $this->content->latestArticles(20);

        $rss = view('frontend.feed', [
            'articles' => $articles,
            'updated' => $articles->max('updated_at') ?? now(),
        ])->render();

        return response($rss, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }
}
