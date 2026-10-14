<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Enums\ArticleStatus;
use App\Models\Article;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class ArticleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Article')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Overview')
                            ->schema([
                                Section::make()
                                    ->columns(3)
                                    ->schema([
                                        ImageEntry::make('featured_image')
                                            ->label('Featured image')
                                            ->disk('public')
                                            ->height(180)
                                            ->columnSpan(2),
                                        TextEntry::make('title')->size('lg')->weight('bold')->columnSpan(3),
                                        TextEntry::make('status')
                                            ->badge()
                                            ->formatStateUsing(fn (ArticleStatus $state): string => $state->getLabel())
                                            ->color(fn (ArticleStatus $state): string => $state->getColor())
                                            ->icon(fn (ArticleStatus $state): string => $state->getIcon()),
                                        TextEntry::make('author.name')->label('Author'),
                                        TextEntry::make('published_at')->label('Published')->dateTime('M j, Y H:i')->placeholder('Not published'),
                                        TextEntry::make('slug')->label('Slug')->copyable()->prefix('/blog/'),
                                        TextEntry::make('reading_time_minutes')->label('Reading time')->suffix(' min')->badge()->color('gray'),
                                        TextEntry::make('view_count')->label('Views')->numeric()->badge()->color('info'),
                                        TextEntry::make('excerpt')->columnSpanFull()->placeholder('No excerpt.'),
                                    ]),
                                Section::make('Classification')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('categories.name')->label('Categories')->badge()->color('primary')->placeholder('Uncategorised'),
                                        TextEntry::make('tags.name')->label('Tags')->badge()->color('info')->placeholder('No tags'),
                                    ]),
                            ]),
                        Tabs\Tab::make('Content')
                            ->schema([
                                TextEntry::make('content')
                                    ->html()
                                    ->prose()
                                    ->columnSpanFull(),
                            ]),
                        Tabs\Tab::make('SEO')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('seo_title_display')->label('SEO title'),
                                        TextEntry::make('seo_description')->placeholder('—'),
                                        TextEntry::make('canonical_url')->placeholder('—')->copyable(),
                                        IconEntry::make('seo_noindex')->label('No-index')->boolean(),
                                    ]),
                            ]),
                        Tabs\Tab::make('Audit')
                            ->schema([
                                Section::make()
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('created_at')->label('Created')->dateTime('M j, Y H:i'),
                                        TextEntry::make('updated_at')->label('Updated')->since(),
                                        TextEntry::make('deleted_at')->label('Deleted')->since()->placeholder('—'),
                                        IconEntry::make('is_featured')->label('Featured')->boolean(),
                                        IconEntry::make('is_active')->label('Active')->boolean(),
                                        IconEntry::make('allow_comments')->label('Comments enabled')->boolean(),
                                        TextEntry::make('comments_count')
                                            ->label('Comments')
                                            ->state(fn (Article $record): int => $record->comments()->count())
                                            ->badge(),
                                        TextEntry::make('revisions_count')
                                            ->label('Revisions')
                                            ->state(fn (Article $record): int => $record->revisions()->count())
                                            ->badge(),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
