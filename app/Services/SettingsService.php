<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Lightweight database-backed settings repository.
 *
 * Defaults live in config/settings.php; overrides are persisted one row per
 * key in the `settings` table so we never need to duplicate rows. Values are
 * cached and the cache is flushed whenever a setting changes.
 */
class SettingsService
{
    protected const CACHE_KEY = 'app.settings.values';

    /** @var array<string, mixed>|null */
    protected ?array $values = null;

    /**
     * Fully qualified key: "{group}.{key}".
     *
     * @return array<string, mixed>
     */
    public function values(): array
    {
        if ($this->values !== null) {
            return $this->values;
        }

        $values = Cache::rememberForever(self::CACHE_KEY, function (): array {
            return Setting::query()
                ->get()
                ->mapWithKeys(fn (Setting $setting): array => [
                    $setting->group.'.'.$setting->key => $this->castFromStorage($setting),
                ])
                ->all();
        });

        return $this->values = array_merge($this->defaults(), $values);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values()[$key] ?? $default;
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default);

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function int(string $key, int $default = 0): int
    {
        return (int) $this->get($key, $default);
    }

    /**
     * Persist a flat array of "{group}.{key}" => value pairs.
     *
     * @param  array<string, mixed>  $values
     */
    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            [$group, $name] = array_pad(explode('.', $key, 2), 2, null);

            if ($group === null || $name === null) {
                continue;
            }

            Setting::query()->updateOrCreate(
                ['group' => $group, 'key' => $name],
                ['value' => $this->castForStorage($value), 'type' => $this->typeOf($group, $name)],
            );
        }

        $this->flush();
    }

    public function set(string $key, mixed $value): void
    {
        $this->setMany([$key => $value]);
    }

    public function flush(): void
    {
        $this->values = null;
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        $defaults = [];

        foreach (config('settings', []) as $group => $definition) {
            foreach ($definition['settings'] ?? [] as $key => $meta) {
                $defaults[$group.'.'.$key] = $meta['default'] ?? null;
            }
        }

        return $defaults;
    }

    /**
     * The settings schema, used to build the settings page.
     *
     * @return array<string, array<string, mixed>>
     */
    public function schema(): array
    {
        return config('settings', []);
    }

    protected function typeOf(string $group, string $key): string
    {
        return config("settings.{$group}.settings.{$key}.type", 'string');
    }

    protected function castForStorage(mixed $value): ?string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return $value === null ? null : (string) $value;
    }

    protected function castFromStorage(Setting $setting): mixed
    {
        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'json', 'array' => json_decode((string) $setting->value, true),
            default => $setting->value,
        };
    }
}
