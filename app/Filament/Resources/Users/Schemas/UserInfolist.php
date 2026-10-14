<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile')
                    ->columns(3)
                    ->components([
                        ImageEntry::make('avatar_path')
                            ->label('Avatar')
                            ->disk('public')
                            ->circular()
                            ->height(120)
                            ->defaultImageUrl(fn ($record): string => $record->getFilamentAvatarUrl() ?? ''),
                        Grid::make(2)
                            ->columnSpan(2)
                            ->components([
                                TextEntry::make('name')->weight('bold')->size('lg'),
                                TextEntry::make('username')->placeholder('—'),
                                TextEntry::make('email')->icon('heroicon-o-envelope')->copyable(),
                                TextEntry::make('job_title')->placeholder('—'),
                                TextEntry::make('role')
                                    ->badge()
                                    ->formatStateUsing(fn (UserRole $state): string => $state->getLabel())
                                    ->color(fn (UserRole $state): string => $state->getColor()),
                                TextEntry::make('status')
                                    ->badge()
                                    ->formatStateUsing(fn (UserStatus $state): string => $state->getLabel())
                                    ->color(fn (UserStatus $state): string => $state->getColor()),
                                TextEntry::make('last_login_at')
                                    ->label('Last login')
                                    ->since()
                                    ->placeholder('Never'),
                                TextEntry::make('created_at')->label('Joined')->dateTime('M j, Y'),
                            ]),
                        TextEntry::make('bio')
                            ->columnSpanFull()
                            ->placeholder('No biography provided.'),
                    ]),

                Section::make('Contribution')
                    ->columns(4)
                    ->components([
                        TextEntry::make('articles_count')
                            ->label('Articles')
                            ->state(fn ($record): int => $record->articles()->count())
                            ->numeric()
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('comments_count')
                            ->label('Comments')
                            ->state(fn ($record): int => $record->comments()->count())
                            ->numeric()
                            ->badge()
                            ->color('info'),
                        TextEntry::make('email_verified_at')->label('Email verified')->dateTime('M j, Y')->placeholder('Not verified'),
                        TextEntry::make('updated_at')->label('Last updated')->since(),
                    ]),
            ]);
    }
}
