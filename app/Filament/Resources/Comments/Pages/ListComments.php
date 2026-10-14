<?php

namespace App\Filament\Resources\Comments\Pages;

use App\Enums\CommentStatus;
use App\Filament\Resources\Comments\CommentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListComments extends ListRecords
{
    protected static string $resource = CommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        $pendingCount = CommentResource::getModel()::query()
            ->where('status', CommentStatus::Pending->value)
            ->count();

        return [
            'pending' => Tab::make('Pending')
                ->badge(fn (): int => $pendingCount)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CommentStatus::Pending->value)),
            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CommentStatus::Approved->value)),
            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CommentStatus::Rejected->value)),
            'spam' => Tab::make('Spam')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CommentStatus::Spam->value)),
            'trash' => Tab::make('Trash')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CommentStatus::Trash->value)),
            'all' => Tab::make('All'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
