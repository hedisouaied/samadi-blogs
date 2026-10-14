<?php

namespace App\Filament\Resources\Menus\Tables;

use App\Models\Menu;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MenusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('semibold')
                    ->description(fn (Menu $record): ?string => $record->description),
                TextColumn::make('slug')->searchable()->color('gray')->toggleable(),
                TextColumn::make('items_count')->counts('items')->label('Items')->badge()->color('primary'),
                IconColumn::make('is_active')->boolean()->label('Active'),
                TextColumn::make('updated_at')->label('Updated')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('toggleActive')
                        ->label(fn (Menu $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                        ->icon('heroicon-o-power')
                        ->action(function (Menu $record): void {
                            $record->update(['is_active' => ! $record->is_active]);
                            Notification::make()->title('Menu updated')->success()->send();
                        }),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No menus yet')
            ->emptyStateDescription('Build navigation menus for the public site.')
            ->emptyStateIcon('heroicon-o-bars-3');
    }
}
