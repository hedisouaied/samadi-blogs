<?php

namespace App\Filament\Resources\ArticleRevisions\Schemas;

use App\Enums\ArticleStatus;
use App\Filament\Resources\Articles\ArticleResource;
use App\Models\ArticleRevision;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArticleRevisionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Revision')
                    ->columns(3)
                    ->components([
                        TextEntry::make('id')->label('Revision')->badge()->color('gray'),
                        TextEntry::make('article.title')->label('Article')->url(fn (ArticleRevision $record): string => ArticleResource::getUrl('edit', ['record' => $record->article_id])),
                        TextEntry::make('user.name')->label('Edited by')->placeholder('System'),
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (ArticleStatus $state): string => $state->getLabel())
                            ->color(fn (ArticleStatus $state): string => $state->getColor()),
                        TextEntry::make('note')->placeholder('—'),
                        TextEntry::make('created_at')->label('Saved')->dateTime('M j, Y H:i'),
                    ]),
                Section::make('Snapshot')
                    ->columns(2)
                    ->components([
                        TextEntry::make('title')->weight('bold')->columnSpanFull(),
                        TextEntry::make('slug')->copyable(),
                        TextEntry::make('excerpt')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('content')->html()->prose()->columnSpanFull(),
                    ]),
            ]);
    }
}
