<?php

namespace App\Filament\Concerns;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

use function Filament\Support\get_model_label;

trait TranslatesResourceLabels
{
    protected static function baseModelLabel(): string
    {
        return static::$modelLabel ?? get_model_label(static::getModel());
    }

    public static function getModelLabel(): string
    {
        return __(static::baseModelLabel());
    }

    public static function getPluralModelLabel(): string
    {
        return __(static::$pluralModelLabel ?? Str::plural(static::baseModelLabel()));
    }

    public static function hasTitleCaseModelLabel(): bool
    {
        return app()->getLocale() === 'en';
    }

    public static function getNavigationLabel(): string|Htmlable
    {
        if (filled(static::$navigationLabel)) {
            return __(static::$navigationLabel);
        }

        return Str::ucfirst(static::getPluralModelLabel());
    }
}
