# Architecture

This document describes how the Samadi Blogs admin CMS is structured and the
reasoning behind the main design decisions.

## Overview

The application is a standard Laravel 13 project with a single Filament panel
registered by `app/Providers/Filament/AdminPanelProvider.php`:

- Panel id: `admin`
- Path: `/admin`
- Brand: **Samadi Blogs**, indigo primary / slate gray, dark mode enabled
- Navigation groups: `Content`, `Community`, `Administration`

Resources, pages and widgets are auto-discovered from `app/Filament/**`.

## Directory responsibilities

| Path | Responsibility |
| --- | --- |
| `app/Enums` | Domain enums. Each implements Filament's `HasLabel` / `HasColor` / `HasIcon` where useful and exposes a static `options()` helper for form building. |
| `app/Models` | Eloquent models. Business queries live in scopes; state-changing logic lives in services. |
| `app/Models/Concerns/HasSlug.php` | Reusable, collision-safe slug generation (honours soft deletes). |
| `app/Services` | Domain services (article lifecycle, comments, settings, media, activity). |
| `app/Policies` | One policy per model, delegating to the `Permission` enum. |
| `app/Filament/Resources` | One resource per model, split into `Schemas/`, `Tables/`, `Pages/` and `RelationManagers/`. |
| `app/Filament/Pages` | Standalone pages; currently the dynamic `Settings` page. |
| `app/Filament/Widgets` | Dashboard widgets. |
| `app/Support` | Small helpers (`Csv`, global functions). |
| `config/settings.php` | Declarative settings schema and defaults. |

## Models and relationships

```
User ──< Article (author_id)
User ──< Comment / ActivityLog / ArticleRevision
Article >──< Category (article_category)
Article >──< Tag      (article_tag)
Article ──< Comment (article_id)
Article ──< ArticleRevision
Comment ──< Comment (parent_id, self-referencing)
Category ──< Category (parent_id, self-referencing)
Menu ──< MenuItem (menu_id) ──< MenuItem (parent_id)
Banner ── User (created_by)
Media  ── User (uploaded_by)
```

### Naming conventions

- The model is **`Article`** and the table is **`articles`**.
- Comments reference articles via **`article_id`** (never `blog_id`).

## Services

| Service | Purpose |
| --- | --- |
| `ArticleService` | Article state transitions (`publish`, `schedule`, `unpublish`, `archive`, `submitForReview`, `duplicate`, `toggleFeatured`, `toggleActive`), revision capture and `publishDueScheduled()`. All transitions run in a database transaction and write an activity log entry. |
| `CommentService` | Moderation (`approve`, `reject`, `markAsSpam`, `moveToTrash`, `togglePinned`) and `deletePreservingReplies()`, which re-parents a deleted comment's replies to its parent instead of cascading. |
| `SettingsService` | Cached, database-backed key/value settings merged with `config/settings.php` defaults. Never duplicates rows — one row per `group.key`. |
| `ActivityLogger` | Writes `activity_logs` entries for meaningful actions. |
| `MediaService` | `syncMetadata()` and `deleteFile()` for the media library. |

## Authorization

Authorization is permission-based. `UserRole::permissions()` maps each role to a set
of `Permission` enum cases, `User::hasPermission()` checks membership, and every
policy delegates to those checks. Filament pages and actions additionally check
permissions for UI affordances, but enforcement is always server-side. See
[`roles-and-permissions.md`](roles-and-permissions.md).

## Slugs

`HasSlug` generates a unique slug on save **only when the slug is blank**, so editing
a title never silently changes an existing URL. A `-2`, `-3`, … suffix resolves
collisions, including against soft-deleted rows.

## Settings storage

Settings are stored one row per key in the `settings` table. Defaults live in
`config/settings.php`. Reads go through a `rememberForever` cache that is flushed on
write. See [`settings.md`](settings.md).

## Testing & verification

There is no automated test suite by design. Changes are verified by:

- `php -l` on changed files,
- `php artisan route:list --path=admin`,
- `php artisan filament:cache-components`,
- rendering each panel page while authenticated.
