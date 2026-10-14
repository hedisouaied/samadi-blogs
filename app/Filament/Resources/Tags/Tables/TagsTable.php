<?php

namespace App\Filament\Resources\Tags\Tables;

use App\Models\Tag;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                ColorColumn::make('color')->label(''),
                TextColumn::make('name')->searchable()->sortable()->weight('semibold')
                    ->description(fn (Tag $record): ?string => $record->description),
                TextColumn::make('slug')->searchable()->toggleable(isToggledHiddenByDefault: true)->color('gray'),
                TextColumn::make('articles_count')->counts('articles')->label('Articles')->badge()->color('primary')->sortable(),
                IconColumn::make('is_active')->boolean()->label('Active'),
                IconColumn::make('show_in_menu')->boolean()->label('In menu')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Created')->dateTime('M j, Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
                TernaryFilter::make('show_in_menu')->label('Show in menu'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('toggleActive')
                        ->label(fn (Tag $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                        ->icon('heroicon-o-power')
                        ->action(function (Tag $record): void {
                            $record->update(['is_active' => ! $record->is_active]);
                            Notification::make()->title('Tag updated')->success()->send();
                        }),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No tags yet')
            ->emptyStateDescription('Tags help readers discover related articles.')
            ->emptyStateIcon('heroicon-o-tag');
    }
}
