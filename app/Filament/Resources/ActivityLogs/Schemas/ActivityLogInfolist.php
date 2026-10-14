<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Activity')
                    ->columns(2)
                    ->components([
                        TextEntry::make('action_label')->label('Action')->badge()->color('info'),
                        TextEntry::make('created_at')->label('When')->dateTime('M j, Y H:i:s'),
                        TextEntry::make('user.name')->label('Performed by')->placeholder('System'),
                        TextEntry::make('subject')
                            ->label('Subject')
                            ->placeholder('—')
                            ->state(fn ($record): string => $record->subject
                                ? class_basename($record->subject).' #'.$record->subject_id
                                : '—'),
                        TextEntry::make('description')->columnSpanFull()->placeholder('—'),
                        TextEntry::make('ip_address')->label('IP address')->placeholder('—'),
                        TextEntry::make('properties')
                            ->label('Details')
                            ->formatStateUsing(fn ($state): ?string => filled($state)
                                ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                                : null)
                            ->columnSpanFull()
                            ->placeholder('No extra details recorded.'),
                    ]),
            ]);
    }
}
