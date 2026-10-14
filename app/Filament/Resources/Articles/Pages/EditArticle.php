<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Enums\ArticleStatus;
use App\Enums\Permission;
use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use App\Services\ActivityLogger;
use App\Services\ArticleService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    /** @var array<string, mixed> */
    protected array $originalAttributes = [];

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function beforeSave(): void
    {
        $this->originalAttributes = $this->getRecord()->getOriginal();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = Auth::user();

        if (! $user?->hasPermission(Permission::UpdateAnyArticle)) {
            $data['author_id'] = $user?->getKey();
        }

        if (! $user?->hasPermission(Permission::PublishArticles)
            && in_array($data['status'] ?? null, [ArticleStatus::Published->value, ArticleStatus::Scheduled->value, ArticleStatus::Archived->value], true)) {
            $data['status'] = $this->getRecord()->status->value;
        }

        $data['reading_time_minutes'] = $this->calculateReadingTime($data['content'] ?? null);

        if (($data['status'] ?? null) === ArticleStatus::Published->value && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var Article $article */
        $article = $this->getRecord();

        app(ArticleService::class)->maybeCaptureRevision($article, $this->originalAttributes, Auth::user());
        app(ActivityLogger::class)->log('article.updated', $article, "Updated article \"{$article->title}\"");
    }

    protected function calculateReadingTime(?string $content): int
    {
        $article = new Article;
        $article->content = $content;

        return $article->calculateReadingTime();
    }
}
