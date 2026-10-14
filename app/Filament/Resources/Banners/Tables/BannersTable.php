<?php

namespace App\Filament\Resources\Banners\Tables;

use App\Enums\BannerPlacement;
use App\Enums\BannerStatus;
use App\Enums\Permission;
use App\Models\Banner;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('')
                    ->disk('public')
                    ->height(40)
                    ->width(72),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (Banner $record): ?string => $record->subtitle),
                TextColumn::make('placement')
                    ->badge()
                    ->formatStateUsing(fn (BannerPlacement $state): string => $state->getLabel())
                    ->color(fn (BannerPlacement $state): string => $state->getColor()),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (BannerStatus $state): string => $state->getLabel())
                    ->color(fn (BannerStatus $state): string => $state->getColor())
                    ->icon(fn (BannerStatus $state): string => $state->getIcon()),
                IconColumn::make('is_active')->boolean()->label('Active'),
                TextColumn::make('schedule')
                    ->label('Window')
                    ->state(fn (Banner $record): string => match (true) {
                        $record->starts_at && $record->ends_at => $record->starts_at->format('M j').' – '.$record->ends_at->format('M j, Y'),
                        $record->starts_at => 'From '.$record->starts_at->format('M j, Y'),
                        $record->ends_at => 'Until '.$record->ends_at->format('M j, Y'),
                        default => 'Always',
                    })
                    ->toggleable(),
                TextColumn::make('displayable')
                    ->label('Live?')
                    ->state(fn (Banner $record): string => $record->isDisplayable() ? 'Yes' : 'No')
                    ->badge()
                    ->color(fn (Banner $record): string => $record->isDisplayable() ? 'success' : 'gray'),
                TextColumn::make('sort_order')->label('Order')->badge()->color('gray')->sortable(),
            ])
            ->filters([
                SelectFilter::make('placement')->options(BannerPlacement::options())->multiple(),
                SelectFilter::make('status')->options(BannerStatus::options())->multiple(),
                TernaryFilter::make('is_active')->label('Active'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('publish')
                        ->label('Publish')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn (Banner $record): bool => $record->status !== BannerStatus::Published
                            && (Auth::user()?->hasPermission(Permission::UpdateBanners) ?? false))
                        ->action(function (Banner $record): void {
                            $record->update(['status' => BannerStatus::Published, 'is_active' => true]);
                            Notification::make()->title('Banner published')->success()->send();
                        }),
                    Action::make('archive')
                        ->label('Archive')
                        ->icon('heroicon-o-archive-box')
                        ->visible(fn (Banner $record): bool => $record->status !== BannerStatus::Archived
                            && (Auth::user()?->hasPermission(Permission::UpdateBanners) ?? false))
                        ->requiresConfirmation()
                        ->action(function (Banner $record): void {
                            $record->update(['status' => BannerStatus::Archived]);
                            Notification::make()->title('Banner archived')->success()->send();
                        }),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No banners yet')
            ->emptyStateDescription('Promotional banners appear across the public site.')
            ->emptyStateIcon('heroicon-o-photo');
    }
}
