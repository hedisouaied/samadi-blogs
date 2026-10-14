<?php

namespace App\Filament\Resources\Articles\RelationManagers;

use App\Enums\CommentStatus;
use App\Enums\Permission;
use App\Models\Comment;
use App\Services\CommentService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Panel;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    public static function isAvailable(Panel $panel): bool
    {
        return Auth::user()?->hasPermission(Permission::ViewComments) ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('author_name')->label('Guest name'),
                TextInput::make('author_email')->label('Guest email')->email(),
                Textarea::make('content')->required()->rows(3)->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('content')
                    ->limit(60)
                    ->wrap()
                    ->description(fn (Comment $record): string => $record->display_author_name.' · depth '.$record->depth()),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (CommentStatus $state): string => $state->getLabel())
                    ->color(fn (CommentStatus $state): string => $state->getColor()),
                IconColumn::make('is_pinned')->boolean()->label('Pinned'),
                TextColumn::make('created_at')->label('At')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(CommentStatus::options())
                    ->multiple(),
            ])
            ->recordActions([
                ActionGroup::make([
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
                        ->action(function (Comment $record): void {
                            app(CommentService::class)->deletePreservingReplies($record, Auth::user());
                            Notification::make()->title('Comment deleted')->success()->send();
                        }),
                ]),
            ])
            ->toolbarActions([])
            ->emptyStateHeading('No comments yet')
            ->emptyStateDescription('Comments on this article will appear here.');
    }
}
