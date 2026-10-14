<?php

namespace App\Filament\Resources\ArticleRevisions\Tables;

use App\Enums\ArticleStatus;
use App\Filament\Resources\ArticleRevisions\ArticleRevisionResource;
use App\Models\ArticleRevision;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ArticleRevisionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('article.title')->label('Article')->searchable()->limit(40)->wrap(),
                TextColumn::make('title')->label('Revision title')->limit(40)->wrap()->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (ArticleStatus $state): string => $state->getLabel())
                    ->color(fn (ArticleStatus $state): string => $state->getColor()),
                TextColumn::make('user.name')->label('Edited by')->placeholder('System')->toggleable(),
                TextColumn::make('note')->limit(40)->placeholder('—')->color('gray')->toggleable(),
                TextColumn::make('created_at')->label('Saved')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(ArticleStatus::options())->multiple(),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ArticleRevision $record): string => ArticleRevisionResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(false),
            ])
            ->emptyStateHeading('No revisions')
            ->emptyStateDescription('Revisions are captured automatically as articles change.')
            ->emptyStateIcon('heroicon-o-clock');
    }
}
