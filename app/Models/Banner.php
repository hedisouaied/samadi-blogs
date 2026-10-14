<?php

namespace App\Models;

use App\Enums\BannerPlacement;
use App\Enums\BannerStatus;
use App\Models\Concerns\HasSlug;
use Database\Factories\BannerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'title',
    'slug',
    'subtitle',
    'description',
    'image_path',
    'mobile_image_path',
    'alt_text',
    'target_url',
    'button_text',
    'placement',
    'status',
    'is_active',
    'sort_order',
    'starts_at',
    'ends_at',
    'open_in_new_tab',
    'created_by',
])]
class Banner extends Model
{
    /** @use HasFactory<BannerFactory> */
    use HasFactory, HasSlug, SoftDeletes;

    public static string $slugSource = 'title';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'placement' => BannerPlacement::class,
            'status' => BannerStatus::class,
            'is_active' => 'boolean',
            'open_in_new_tab' => 'boolean',
            'sort_order' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /* -----------------------------------------------------------------
     | Relationships
     | ----------------------------------------------------------------- */

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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

    public function getMobileImageUrlAttribute(): ?string
    {
        return $this->mobile_image_path
            ? Storage::disk(config('filament.default_filesystem_disk', 'public'))->url($this->mobile_image_path)
            : null;
    }

    /* -----------------------------------------------------------------
     | Business logic
     | ----------------------------------------------------------------- */

    /**
     * Whether the banner is currently within its active scheduling window.
     */
    public function isWithinSchedule(): bool
    {
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Whether the banner is eligible to be shown on the public front end.
     */
    public function isDisplayable(): bool
    {
        return $this->is_active
            && $this->status === BannerStatus::Published
            && $this->isWithinSchedule();
    }

    public function isScheduled(): bool
    {
        return $this->status === BannerStatus::Scheduled
            || ($this->starts_at !== null && $this->starts_at->isFuture());
    }

    /* -----------------------------------------------------------------
     | Scopes
     | ----------------------------------------------------------------- */

    /** @param Builder<Banner> $query */
    public function scopeDisplayable(Builder $query): void
    {
        $query->where('status', BannerStatus::Published->value)
            ->where('is_active', true)
            ->where(fn (Builder $query): Builder => $query
                ->whereNull('starts_at')
                ->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $query): Builder => $query
                ->whereNull('ends_at')
                ->orWhere('ends_at', '>=', now()));
    }

    /** @param Builder<Banner> $query */
    public function scopePlacement(Builder $query, BannerPlacement $placement): void
    {
        $query->where('placement', $placement->value);
    }

    /** @param Builder<Banner> $query */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('title');
    }
}
