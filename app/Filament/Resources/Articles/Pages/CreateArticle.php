<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Enums\ArticleStatus;
use App\Enums\Permission;
use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use App\Services\ActivityLogger;
use App\Services\ArticleService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        // Authors cannot assign someone else as the author.
        if (! $user?->hasPermission(Permission::UpdateAnyArticle)) {
            $data['author_id'] = $user?->getKey();
        }

        // Authors without publish rights may only save drafts / submit for review.
        if (! $user?->hasPermission(Permission::PublishArticles)
            && in_array($data['status'] ?? null, [ArticleStatus::Published->value, ArticleStatus::Scheduled->value, ArticleStatus::Archived->value], true)) {
            $data['status'] = ArticleStatus::PendingReview->value;
        }

        $data['reading_time_minutes'] = $this->calculateReadingTime($data['content'] ?? null);

        if (($data['status'] ?? null) === ArticleStatus::Published->value && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Article $article */
        $article = $this->getRecord();

        app(ArticleService::class)->captureInitialRevision($article, Auth::user());
        app(ActivityLogger::class)->log('article.created', $article, "Created article \"{$article->title}\"");
    }

    protected function calculateReadingTime(?string $content): int
    {
        $article = new Article;
        $article->content = $content;

        return $article->calculateReadingTime();
    }
}
