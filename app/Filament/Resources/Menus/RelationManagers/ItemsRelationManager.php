<?php

namespace App\Filament\Resources\Menus\RelationManagers;

use App\Enums\MenuItemType;
use App\Models\MenuItem;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Menu items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->required()
                    ->maxLength(120),
                Select::make('type')
                    ->options(MenuItemType::options())
                    ->default(MenuItemType::CustomUrl->value)
                    ->required()
                    ->native(false)
                    ->live(),
                TextInput::make('url')
                    ->label('URL')
                    ->url()
                    ->maxLength(255)
                    ->required(fn (Get $get): bool => $get('type') === MenuItemType::CustomUrl->value)
                    ->visible(fn (Get $get): bool => $get('type') === MenuItemType::CustomUrl->value),
                Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get): bool => $get('type') === MenuItemType::Category->value)
                    ->visible(fn (Get $get): bool => $get('type') === MenuItemType::Category->value),
                Select::make('article_id')
                    ->label('Article')
                    ->relationship('article', 'title')
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get): bool => $get('type') === MenuItemType::Article->value)
                    ->visible(fn (Get $get): bool => $get('type') === MenuItemType::Article->value),
                Select::make('parent_id')
                    ->label('Parent item')
                    ->relationship(
                        'parent',
                        'label',
                        modifyQueryUsing: fn (Builder $query) => $query->where('menu_id', $this->getOwnerRecord()->getKey()),
                    )
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->placeholder('Top level'),
                TextInput::make('sort_order')
                    ->label('Sort order')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Toggle::make('open_in_new_tab')->label('Open in new tab')->default(false)->inline(false),
                Toggle::make('is_active')->label('Active')->default(true)->inline(false),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('label')
                    ->weight('semibold')
                    ->description(fn (MenuItem $record): ?string => $record->parent?->label ? '↳ under '.$record->parent->label : 'Top level'),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (MenuItemType $state): string => $state->getLabel())
                    ->color(fn (MenuItemType $state): string => $state->getColor()),
                TextColumn::make('target')
                    ->label('Links to')
                    ->state(fn (MenuItem $record): string => $record->resolveUrl() ?: '—')
                    ->color('gray')
                    ->url(fn (MenuItem $record): ?string => $record->resolveUrl())
                    ->openUrlInNewTab(),
                TextColumn::make('sort_order')->label('Order')->badge()->color('gray'),
                IconColumn::make('is_active')->boolean()->label('Active'),
                IconColumn::make('open_in_new_tab')->boolean()->label('New tab')->toggleable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $this->assertNoCycle($data['parent_id'] ?? null, null);

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateFormDataUsing(function (array $data, MenuItem $record): array {
                        $this->assertNoCycle($data['parent_id'] ?? null, $record);

                        return $data;
                    }),
                Action::make('toggleActive')
                    ->label(fn (MenuItem $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon('heroicon-o-power')
                    ->action(function (MenuItem $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()->title('Item updated')->success()->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No menu items')
            ->emptyStateDescription('Add links to build this menu.')
            ->emptyStateIcon('heroicon-o-bars-3');
    }

    protected function assertNoCycle(?int $parentId, ?MenuItem $record): void
    {
        if ($parentId === null) {
            return;
        }

        if ($record && $record->getKey() && (int) $parentId === (int) $record->getKey()) {
            throw ValidationException::withMessages([
                'data.parent_id' => 'A menu item cannot be its own parent.',
            ]);
        }

        if ($record && $record->wouldCreateCycle((int) $parentId)) {
            throw ValidationException::withMessages([
                'data.parent_id' => 'A menu item cannot be nested inside one of its own children.',
            ]);
        }
    }
}
