<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SubscriberStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Subscribed = 'subscribed';
    case Unsubscribed = 'unsubscribed';
    case Bounced = 'bounced';
    case Suppressed = 'suppressed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Subscribed => 'Subscribed',
            self::Unsubscribed => 'Unsubscribed',
            self::Bounced => 'Bounced',
            self::Suppressed => 'Suppressed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Subscribed => 'success',
            self::Unsubscribed => 'gray',
            self::Bounced => 'danger',
            self::Suppressed => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Pending => 'heroicon-o-clock',
            self::Subscribed => 'heroicon-o-envelope',
            self::Unsubscribed => 'heroicon-o-envelope-open',
            self::Bounced => 'heroicon-o-exclamation-triangle',
            self::Suppressed => 'heroicon-o-no-symbol',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->getLabel()])
            ->all();
    }
}
