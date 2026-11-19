<?php

use App\Services\SettingsService;
use Illuminate\Support\Carbon;

if (! function_exists('setting')) {
    /**
     * Retrieve an application setting using its "{group}.{key}" name.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingsService::class)->get($key, $default);
    }
}

if (! function_exists('format_date')) {
    /**
     * Format a date using the configured site date format.
     */
    function format_date(DateTimeInterface|string|null $date, ?string $format = null): ?string
    {
        if ($date === null) {
            return null;
        }

        $format ??= (string) setting('general.date_format', 'M j, Y');

        return Carbon::parse($date)->locale(app()->getLocale())->translatedFormat($format);
    }
}

if (! function_exists('supported_locales')) {
    /**
     * @return array<string, array{name: string, native: string, dir: string}>
     */
    function supported_locales(): array
    {
        return config('app.supported_locales', []);
    }
}

if (! function_exists('locale_direction')) {
    function locale_direction(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return supported_locales()[$locale]['dir'] ?? 'ltr';
    }
}
