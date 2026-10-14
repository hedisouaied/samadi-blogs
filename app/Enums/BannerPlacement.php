<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BannerPlacement: string implements HasColor, HasIcon, HasLabel
{
    case HomepageHero = 'homepage_hero';
    case HomepageMiddle = 'homepage_middle';
    case Sidebar = 'sidebar';
    case ArticleInline = 'article_inline';
    case Footer = 'footer';

    public function getLabel(): string
    {
        return match ($this) {
            self::HomepageHero => 'Homepage Hero',
            self::HomepageMiddle => 'Homepage Middle',
            self::Sidebar => 'Sidebar',
            self::ArticleInline => 'Article Inline',
            self::Footer => 'Footer',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::HomepageHero => 'primary',
            self::HomepageMiddle => 'info',
            self::Sidebar => 'warning',
            self::ArticleInline => 'success',
            self::Footer => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::HomepageHero => 'heroicon-o-photo',
            self::HomepageMiddle => 'heroicon-o-rectangle-group',
            self::Sidebar => 'heroicon-o-view-columns',
            self::ArticleInline => 'heroicon-o-document-text',
            self::Footer => 'heroicon-o-bars-3-bottom-left',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $placement): array => [$placement->value => $placement->getLabel()])
            ->all();
    }
}
