<?php

namespace App\Filament\Resources\Media\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('File')
                    ->columns(2)
                    ->components([
                        FileUpload::make('path')
                            ->label('File')
                            ->disk('public')
                            ->directory('media')
                            ->visibility('public')
                            ->preserveFilenames()
                            ->storeFileNamesIn('original_filename')
                            ->maxSize(20480)
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('display_name')
                            ->label('Display name')
                            ->maxLength(180)
                            ->helperText('A friendly name for the media library.'),
                        TextInput::make('collection')
                            ->maxLength(80)
                            ->placeholder('e.g. articles, banners, avatars')
                            ->helperText('Group related assets together.'),
                        Textarea::make('alt_text')
                            ->label('Alt text')
                            ->rows(2)
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('caption')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),

                Section::make('Technical metadata')
                    ->columns(3)
                    ->visible(fn ($record): bool => $record?->exists ?? false)
                    ->components([
                        TextInput::make('mime_type')->label('MIME type')->disabled()->dehydrated(false),
                        TextInput::make('human_size')->label('Size')->disabled()->dehydrated(false),
                        TextInput::make('dimensions')->label('Dimensions')->disabled()->dehydrated(false),
                    ]),
            ]);
    }
}
