<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
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
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CategoriesTable
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
                    ->height(36)
                    ->width(36)
                    ->grow(false),
                ColorColumn::make('color')->label('')->grow(false),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (Category $record): ?string => $record->parent?->name ? 'Parent: '.$record->parent->name : 'Top level'),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray'),
                TextColumn::make('articles_count')
                    ->counts('articles')
                    ->label('Articles')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                IconColumn::make('is_active')->boolean()->label('Active'),
                IconColumn::make('is_featured')->boolean()->label('Featured')->toggleable(),
                IconColumn::make('show_in_menu')->boolean()->label('In menu')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')->label('Order')->sortable()->badge()->color('gray')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Created')->dateTime('M j, Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
                TernaryFilter::make('is_featured')->label('Featured'),
                TernaryFilter::make('show_in_menu')->label('Show in menu'),
                SelectFilter::make('parent_id')
                    ->label('Parent category')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('toggleActive')
                        ->label(fn (Category $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                        ->icon('heroicon-o-power')
                        ->action(function (Category $record): void {
                            $record->update(['is_active' => ! $record->is_active]);
                            Notification::make()->title('Category updated')->success()->send();
                        }),
                    Action::make('toggleFeatured')
                        ->label(fn (Category $record): string => $record->is_featured ? 'Unfeature' : 'Feature')
                        ->icon('heroicon-o-star')
                        ->action(function (Category $record): void {
                            $record->update(['is_featured' => ! $record->is_featured]);
                            Notification::make()->title('Category updated')->success()->send();
                        }),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (Category $category) => $category->update(['is_active' => true])))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-pause-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (Category $category) => $category->update(['is_active' => false])))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No categories yet')
            ->emptyStateDescription('Create categories to organise your articles.')
            ->emptyStateIcon('heroicon-o-folder');
    }
}
