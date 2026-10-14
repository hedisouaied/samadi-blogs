<?php

namespace App\Filament\Resources\Comments\Schemas;

use App\Enums\CommentStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CommentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Comment')
                    ->columns(2)
                    ->components([
                        Textarea::make('content')
                            ->required()
                            ->rows(5)
                            ->maxLength(5000)
                            ->columnSpanFull(),
                        Select::make('status')
                            ->options(CommentStatus::options())
                            ->required()
                            ->native(false)
                            ->live()
                            ->helperText(function (Get $get): string {
                                if ($get('status') !== CommentStatus::Approved->value) {
                                    return '';
                                }

                                return 'Approving a reply requires its parent comment to be approved too.';
                            }),
                        Toggle::make('is_pinned')->label('Pinned')->default(false)->inline(false),
                        Select::make('article_id')
                            ->label('Article')
                            ->relationship('article', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),
                        Select::make('parent_id')
                            ->label('Reply to')
                            ->relationship('parent', 'content')
                            ->searchable()
                            ->preload()
                            ->placeholder('Root comment')
                            ->native(false),
                    ]),

                Section::make('Author')
                    ->columns(2)
                    ->components([
                        Select::make('user_id')
                            ->label('Registered user')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Guest comment'),
                        TextInput::make('author_name')->label('Guest name')->maxLength(120),
                        TextInput::make('author_email')->label('Guest email')->email()->maxLength(190),
                    ]),

                Section::make('Moderation meta')
                    ->columns(3)
                    ->components([
                        TextInput::make('ip_address')->label('IP address')->disabled()->dehydrated(false)->placeholder('—'),
                        TextInput::make('user_agent')->label('User agent')->disabled()->dehydrated(false)->columnSpan(2),
                    ]),
            ]);
    }
}
