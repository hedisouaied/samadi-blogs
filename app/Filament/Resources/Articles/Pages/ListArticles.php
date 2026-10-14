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
            'all' => Tab::make('All'),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ArticleStatus::Published->value))
                ->badge(fn (): int => ArticleResource::getModel()::query()->where('status', ArticleStatus::Published->value)->count()),
            'pending_review' => Tab::make('Pending review')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ArticleStatus::PendingReview->value)),
            'draft' => Tab::make('Drafts')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ArticleStatus::Draft->value)),
            'scheduled' => Tab::make('Scheduled')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ArticleStatus::Scheduled->value)),
            'archived' => Tab::make('Archived')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ArticleStatus::Archived->value)),
        ];
    }
}
