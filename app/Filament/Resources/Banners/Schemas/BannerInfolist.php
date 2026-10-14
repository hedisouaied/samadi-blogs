<?php

namespace App\Filament\Resources\Banners\Schemas;

use App\Enums\BannerPlacement;
use App\Enums\BannerStatus;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BannerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Banner')
                    ->columns(3)
                    ->components([
                        ImageEntry::make('image_path')
                            ->label('Image')
                            ->disk('public')
                            ->height(180)
                            ->columnSpan(2),
                        TextEntry::make('title')->weight('bold')->columnSpanFull(),
                        TextEntry::make('placement')
                            ->badge()
                            ->formatStateUsing(fn (BannerPlacement $state): string => $state->getLabel())
                            ->color(fn (BannerPlacement $state): string => $state->getColor()),
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (BannerStatus $state): string => $state->getLabel())
                            ->color(fn (BannerStatus $state): string => $state->getColor()),
                        IconEntry::make('is_active')->label('Active')->boolean(),
                        TextEntry::make('subtitle')->placeholder('—'),
                        TextEntry::make('button_text')->placeholder('No button'),
                        TextEntry::make('target_url')->copyable()->placeholder('—'),
                        TextEntry::make('alt_text')->placeholder('—'),
                    ]),
                Section::make('Schedule')
                    ->columns(3)
                    ->components([
                        TextEntry::make('starts_at')->label('Starts')->dateTime('M j, Y H:i')->placeholder('Immediately'),
                        TextEntry::make('ends_at')->label('Ends')->dateTime('M j, Y H:i')->placeholder('No end date'),
                        TextEntry::make('sort_order')->label('Sort order')->badge()->color('gray'),
                        TextEntry::make('within_schedule')
                            ->label('In schedule')
                            ->state(fn ($record): string => $record->isWithinSchedule() ? 'Yes' : 'No')
                            ->badge()
                            ->color(fn ($record): string => $record->isWithinSchedule() ? 'success' : 'gray'),
                        TextEntry::make('displayable')
                            ->label('Displayable now')
                            ->state(fn ($record): string => $record->isDisplayable() ? 'Yes' : 'No')
                            ->badge()
                            ->color(fn ($record): string => $record->isDisplayable() ? 'success' : 'gray'),
                        TextEntry::make('created_at')->label('Created')->dateTime('M j, Y'),
                    ]),
            ]);
    }
}
