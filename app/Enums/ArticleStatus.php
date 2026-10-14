<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ArticleStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingReview => 'Pending Review',
            self::Scheduled => 'Scheduled',
            self::Published => 'Published',
            self::Archived => 'Archived',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::PendingReview => 'warning',
            self::Scheduled => 'info',
            self::Published => 'success',
            self::Archived => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Draft => 'heroicon-o-document',
            self::PendingReview => 'heroicon-o-clipboard-document-check',
            self::Scheduled => 'heroicon-o-clock',
            self::Published => 'heroicon-o-check-badge',
            self::Archived => 'heroicon-o-archive-box',
        };
    }

    /** Whether the article is visible to the public front end. */
    public function isPubliclyVisible(): bool
    {
        return $this === self::Published;
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
