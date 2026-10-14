<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name',
    'slug',
    'description',
    'image_path',
    'parent_id',
    'color',
    'icon',
    'sort_order',
    'is_active',
    'is_featured',
    'show_in_menu',
    'seo_title',
    'seo_description',
])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, HasSlug, SoftDeletes;

    public static string $slugSource = 'name';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'show_in_menu' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /* -----------------------------------------------------------------
     | Relationships
     | ----------------------------------------------------------------- */

    /** @return BelongsTo<Category, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<Category, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** @return BelongsToMany<Article, $this> */
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }

    /* -----------------------------------------------------------------
     | Accessors
     | ----------------------------------------------------------------- */

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path
            ? Storage::disk(config('filament.default_filesystem_disk', 'public'))->url($this->image_path)
            : null;
    }

    /* -----------------------------------------------------------------
     | Business logic
     | ----------------------------------------------------------------- */

    /**
     * Determine whether assigning $parentId as the parent would create a cycle.
     */
    public function wouldCreateCycle(?int $parentId): bool
    {
        if (blank($parentId)) {
            return false;
        }

        if ($this->exists && (int) $parentId === (int) $this->getKey()) {
            return true;
        }

        $visited = [];
        $current = self::withTrashed()->find($parentId);

        while ($current) {
            if ($this->exists && (int) $current->getKey() === (int) $this->getKey()) {
                return true;
            }

            if (in_array($current->getKey(), $visited, true)) {
                break;
            }

            $visited[] = $current->getKey();
            $current = $current->parent_id
                ? self::withTrashed()->find($current->parent_id)
                : null;
        }

        return false;
    }

    /* -----------------------------------------------------------------
     | Scopes
     | ----------------------------------------------------------------- */

    /** @param Builder<Category> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @param Builder<Category> $query */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /** @param Builder<Category> $query */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }
}
