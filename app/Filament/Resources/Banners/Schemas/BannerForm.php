<?php

namespace App\Filament\Resources\Banners\Schemas;

use App\Enums\BannerPlacement;
use App\Enums\BannerStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Banner content')
                    ->columns(2)
                    ->components([
                        TextInput::make('title')
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
                            ->unique(ignoreRecord: true),
                        TextInput::make('subtitle')
                            ->maxLength(160)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        TextInput::make('button_text')
                            ->label('Call to action label')
                            ->maxLength(60)
                            ->helperText('Leave empty to render no button.'),
                        TextInput::make('target_url')
                            ->label('Button target URL')
                            ->url()
                            ->maxLength(255),
                        Toggle::make('open_in_new_tab')->label('Open in a new tab')->default(true)->inline(false),
                    ]),

                Section::make('Media')
                    ->columns(2)
                    ->components([
                        FileUpload::make('image_path')
                            ->label('Banner image (desktop)')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('banners')
                            ->visibility('public')
                            ->maxSize(4096),
                        FileUpload::make('mobile_image_path')
                            ->label('Banner image (mobile)')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('banners')
                            ->visibility('public')
                            ->maxSize(4096),
                        TextInput::make('alt_text')
                            ->label('Alt text')
                            ->maxLength(180)
                            ->columnSpanFull()
                            ->helperText('Describe the image for accessibility and SEO.'),
                    ]),

                Section::make('Placement & scheduling')
                    ->columns(3)
                    ->components([
                        Select::make('placement')
                            ->label('Placement')
                            ->options(BannerPlacement::options())
                            ->required()
                            ->native(false)
                            ->helperText('Where the banner is rendered on the public site.'),
                        Select::make('status')
                            ->options(BannerStatus::options())
                            ->default(BannerStatus::Draft->value)
                            ->required()
                            ->native(false),
                        TextInput::make('sort_order')
                            ->label('Sort order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        DateTimePicker::make('starts_at')
                            ->label('Starts at')
                            ->seconds(false)
                            ->native(false)
                            ->nullable(),
                        DateTimePicker::make('ends_at')
                            ->label('Ends at')
                            ->seconds(false)
                            ->native(false)
                            ->nullable()
                            ->afterOrEqual('starts_at'),
                        Toggle::make('is_active')->label('Active')->default(true)->inline(false),
                    ]),
            ]);
    }
}
