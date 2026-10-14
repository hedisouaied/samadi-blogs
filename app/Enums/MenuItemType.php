<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MenuItemType: string implements HasColor, HasIcon, HasLabel
{
    case CustomUrl = 'custom_url';
    case Category = 'category';
    case Article = 'article';

    public function getLabel(): string
    {
        return match ($this) {
            self::CustomUrl => 'Custom / Internal URL',
            self::Category => 'Category',
            self::Article => 'Article',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::CustomUrl => 'gray',
            self::Category => 'info',
            self::Article => 'success',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::CustomUrl => 'heroicon-o-link',
            self::Category => 'heroicon-o-folder',
            self::Article => 'heroicon-o-document-text',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->getLabel()])
            ->all();
    }
}
