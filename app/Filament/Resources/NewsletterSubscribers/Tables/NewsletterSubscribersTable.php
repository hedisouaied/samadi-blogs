<?php

namespace App\Filament\Resources\NewsletterSubscribers\Tables;

use App\Enums\Permission;
use App\Enums\SubscriberStatus;
use App\Models\NewsletterSubscriber;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class NewsletterSubscribersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')->searchable()->sortable()->copyable()->weight('semibold')
                    ->description(fn (NewsletterSubscriber $record): ?string => $record->name),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (SubscriberStatus $state): string => $state->getLabel())
                    ->color(fn (SubscriberStatus $state): string => $state->getColor())
                    ->icon(fn (SubscriberStatus $state): string => $state->getIcon()),
                TextColumn::make('source')->label('Source')->badge()->color('gray')->placeholder('—')->toggleable(),
                TextColumn::make('subscribed_at')->label('Subscribed')->since()->placeholder('—')->sortable(),
                TextColumn::make('last_email_sent_at')->label('Last email')->since()->placeholder('Never')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Added')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(SubscriberStatus::options())->multiple(),
                SelectFilter::make('source')
                    ->options(fn (): array => NewsletterSubscriber::query()
                        ->whereNotNull('source')
                        ->distinct()
                        ->orderBy('source')
                        ->pluck('source', 'source')
                        ->all()),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('subscribe')
                        ->label('Mark subscribed')
                        ->icon('heroicon-o-envelope')
                        ->color('success')
                        ->visible(fn (NewsletterSubscriber $record): bool => $record->status !== SubscriberStatus::Subscribed
                            && (Auth::user()?->hasPermission(Permission::UpdateSubscribers) ?? false))
                        ->action(function (NewsletterSubscriber $record): void {
                            $record->markSubscribed();
                            $record->save();
                            Notification::make()->title('Subscriber activated')->success()->send();
                        }),
                    Action::make('unsubscribe')
                        ->label('Unsubscribe')
                        ->icon('heroicon-o-envelope-open')
                        ->color('warning')
                        ->visible(fn (NewsletterSubscriber $record): bool => $record->status !== SubscriberStatus::Unsubscribed
                            && (Auth::user()?->hasPermission(Permission::UpdateSubscribers) ?? false))
                        ->requiresConfirmation()
                        ->action(function (NewsletterSubscriber $record): void {
                            $record->markUnsubscribed();
                            $record->save();
                            Notification::make()->title('Subscriber unsubscribed')->success()->send();
                        }),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('subscribe')
                        ->label('Mark subscribed')
                        ->icon('heroicon-o-envelope')
                        ->color('success')
                        ->visible(fn (): bool => Auth::user()?->hasPermission(Permission::UpdateSubscribers) ?? false)
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(function (NewsletterSubscriber $record): void {
                                $record->markSubscribed();
                                $record->save();
                            });
                            Notification::make()->title('Subscribers activated')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No subscribers yet')
            ->emptyStateDescription('Newsletter sign-ups from the public site appear here.')
            ->emptyStateIcon('heroicon-o-envelope');
    }
}
