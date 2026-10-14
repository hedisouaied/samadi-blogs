# Media library and menus

## Media library

The media library is a **reusable asset registry**. Files are stored on the
configured disk (default `public`), while content tables (articles, banners, settings)
store plain **path strings** as the source of truth. The `media` table records
metadata about assets so they can be browsed, re-used and safely deleted.

### Storage

- Uploads are stored on the disk returned by
  `config('filament.default_filesystem_disk')` (set to `public` in this project).
- Run `php artisan storage:link` once so `/storage/...` URLs resolve.
- `Media::getUrlAttribute()` resolves the public URL; `Media::isImage()` checks the
  MIME type; `getHumanSizeAttribute()` and `getDimensionsAttribute()` provide display
  values.

### Behaviour

- Creating a media record stores the file, sets `uploaded_by`, defaults the
  `display_name` from the original filename, and calls
  `MediaService::syncMetadata()` to persist MIME type, size and (for images)
  dimensions.
- The media table shows an image preview for image assets and offers "open" and
  "copy URL" actions.
- Deleting a media record also removes the underlying file via
  `MediaService::deleteFile()`.

> Because content stores paths rather than foreign keys, deleting a media record does
> not break existing content — it only removes the file and the registry entry.

## Menus

Menus are nested navigation structures (`Menu` → `MenuItem`), managed through the
`MenuResource` and its `ItemsRelationManager`.

### Item types

`App\Enums\MenuItemType`:

| Type | Value | Destination |
| --- | --- | --- |
| Custom URL | `custom_url` | `MenuItem::$url` |
| Category | `category` | `/category/{slug}` for the linked category |
| Article | `article` | `/blog/{slug}` for the linked article |

`MenuItem::resolveUrl()` returns the relative destination; the future public front end
maps these paths to real routes. The item form shows the relevant field(s) for the
selected type.

### Guards

- **Cycle protection.** `MenuItem::wouldCreateCycle()` prevents a menu item from
  becoming its own ancestor. The relation manager enforces this via
  `mutateFormDataUsing()` before saving.
- **Menu scoping.** The parent select only lists items belonging to the same menu.
- **Reordering.** Items are drag-reorderable by `sort_order`.

### Categories

Categories are also hierarchical and use the same cycle-protection pattern
(`Category::wouldCreateCycle()`), plus drag reordering by `sort_order`.
