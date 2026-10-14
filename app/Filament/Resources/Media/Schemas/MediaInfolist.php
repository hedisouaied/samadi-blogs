<?php

namespace App\Filament\Resources\Media\Schemas;

use App\Models\Media;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MediaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Media')
                    ->columns(3)
                    ->components([
                        ImageEntry::make('url')
                            ->label('Preview')
                            ->height(200)
                            ->visible(fn (Media $record): bool => $record->isImage()),
                        TextEntry::make('display_name')->weight('bold'),
                        TextEntry::make('original_filename')->label('Original filename')->placeholder('—'),
                        TextEntry::make('collection')->badge()->color('gray')->placeholder('—'),
                        TextEntry::make('url')->label('URL')->copyable()->columnSpanFull()->visible(fn (Media $record): bool => filled($record->url)),
                        TextEntry::make('alt_text')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('caption')->placeholder('—')->columnSpanFull(),
                    ]),
                Section::make('Technical metadata')
                    ->columns(3)
                    ->components([
                        TextEntry::make('mime_type')->label('MIME type')->badge()->color('info'),
                        TextEntry::make('human_size')->label('Size'),
                        TextEntry::make('dimensions')->label('Dimensions')->placeholder('—'),
                        TextEntry::make('uploader.name')->label('Uploaded by')->placeholder('—'),
                        TextEntry::make('created_at')->label('Uploaded')->dateTime('M j, Y H:i'),
                        TextEntry::make('updated_at')->label('Updated')->since(),
                    ]),
            ]);
    }
}
