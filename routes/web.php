<?php

use App\Http\Controllers\Frontend\AuthorController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\CategoryController;
use App\Http\Controllers\Frontend\CommentController;
use App\Http\Controllers\Frontend\FeedController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\NewsletterController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\Frontend\SitemapController;
use App\Http\Controllers\Frontend\TagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public front end
|--------------------------------------------------------------------------
|
| These routes power the reader-facing editorial experience. The Filament
| admin panel is registered separately by its panel provider at /admin.
|
*/

Route::get('/', HomeController::class)->name('home');

Route::get('/blog', BlogController::class.'@index')->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/categories/{slug}', CategoryController::class)->name('categories.show');
Route::get('/tags/{slug}', TagController::class)->name('tags.show');
Route::get('/authors/{key}', AuthorController::class)->name('authors.show');

Route::get('/search', SearchController::class)->name('search');

Route::post('/newsletter/subscribe', [\App\Http\Controllers\Frontend\NewsletterController::class, 'subscribe'])
    ->middleware('throttle:15,1')
    ->name('newsletter.subscribe');

Route::match(['get', 'post'], '/newsletter/unsubscribe', [\App\Http\Controllers\Frontend\NewsletterController::class, 'unsubscribe'])
    ->name('newsletter.unsubscribe');

Route::post('/blog/{slug}/comments', [CommentController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('comments.store');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/feed', FeedController::class)->name('feed');
Route::get('/robots.txt', function () {
    $sitemap = route('sitemap');

    return response("User-agent: *\nAllow: /\nDisallow: /admin\n\nSitemap: {$sitemap}\n", 200, [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
})->name('robots');
