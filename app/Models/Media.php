<?php

namespace App\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'path',
    'original_filename',
    'display_name',
    'mime_type',
    'size',
    'width',
    'height',
    'alt_text',
    'caption',
    'collection',
    'uploaded_by',
    'meta',
])]
class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    protected $table = 'media';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'meta' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): ?string
    {
        return $this->path
            ? Storage::disk(config('filament.default_filesystem_disk', 'public'))->url($this->path)
            : null;
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = (int) $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, $index === 0 ? 0 : 1).' '.$units[$index];
    }

    public function getDimensionsAttribute(): ?string
    {
        if (! $this->width || ! $this->height) {
            return null;
        }

        return $this->width.' × '.$this->height.' px';
    }
}
