<?php

namespace App\Filament\Resources\Comments\Schemas;

use App\Enums\CommentStatus;
use App\Models\Comment;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Comment')
                    ->columns(2)
                    ->components([
                        TextEntry::make('content')->columnSpanFull()->prose(),
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (CommentStatus $state): string => $state->getLabel())
                            ->color(fn (CommentStatus $state): string => $state->getColor())
                            ->icon(fn (CommentStatus $state): string => $state->getIcon()),
                        IconEntry::make('is_pinned')->label('Pinned')->boolean(),
                        TextEntry::make('display_author_name')->label('From'),
                        TextEntry::make('display_author_email')->label('Email')->placeholder('—'),
                        TextEntry::make('article.title')->label('On article'),
                        TextEntry::make('parent.content')->label('Reply to')->limit(50)->placeholder('Root comment'),
                        TextEntry::make('depth')->label('Depth')->state(fn (Comment $record): int => $record->depth()),
                    ]),

                Section::make('Moderation')
                    ->columns(3)
                    ->components([
                        TextEntry::make('approved_at')->label('Approved at')->since()->placeholder('Not approved'),
                        TextEntry::make('approver.name')->label('Approved by')->placeholder('—'),
                        TextEntry::make('replies_count')
                            ->label('Replies')
                            ->state(fn (Comment $record): int => $record->replies()->count())
                            ->badge()
                            ->color('info'),
                    ]),

                Section::make('Technical')
                    ->columns(2)
                    ->components([
                        TextEntry::make('ip_address')->label('IP address')->placeholder('—'),
                        TextEntry::make('user_agent')->label('User agent')->limit(80)->placeholder('—'),
                        TextEntry::make('created_at')->label('Submitted')->dateTime('M j, Y H:i'),
                        TextEntry::make('updated_at')->label('Updated')->since(),
                    ]),
            ]);
    }
}
