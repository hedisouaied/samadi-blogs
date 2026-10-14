<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category')
                    ->columns(3)
                    ->components([
                        ImageEntry::make('image_path')
                            ->label('Image')
                            ->disk('public')
                            ->height(140),
                        TextEntry::make('name')->weight('bold')->size('lg'),
                        TextEntry::make('slug')->copyable()->prefix('/category/'),
                        TextEntry::make('parent.name')->label('Parent category')->placeholder('Top level'),
                        TextEntry::make('articles_count')
                            ->label('Articles')
                            ->state(fn (Category $record): int => $record->articles()->count())
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('children_count')
                            ->label('Subcategories')
                            ->state(fn (Category $record): int => $record->children()->count())
                            ->badge()
                            ->color('info'),
                        TextEntry::make('sort_order')->label('Sort order')->badge()->color('gray'),
                        TextEntry::make('description')->columnSpanFull()->placeholder('No description.'),
                    ]),

                Section::make('Visibility')
                    ->columns(3)
                    ->components([
                        IconEntry::make('is_active')->boolean()->label('Active'),
                        IconEntry::make('is_featured')->boolean()->label('Featured'),
                        IconEntry::make('show_in_menu')->boolean()->label('Show in menu'),
                    ]),

                Section::make('SEO')
                    ->columns(2)
                    ->components([
                        TextEntry::make('seo_title')->placeholder('—'),
                        TextEntry::make('seo_description')->placeholder('—'),
                        TextEntry::make('created_at')->label('Created')->dateTime('M j, Y'),
                        TextEntry::make('updated_at')->label('Updated')->since(),
                    ]),
            ]);
    }
}
