<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Menu')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(120)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Set $set): void {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(140)
                            ->alphaDash()
                            ->unique(ignoreRecord: true)
                            ->helperText('Identifier used to render the menu on the public site.'),
                        Textarea::make('description')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Toggle::make('is_active')->label('Active')->default(true)->inline(false),
                    ]),
            ]);
    }
}
