<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category details')
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
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->suffixAction(
                                Action::make('regenerateSlug')
                                    ->icon('heroicon-o-arrow-path')
                                    ->tooltip('Regenerate from name')
                                    ->action(fn (Get $get, Set $set) => $set('slug', Str::slug((string) $get('name')))),
                            )
                            ->helperText('Used in the future public URL: /category/{slug}'),
                        Select::make('parent_id')
                            ->label('Parent category')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('No parent (top level)')
                            ->disableOptionWhen(fn ($value, ?Category $record): bool => $record !== null && (int) $value === (int) $record->getKey()),
                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),

                Section::make('Appearance & media')
                    ->columns(2)
                    ->components([
                        FileUpload::make('image_path')
                            ->label('Category image')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('categories')
                            ->visibility('public')
                            ->maxSize(4096)
                            ->columnSpanFull(),
                        ColorPicker::make('color')
                            ->label('Accent color')
                            ->default('#4f46e5'),
                        TextInput::make('icon')
                            ->label('Heroicon name')
                            ->placeholder('heroicon-o-folder')
                            ->helperText('Optional Heroicon identifier, e.g. heroicon-o-folder.'),
                    ]),

                Section::make('Visibility & ordering')
                    ->columns(3)
                    ->components([
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Toggle::make('is_active')->label('Active')->default(true)->inline(false),
                        Toggle::make('is_featured')->label('Featured')->default(false)->inline(false),
                        Toggle::make('show_in_menu')->label('Show in menu')->default(true)->inline(false),
                    ]),

                Section::make('SEO')
                    ->icon('heroicon-o-magnifying-glass')
                    ->columns(2)
                    ->components([
                        TextInput::make('seo_title')
                            ->maxLength(70)
                            ->helperText('Recommended maximum 70 characters.'),
                        TextInput::make('seo_description')
                            ->maxLength(160)
                            ->helperText('Recommended maximum 160 characters.'),
                    ]),
            ]);
    }
}
