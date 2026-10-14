<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;

/**
 * Keeps the media registry in sync with the underlying stored files.
 *
 * The file path is the source of truth for content; this service only
 * mirrors technical metadata (size, mime type, dimensions) into the
 * reusable media library records.
 */
class MediaService
{
    protected string $disk;

    public function __construct()
    {
        $this->disk = config('filament.default_filesystem_disk', 'public');
    }

    public function syncMetadata(Media $media): Media
    {
        if (blank($media->path)) {
            return $media;
        }

        $storage = Storage::disk($this->disk);

        if (! $storage->exists($media->path)) {
            return $media;
        }

        $media->mime_type = $storage->mimeType($media->path) ?: $media->mime_type;
        $media->size = $storage->size($media->path);

        if ($media->isImage()) {
            try {
                $dimensions = @getimagesize($storage->path($media->path));

                if (is_array($dimensions)) {
                    $media->width = $dimensions[0] ?? null;
                    $media->height = $dimensions[1] ?? null;
                }
            } catch (\Throwable) {
                // Non-fatal: dimensions are best-effort metadata.
            }
        }

        $media->save();

        return $media;
    }

    public function deleteFile(Media $media): void
    {
        if (filled($media->path)) {
            Storage::disk($this->disk)->delete($media->path);
        }
    }
}
