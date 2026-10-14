<?php

namespace App\Filament\Resources\Articles\RelationManagers;

use App\Models\ArticleRevision;
use App\Services\ArticleService;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Panel;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RevisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'revisions';

    public static function isAvailable(Panel $panel): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('Rev')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Edited by')
                    ->placeholder('System'),
                TextColumn::make('title')
                    ->limit(40)
                    ->wrap(),
                TextColumn::make('status')
                    ->badge()
                    ->label('Status'),
                TextColumn::make('note')
                    ->limit(40)
                    ->color('gray')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Saved')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->schema([
                        Section::make('Revision content')
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Title')
                                    ->state(fn (ArticleRevision $record): string => (string) $record->title)
                                    ->weight('bold'),
                                TextEntry::make('slug')
                                    ->label('Slug')
                                    ->state(fn (ArticleRevision $record): ?string => $record->slug)
                                    ->copyable(),
                                TextEntry::make('excerpt')
                                    ->label('Excerpt')
                                    ->state(fn (ArticleRevision $record): ?string => $record->excerpt)
                                    ->placeholder('—'),
                                TextEntry::make('content')
                                    ->label('Content')
                                    ->state(fn (ArticleRevision $record): ?string => $record->content)
                                    ->html()
                                    ->prose()
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Action::make('restore')
                    ->label('Restore this revision')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalDescription('Apply the title, slug, excerpt and content of this revision back to the live article? A new revision is recorded so nothing is lost.')
                    ->action(function (ArticleRevision $record): void {
                        $article = $record->article;

                        $article->title = $record->title;
                        $article->slug = $record->slug;
                        $article->excerpt = $record->excerpt;
                        $article->content = $record->content;
                        $article->syncReadingTime();
                        $article->save();

                        app(ArticleService::class)->captureRevision($article, Auth::user(), "Restored from revision #{$record->getKey()}");

                        Notification::make()
                            ->title('Revision restored')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([])
            ->headerActions([])
            ->emptyStateHeading('No revisions yet')
            ->emptyStateDescription('Every publish or content edit snapshots a revision.');
    }
}
