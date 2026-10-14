<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'slug',
    'description',
    'color',
    'is_active',
    'show_in_menu',
])]
class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory, HasSlug, SoftDeletes;

    public static string $slugSource = 'name';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_in_menu' => 'boolean',
        ];
    }

    /** @return BelongsToMany<Article, $this> */
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }

    /** @param Builder<Tag> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
