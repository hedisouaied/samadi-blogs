<?php

namespace App\Filament\Resources\ArticleRevisions\Pages;

use App\Enums\Permission;
use App\Filament\Resources\ArticleRevisions\ArticleRevisionResource;
use App\Filament\Resources\Articles\ArticleResource;
use App\Services\ArticleService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewArticleRevision extends ViewRecord
{
    protected static string $resource = ArticleRevisionResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        if (Auth::user()?->hasPermission(Permission::ViewArticles)) {
            $actions[] = Action::make('viewArticle')
                ->label('Open article')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (): string => ArticleResource::getUrl('edit', ['record' => $this->getRecord()->article_id]))
                ->openUrlInNewTab();
        }

        if (Auth::user()?->hasPermission(Permission::UpdateAnyArticle)) {
            $actions[] = Action::make('restore')
                ->label('Restore this revision')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('Apply this snapshot back to the live article? A fresh revision is recorded first.')
                ->action(function (): void {
                    $record = $this->getRecord();
                    $article = $record->article;

                    $article->title = $record->title;
                    $article->slug = $record->slug;
                    $article->excerpt = $record->excerpt;
                    $article->content = $record->content;
                    $article->syncReadingTime();
                    $article->save();

                    app(ArticleService::class)->captureRevision($article, Auth::user(), "Restored from revision #{$record->getKey()}");

                    Notification::make()->title('Revision restored')->success()->send();

                    $this->redirect(ArticleResource::getUrl('edit', ['record' => $article]));
                });
        }

        return $actions;
    }
}
