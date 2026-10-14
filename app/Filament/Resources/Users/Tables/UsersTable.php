<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
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
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('avatar_path')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->height(40)
                    ->width(40)
                    ->grow(false),
                TextColumn::make('name')
                    ->searchable(['name', 'username', 'email'])
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (User $record): string => $record->email)
                    ->wrap(),
                TextColumn::make('username')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
                TextColumn::make('job_title')
                    ->label('Job title')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
                TextColumn::make('role')
                    ->badge()
                    ->formatStateUsing(fn (UserRole $state): string => $state->getLabel())
                    ->color(fn (UserRole $state): string => $state->getColor()),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (UserStatus $state): string => $state->getLabel())
                    ->color(fn (UserStatus $state): string => $state->getColor()),
                TextColumn::make('articles_count')
                    ->counts('articles')
                    ->label('Articles')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('comments_count')
                    ->counts('comments')
                    ->label('Comments')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_login_at')
                    ->label('Last login')
                    ->since()
                    ->sortable()
                    ->placeholder('Never')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options(UserRole::options())
                    ->multiple(),
                SelectFilter::make('status')
                    ->options(UserStatus::options())
                    ->multiple(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (User $record): bool => $record->status !== UserStatus::Active)
                        ->requiresConfirmation()
                        ->action(function (User $record): void {
                            $record->update(['status' => UserStatus::Active]);
                            Notification::make()->title('User activated')->success()->send();
                        }),
                    Action::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-pause-circle')
                        ->color('warning')
                        ->visible(fn (User $record): bool => $record->status === UserStatus::Active)
                        ->requiresConfirmation()
                        ->action(function (User $record): void {
                            $record->update(['status' => UserStatus::Inactive]);
                            Notification::make()->title('User deactivated')->success()->send();
                        }),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (User $user) => $user->update(['status' => UserStatus::Active])))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('deactivate')
                        ->label('Deactivate selected')
                        ->icon('heroicon-o-pause-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (User $user) => $user->update(['status' => UserStatus::Inactive])))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No users yet')
            ->emptyStateDescription('Create the first administrator or author account.')
            ->emptyStateIcon('heroicon-o-users');
    }
}
