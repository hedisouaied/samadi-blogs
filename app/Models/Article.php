<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use App\Models\Concerns\HasSlug;
use Database\Factories\ArticleFactory;
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
    'author_id',
    'title',
    'slug',
    'excerpt',
    'content',
    'featured_image',
    'featured_image_alt',
    'status',
    'is_featured',
    'is_active',
    'allow_comments',
    'published_at',
    'scheduled_at',
    'reading_time_minutes',
    'seo_title',
    'seo_description',
    'canonical_url',
    'seo_noindex',
])]
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory, HasSlug, SoftDeletes;

    public static string $slugSource = 'title';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'allow_comments' => 'boolean',
            'seo_noindex' => 'boolean',
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'reading_time_minutes' => 'integer',
            'view_count' => 'integer',
            'like_count' => 'integer',
            'share_count' => 'integer',
        ];
    }

    /* -----------------------------------------------------------------
     | Relationships
     | ----------------------------------------------------------------- */

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** @return BelongsToMany<Category, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /** @return HasMany<Comment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** @return HasMany<Comment, $this> */
    public function rootComments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    /** @return HasMany<ArticleRevision, $this> */
    public function revisions(): HasMany
    {
        return $this->hasMany(ArticleRevision::class)->latest();
    }

    /* -----------------------------------------------------------------
     | Accessors
     | ----------------------------------------------------------------- */

    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featured_image
            ? Storage::disk(config('filament.default_filesystem_disk', 'public'))->url($this->featured_image)
            : null;
    }

    public function getSeoTitleDisplayAttribute(): string
    {
        return $this->seo_title ?: $this->title;
    }

    /* -----------------------------------------------------------------
     | Business logic
     | ----------------------------------------------------------------- */

    public function isPublished(): bool
    {
        return $this->status === ArticleStatus::Published
            && $this->is_active
            && $this->published_at !== null
            && $this->published_at->isPast();
    }

    public function isScheduled(): bool
    {
        return $this->status === ArticleStatus::Scheduled;
    }

    public function isEditableByAuthor(): bool
    {
        return in_array($this->status, [ArticleStatus::Draft, ArticleStatus::PendingReview], true);
    }

    /**
     * Recalculate the reading time from the current content.
     */
    public function calculateReadingTime(int $wordsPerMinute = 200): int
    {
        $text = trim(strip_tags((string) $this->content));
        $words = $text === '' ? 0 : count(preg_split('/\s+/u', $text) ?: []);

        return max(1, (int) ceil($words / max(1, $wordsPerMinute)));
    }

    public function syncReadingTime(int $wordsPerMinute = 200): void
    {
        $this->reading_time_minutes = $this->calculateReadingTime($wordsPerMinute);
    }

    /* -----------------------------------------------------------------
     | Scopes
     | ----------------------------------------------------------------- */

    /** @param Builder<Article> $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', ArticleStatus::Published->value)
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /** @param Builder<Article> $query */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /** @param Builder<Article> $query */
    public function scopeWithStatus(Builder $query, ArticleStatus $status): void
    {
        $query->where('status', $status->value);
    }

    /** @param Builder<Article> $query */
    public function scopeDueForPublication(Builder $query): void
    {
        $query->where('status', ArticleStatus::Scheduled->value)
            ->where('is_active', true)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now());
    }
}
