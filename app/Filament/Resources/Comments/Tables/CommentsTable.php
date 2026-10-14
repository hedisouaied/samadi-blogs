<?php

namespace App\Filament\Resources\Comments\Tables;

use App\Enums\CommentStatus;
use App\Enums\Permission;
use App\Models\Comment;
use App\Services\CommentService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('content')
                    ->limit(70)
                    ->wrap()
                    ->description(fn (Comment $record): string => $record->display_author_name
                        .($record->user ? ' · member' : ' · guest')
                        .' · on '.$record->article?->title),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (CommentStatus $state): string => $state->getLabel())
                    ->color(fn (CommentStatus $state): string => $state->getColor())
                    ->icon(fn (CommentStatus $state): string => $state->getIcon()),
                TextColumn::make('article.title')
                    ->label('Article')
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('depth')
                    ->label('Depth')
                    ->state(fn (Comment $record): int => $record->depth())
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_pinned')->boolean()->label('Pinned'),
                TextColumn::make('approved_at')
                    ->label('Approved')
                    ->since()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(CommentStatus::options())
                    ->multiple()
                    ->default([CommentStatus::Pending->value]),
                SelectFilter::make('article_id')
                    ->label('Article')
                    ->relationship('article', 'title')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('user_id')
                    ->label('Author')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Guests only'),
                TernaryFilter::make('is_pinned')->label('Pinned'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Comment $record): bool => $record->status !== CommentStatus::Approved
                            && (Auth::user()?->hasPermission(Permission::ModerateComments) ?? false))
                        ->action(function (Comment $record): void {
                            $ok = app(CommentService::class)->approve($record, Auth::user());

                            if ($ok) {
                                Notification::make()->title('Comment approved')->success()->send();
                            } else {
                                Notification::make()
                                    ->title('Cannot approve yet')
                                    ->body('Approve the parent comment first.')
                                    ->warning()
                                    ->send();
                            }
                        }),
                    Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (): bool => Auth::user()?->hasPermission(Permission::ModerateComments) ?? false)
                        ->action(function (Comment $record): void {
                            app(CommentService::class)->reject($record, Auth::user());
                            Notification::make()->title('Comment rejected')->success()->send();
                        }),
                    Action::make('spam')
                        ->label('Mark as spam')
                        ->icon('heroicon-o-hand-raised')
                        ->visible(fn (): bool => Auth::user()?->hasPermission(Permission::ModerateComments) ?? false)
                        ->action(function (Comment $record): void {
                            app(CommentService::class)->markAsSpam($record, Auth::user());
                            Notification::make()->title('Marked as spam')->success()->send();
                        }),
                    Action::make('trash')
                        ->label('Move to trash')
                        ->icon('heroicon-o-trash')
                        ->visible(fn (): bool => Auth::user()?->hasPermission(Permission::ModerateComments) ?? false)
                        ->action(function (Comment $record): void {
                            app(CommentService::class)->moveToTrash($record, Auth::user());
                            Notification::make()->title('Moved to trash')->success()->send();
                        }),
                    Action::make('pin')
                        ->label(fn (Comment $record): string => $record->is_pinned ? 'Unpin' : 'Pin')
                        ->icon(fn (Comment $record): string => $record->is_pinned ? 'heroicon-o-bookmark-slash' : 'heroicon-o-bookmark')
                        ->visible(fn (): bool => Auth::user()?->hasPermission(Permission::ModerateComments) ?? false)
                        ->action(function (Comment $record): void {
                            app(CommentService::class)->togglePinned($record, Auth::user());
                            Notification::make()->title('Comment updated')->success()->send();
                        }),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Replies are preserved and re-attached to this comment\'s parent.')
                        ->visible(fn (): bool => Auth::user()?->hasPermission(Permission::DeleteComments) ?? false)
                        ->action(function (Comment $record): void {
                            app(CommentService::class)->deletePreservingReplies($record, Auth::user());
                            Notification::make()->title('Comment deleted')->success()->send();
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')
                        ->label('Approve selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (): bool => Auth::user()?->hasPermission(Permission::ModerateComments) ?? false)
                        ->action(function (Collection $records): void {
                            $service = app(CommentService::class);
                            $approved = 0;
                            $blocked = 0;

                            foreach ($records as $comment) {
                                $service->approve($comment, Auth::user()) ? $approved++ : $blocked++;
                            }

                            Notification::make()
                                ->title("Approved {$approved} comment(s)".($blocked ? ", {$blocked} skipped (parent not approved)" : ''))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('spam')
                        ->label('Mark spam')
                        ->icon('heroicon-o-hand-raised')
                        ->visible(fn (): bool => Auth::user()?->hasPermission(Permission::ModerateComments) ?? false)
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $service = app(CommentService::class);
                            $records->each(fn (Comment $comment) => $service->markAsSpam($comment, Auth::user()));
                            Notification::make()->title('Marked as spam')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => Auth::user()?->hasPermission(Permission::DeleteComments) ?? false),
                ]),
            ])
            ->emptyStateHeading('No comments')
            ->emptyStateDescription('Moderate reader comments from the public site here.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }
}
