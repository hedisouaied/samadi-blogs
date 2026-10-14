<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'slug', 'description', 'is_active'])]
class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use HasFactory, HasSlug, SoftDeletes;

    public static string $slugSource = 'name';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<MenuItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    /** @return HasMany<MenuItem, $this> */
    public function rootItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('sort_order');
    }
}
