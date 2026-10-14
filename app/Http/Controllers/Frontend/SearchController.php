<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\PublicContentService;
use App\Support\Seo;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        protected PublicContentService $content,
    ) {}

    public function __invoke(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        $results = $term !== '' ? $this->content->search($term) : null;

        return view('frontend.search.index', [
            'term' => $term,
            'results' => $results,
            'categories' => $this->content->categories(),
            'tags' => $this->content->tags(14),
            'seo' => Seo::make([
                'title' => $term !== '' ? 'Search: '.$term : 'Search',
                'description' => 'Search the archive of '.setting('general.site_name', config('app.name')).'.',
                'canonical' => route('search'),
                'robots' => $term !== '' && ($results === null || $results->total() === 0) ? 'noindex,follow' : null,
            ]),
        ]);
    }
}
