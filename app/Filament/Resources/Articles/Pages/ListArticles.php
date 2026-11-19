<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Enums\ArticleStatus;
use App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('All')),
            'published' => Tab::make(__('Published'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ArticleStatus::Published->value))
                ->badge(fn (): int => ArticleResource::getModel()::query()->where('status', ArticleStatus::Published->value)->count()),
            'pending_review' => Tab::make(__('Pending review'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ArticleStatus::PendingReview->value)),
            'draft' => Tab::make(__('Drafts'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ArticleStatus::Draft->value)),
            'scheduled' => Tab::make(__('Scheduled'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ArticleStatus::Scheduled->value)),
            'archived' => Tab::make(__('Archived'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ArticleStatus::Archived->value)),
        ];
    }
}
