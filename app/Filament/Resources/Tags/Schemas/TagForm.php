<?php

namespace App\Filament\Resources\Tags\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tag details')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(80)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Set $set): void {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(100)
                            ->alphaDash()
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        ColorPicker::make('color')->label('Accent color')->default('#0ea5e9'),
                        Toggle::make('is_active')->label('Active')->default(true)->inline(false),
                        Toggle::make('show_in_menu')->label('Show in menu')->default(false)->inline(false),
                    ]),
            ]);
    }
}
