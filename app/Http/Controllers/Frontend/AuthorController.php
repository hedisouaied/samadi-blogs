<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\PublicContentService;
use App\Support\Seo;

class AuthorController extends Controller
{
    public function __construct(
        protected PublicContentService $content,
    ) {}

    public function __invoke(string $key)
    {
        $author = $this->content->author($key);

        return view('frontend.author.show', [
            'author' => $author,
            'articles' => $this->content->paginatedForAuthor($author),
            'authors' => $this->content->authors(8),
            'categories' => $this->content->categories(),
            'seo' => Seo::make([
                'title' => $author->name,
                'description' => 'Articles and essays written by '.$author->name.'.',
                'canonical' => route('authors.show', $author->username ?: $author->getKey()),
                'image' => $author->getFilamentAvatarUrl(),
                'schema' => [
                    array_filter([
                        '@context' => 'https://schema.org',
                        '@type' => 'ProfilePage',
                        'name' => $author->name,
                        'url' => route('authors.show', $author->username ?: $author->getKey()),
                        'image' => $author->getFilamentAvatarUrl(),
                        'description' => $author->name.' writes for '.setting('general.site_name', config('app.name')),
                    ]),
                ],
            ]),
        ]);
    }
}
