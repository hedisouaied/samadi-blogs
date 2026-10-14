<?php

namespace App\Models;

use App\Enums\MenuItemType;
use Database\Factories\MenuItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'menu_id',
    'parent_id',
    'label',
    'type',
    'article_id',
    'category_id',
    'url',
    'sort_order',
    'open_in_new_tab',
    'is_active',
])]
class MenuItem extends Model
{
    /** @use HasFactory<MenuItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MenuItemType::class,
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<Menu, $this> */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /** @return BelongsTo<MenuItem, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<MenuItem, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /** @return BelongsTo<Article, $this> */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Resolve the destination URL for the item based on its type.
     * Returns a relative path; the future front end maps it to real routes.
     */
    public function resolveUrl(): ?string
    {
        return match ($this->type) {
            MenuItemType::CustomUrl => $this->url,
            MenuItemType::Category => $this->category ? '/category/'.$this->category->slug : null,
            MenuItemType::Article => $this->article ? '/blog/'.$this->article->slug : null,
        };
    }

    /**
     * Whether assigning $parentId as the parent would create a cycle.
     */
    public function wouldCreateCycle(?int $parentId): bool
    {
        if (blank($parentId)) {
            return false;
        }

        if ($this->exists && (int) $parentId === (int) $this->getKey()) {
            return true;
        }

        $current = self::find($parentId);

        while ($current) {
            if ($this->exists && (int) $current->getKey() === (int) $this->getKey()) {
                return true;
            }

            $current = $current->parent_id ? self::find($current->parent_id) : null;
        }

        return false;
    }
}
