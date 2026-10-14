<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Automatically maintains a unique, URL-safe slug for a model.
 *
 * The slug is only generated when it is empty, so an existing slug is
 * preserved when the source attribute (usually the title) changes. Editors
 * can regenerate the slug explicitly through the Filament form action.
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::saving(function (Model $model): void {
            $column = $model->slugColumn();

            if (blank($model->getAttribute($column))) {
                $model->setAttribute($column, $model->generateUniqueSlug());
            } else {
                $model->setAttribute($column, Str::slug((string) $model->getAttribute($column)));
            }
        });
    }

    /**
     * Generate a collision-safe, unique slug from the configured source attribute.
     */
    public function generateUniqueSlug(?string $value = null): string
    {
        $value ??= (string) $this->getAttribute($this->slugSource());
        $base = Str::slug($value) ?: Str::random(8);

        $column = $this->slugColumn();
        $slug = $base;
        $suffix = 2;

        while ($this->slugExists($slug, $column)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, string $column): bool
    {
        $query = static::query()->where($column, $slug);

        if ($this->exists) {
            $query->whereKeyNot($this->getKey());
        }

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            $query->withTrashed();
        }

        return $query->exists();
    }

    public function slugColumn(): string
    {
        return defined(static::class.'::$slugColumn') ? static::$slugColumn : 'slug';
    }

    public function slugSource(): string
    {
        return defined(static::class.'::$slugSource') ? static::$slugSource : 'title';
    }
}
