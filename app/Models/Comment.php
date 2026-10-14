<?php

namespace App\Models;

use App\Enums\CommentStatus;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'article_id',
    'user_id',
    'parent_id',
    'author_name',
    'author_email',
    'content',
    'status',
    'is_pinned',
    'approved_at',
    'approved_by',
    'ip_address',
    'user_agent',
])]
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Maximum supported reply depth. Replies deeper than this are attached
     * to the nearest ancestor at the maximum depth (see {@see self::resolveParent()}).
     */
    public const MAX_DEPTH = 3;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
            'is_pinned' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    /* -----------------------------------------------------------------
     | Relationships
     | ----------------------------------------------------------------- */

    /** @return BelongsTo<Article, $this> */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Comment, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<Comment, $this> */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('created_at');
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /* -----------------------------------------------------------------
     | Accessors
     | ----------------------------------------------------------------- */

    public function getDisplayAuthorNameAttribute(): string
    {
        return $this->user?->name ?: ($this->author_name ?: 'Guest');
    }

    public function getDisplayAuthorEmailAttribute(): ?string
    {
        return $this->user?->email ?: $this->author_email;
    }

    /**
     * Compute the depth of this comment within its thread.
     */
    public function depth(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent && $depth < 50) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    /* -----------------------------------------------------------------
     | Scopes
     | ----------------------------------------------------------------- */

    /** @param Builder<Comment> $query */
    public function scopeApproved(Builder $query): void
    {
        $query->where('status', CommentStatus::Approved->value);
    }

    /** @param Builder<Comment> $query */
    public function scopePending(Builder $query): void
    {
        $query->where('status', CommentStatus::Pending->value);
    }

    /** @param Builder<Comment> $query */
    public function scopeRoots(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    /**
     * A comment may become approved only when its parent (if any) is approved.
     */
    public function canBeApproved(): bool
    {
        if ($this->parent_id === null) {
            return true;
        }

        return $this->parent?->status === CommentStatus::Approved;
    }
}
