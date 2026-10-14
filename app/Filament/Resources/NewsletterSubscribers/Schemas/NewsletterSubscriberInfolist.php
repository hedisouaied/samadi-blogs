<?php

namespace App\Filament\Resources\NewsletterSubscribers\Schemas;

use App\Enums\SubscriberStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NewsletterSubscriberInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Subscriber')
                    ->columns(2)
                    ->components([
                        TextEntry::make('email')->copyable()->weight('bold'),
                        TextEntry::make('name')->placeholder('—'),
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (SubscriberStatus $state): string => $state->getLabel())
                            ->color(fn (SubscriberStatus $state): string => $state->getColor()),
                        TextEntry::make('source')->label('Source')->badge()->color('gray')->placeholder('—'),
                    ]),
                Section::make('Consent & timeline')
                    ->columns(3)
                    ->components([
                        TextEntry::make('consent_at')->label('Consent given')->dateTime('M j, Y H:i')->placeholder('—'),
                        TextEntry::make('consent_source')->label('Consent source')->placeholder('—'),
                        TextEntry::make('subscribed_at')->label('Subscribed')->dateTime('M j, Y H:i')->placeholder('—'),
                        TextEntry::make('unsubscribed_at')->label('Unsubscribed')->dateTime('M j, Y H:i')->placeholder('—'),
                        TextEntry::make('last_email_sent_at')->label('Last email')->since()->placeholder('Never'),
                        TextEntry::make('created_at')->label('Created')->dateTime('M j, Y H:i'),
                    ]),
            ]);
    }
}
