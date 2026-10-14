<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Enums\ArticleStatus;
use App\Enums\Permission;
use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use App\Services\ArticleService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('featured_image')
                    ->label('')
                    ->disk('public')
                    ->height(40)
                    ->width(64)
                    ->grow(false),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (Article $record): ?string => $record->excerpt)
                    ->lineClamp(2)
                    ->wrap(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (ArticleStatus $state): string => $state->getLabel())
                    ->color(fn (ArticleStatus $state): string => $state->getColor())
                    ->icon(fn (ArticleStatus $state): string => $state->getIcon()),
                TextColumn::make('author.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('categories.name')
                    ->label('Categories')
                    ->badge()
                    ->color('primary')
                    ->separator(',')
                    ->toggleable(),
                TextColumn::make('comments_count')
                    ->counts('comments')
                    ->label('Comments')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_featured')->boolean()->label('Featured')->toggleable(),
                IconColumn::make('is_active')->boolean()->label('Active')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('view_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ArticleStatus::options())
                    ->multiple(),
                SelectFilter::make('author_id')
                    ->label('Author')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('categories')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                SelectFilter::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_featured')->label('Featured'),
                TernaryFilter::make('is_active')->label('Active'),
                Filter::make('published_at')
                    ->schema([
                        DateTimePicker::make('published_from')->label('Published from')->seconds(false)->native(false),
                        DateTimePicker::make('published_until')->label('Published until')->seconds(false)->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['published_from'] ?? null, fn (Builder $q, $date): Builder => $q->where('published_at', '>=', $date))
                            ->when($data['published_until'] ?? null, fn (Builder $q, $date): Builder => $q->where('published_at', '<=', $date));
                    }),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('publish')
                        ->label('Publish')
                        ->icon(Heroicon::OutlinedCheckBadge)
                        ->color('success')
                        ->visible(fn (Article $record): bool => $record->status !== ArticleStatus::Published
                            && Auth::user()?->hasPermission(Permission::PublishArticles))
                        ->requiresConfirmation()
                        ->action(function (Article $record, ArticleService $service): void {
                            $service->publish($record, Auth::user());
                            Notification::make()->title('Article published')->success()->send();
                        }),
                    Action::make('schedule')
                        ->label('Schedule')
                        ->icon(Heroicon::OutlinedClock)
                        ->color('info')
                        ->visible(fn (): bool => Auth::user()?->hasPermission(Permission::PublishArticles) ?? false)
                        ->schema([
                            DateTimePicker::make('scheduled_at')
                                ->label('Publish at')
                                ->required()
                                ->seconds(false)
                                ->native(false)
                                ->default(now()->addDay()),
                        ])
                        ->action(function (Article $record, array $data, ArticleService $service): void {
                            $service->schedule($record, Carbon::parse($data['scheduled_at']), Auth::user());
                            Notification::make()->title('Article scheduled')->success()->send();
                        }),
                    Action::make('submitForReview')
                        ->label('Submit for review')
                        ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                        ->visible(fn (Article $record): bool => in_array($record->status, [ArticleStatus::Draft, ArticleStatus::PendingReview], true))
                        ->action(function (Article $record, ArticleService $service): void {
                            $service->submitForReview($record, Auth::user());
                            Notification::make()->title('Submitted for review')->success()->send();
                        }),
                    Action::make('unpublish')
                        ->label('Unpublish')
                        ->icon(Heroicon::OutlinedArrowUturnLeft)
                        ->color('warning')
                        ->visible(fn (Article $record): bool => in_array($record->status, [ArticleStatus::Published, ArticleStatus::Scheduled], true)
                            && (Auth::user()?->hasPermission(Permission::PublishArticles) ?? false))
                        ->requiresConfirmation()
                        ->action(function (Article $record, ArticleService $service): void {
                            $service->unpublish($record, Auth::user());
                            Notification::make()->title('Article moved to draft')->success()->send();
                        }),
                    Action::make('archive')
                        ->label('Archive')
                        ->icon(Heroicon::OutlinedArchiveBox)
                        ->color('danger')
                        ->visible(fn (Article $record): bool => $record->status !== ArticleStatus::Archived
                            && (Auth::user()?->hasPermission(Permission::PublishArticles) ?? false))
                        ->requiresConfirmation()
                        ->action(function (Article $record, ArticleService $service): void {
                            $service->archive($record, Auth::user());
                            Notification::make()->title('Article archived')->success()->send();
                        }),
                    Action::make('toggleFeatured')
                        ->label(fn (Article $record): string => $record->is_featured ? 'Unfeature' : 'Feature')
                        ->icon(Heroicon::OutlinedStar)
                        ->action(function (Article $record, ArticleService $service): void {
                            $service->toggleFeatured($record, Auth::user());
                            Notification::make()->title('Article updated')->success()->send();
                        }),
                    Action::make('toggleActive')
                        ->label(fn (Article $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                        ->icon(Heroicon::OutlinedPower)
                        ->action(function (Article $record, ArticleService $service): void {
                            $service->toggleActive($record, Auth::user());
                            Notification::make()->title('Article updated')->success()->send();
                        }),
                    Action::make('duplicate')
                        ->label('Duplicate')
                        ->icon(Heroicon::OutlinedDocumentDuplicate)
                        ->action(function (Article $record, ArticleService $service, $livewire): void {
                            $copy = $service->duplicate($record, Auth::user());
                            Notification::make()->title('Article duplicated')->success()->send();
                            $livewire->redirect(ArticleResource::getUrl('edit', ['record' => $copy]));
                        }),
                    DeleteAction::make()
                        ->visible(fn (): bool => Auth::user()?->hasPermission(Permission::DeleteArticles) ?? false),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label('Publish selected')
                        ->icon(Heroicon::OutlinedCheckBadge)
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (): bool => Auth::user()?->hasPermission(Permission::PublishArticles) ?? false)
                        ->action(fn (Collection $records, ArticleService $service) => $records->each(fn (Article $article) => $service->publish($article, Auth::user())))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('archive')
                        ->label('Archive selected')
                        ->icon(Heroicon::OutlinedArchiveBox)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (): bool => Auth::user()?->hasPermission(Permission::PublishArticles) ?? false)
                        ->action(fn (Collection $records, ArticleService $service) => $records->each(fn (Article $article) => $service->archive($article, Auth::user())))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => Auth::user()?->hasPermission(Permission::DeleteArticles) ?? false),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No articles yet')
            ->emptyStateDescription('Start writing your first article.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateActions([
                Action::make('create')
                    ->label('New article')
                    ->icon('heroicon-o-plus')
                    ->url(fn (): string => ArticleResource::getUrl('create')),
            ]);
    }
}
