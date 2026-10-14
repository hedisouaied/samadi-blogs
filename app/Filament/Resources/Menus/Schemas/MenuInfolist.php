<?php

namespace App\Filament\Resources\Menus\Schemas;

use App\Models\Menu;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MenuInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Menu')
                    ->columns(2)
                    ->components([
                        TextEntry::make('name')->weight('bold'),
                        TextEntry::make('slug')->copyable(),
                        TextEntry::make('description')->columnSpanFull()->placeholder('—'),
                        TextEntry::make('items_count')
                            ->label('Items')
                            ->state(fn (Menu $record): int => $record->items()->count())
                            ->badge()
                            ->color('primary'),
                        IconEntry::make('is_active')->label('Active')->boolean(),
                        TextEntry::make('created_at')->label('Created')->dateTime('M j, Y'),
                        TextEntry::make('updated_at')->label('Updated')->since(),
                    ]),
            ]);
    }
}
