<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AdminNavigationGroup implements HasIcon, HasLabel
{
    case Content;
    case Community;
    case Administration;

    public function getLabel(): string
    {
        return match ($this) {
            self::Content => __('Content'),
            self::Community => __('Community'),
            self::Administration => __('Administration'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Content => 'heroicon-o-document-duplicate',
            self::Community => 'heroicon-o-users',
            self::Administration => 'heroicon-o-cog-6-tooth',
        };
    }
}
