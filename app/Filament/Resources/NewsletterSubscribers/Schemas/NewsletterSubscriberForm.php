<?php

namespace App\Filament\Resources\NewsletterSubscribers\Schemas;

use App\Enums\SubscriberStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NewsletterSubscriberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Subscriber')
                    ->columns(2)
                    ->components([
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(190)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->maxLength(120),
                        Select::make('status')
                            ->options(SubscriberStatus::options())
                            ->default(SubscriberStatus::Pending->value)
                            ->required()
                            ->native(false),
                        TextInput::make('source')
                            ->label('Acquisition source')
                            ->maxLength(120)
                            ->placeholder('e.g. footer, popup, import'),
                    ]),

                Section::make('Consent & activity')
                    ->columns(3)
                    ->components([
                        DateTimePicker::make('consent_at')->label('Consent given at')->seconds(false)->native(false)->nullable(),
                        TextInput::make('consent_source')->label('Consent source')->maxLength(120)->placeholder('e.g. signup checkbox'),
                        DateTimePicker::make('subscribed_at')->label('Subscribed at')->seconds(false)->native(false)->nullable(),
                        DateTimePicker::make('unsubscribed_at')->label('Unsubscribed at')->seconds(false)->native(false)->nullable(),
                        DateTimePicker::make('last_email_sent_at')->label('Last email sent at')->seconds(false)->native(false)->nullable(),
                    ]),
            ]);
    }
}
