# Samadi Blogs — Admin CMS

A production-minded **blog content management back office** built with **Laravel 13** and
**Filament 5**. This repository contains the admin panel only — there is no public
front end, API, or client application.

The panel lives at `/admin` and manages the full editorial surface of a blog:
articles, taxonomy, moderated comments, promotional banners, newsletter subscribers,
a reusable media library, navigation menus, revision history, activity auditing and
application settings.

---

## Tech stack

| Layer      | Choice                                   |
| ---------- | ---------------------------------------- |
| Framework  | Laravel 13 (`laravel/framework ^13.17`)  |
| Admin UI   | Filament 5 (`filament/filament ^5.10`)   |
| Runtime    | Livewire 4, Alpine.js, Tailwind (via Filament) |
| Language   | PHP 8.3                                   |
| Database   | MySQL 8                                   |
| Assets     | Vite                                      |

---

## Getting started

### 1. Requirements

- PHP 8.3+
- Composer 2
- MySQL 8 (or any Laravel-supported database)
- Node.js + npm (only needed to build front-end assets)

### 2. Install

```sh
composer install
cp .env.example .env
php artisan key:generate
```

Configure your database credentials in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=samadi_blogs
DB_USERNAME=samadi
DB_PASSWORD="change-me"
```

> The password **must be wrapped in quotes** — a `#` in an unquoted value is treated
> as the start of a comment by PHP's dotenv parser and will silently truncate it.

### 3. Database, storage and assets

```sh
php artisan migrate
php artisan db:seed          # creates the admin account + demo content
php artisan storage:link     # expose uploaded media at /storage
npm install && npm run build # optional: build front-end assets
```

### 4. Run

```sh
php artisan serve
```

Then sign in at <http://localhost:8000/admin>.

### Default administrator

| Email             | Password   | Role          |
| ----------------- | ---------- | ------------- |
| `admin@admin.com` | `admin123` | `super_admin` |

> **Change this password immediately in any non-local environment.** The
> `AdminUserSeeder` is idempotent and only ever creates this single account.

---

## Feature overview

- **Articles** — rich text editor, featured image, excerpt, reading-time estimation,
  draft → review → scheduled → published workflow, featured/active toggles,
  duplication, revision snapshots and restore, SEO fields, per-status tabs and a
  navigation badge for pending review.
- **Categories & tags** — hierarchical categories with cycle protection and drag
  reordering, colour/icon metadata, tags with colour coding.
- **Comments** — threaded replies (max depth 3), moderation queue, approve / reject /
  spam / trash, pinning, bulk moderation, and reply-preserving deletion.
- **Banners** — placement targeting, scheduling windows, publish/archive controls.
- **Newsletter subscribers** — lifecycle statuses, CSV export, consent tracking.
- **Media library** — reusable asset registry with file metadata and duplicate-safe
  deletion.
- **Menus** — nested navigation builder with type-aware items (custom URL, category,
  article) and cycle protection.
- **Revision history** — every meaningful article change is snapshotted and can be
  restored.
- **Activity log** — read-only audit trail of meaningful actions.
- **Settings** — grouped, database-backed configuration built dynamically from
  `config/settings.php`.
- **Roles & permissions** — five roles backed by a granular permission enum, enforced
  in policies and at the UI level.
- **Dashboard** — KPI stat cards, a 12-month articles chart and a latest-articles table.

---

## Project layout

```
app/
  Console/Commands/        PublishScheduledArticles command
  Enums/                   Permission, UserRole, ArticleStatus, CommentStatus, …
  Filament/
    Pages/Settings.php     dynamic settings page
    Resources/**           one resource per domain model
    Widgets/**             dashboard stats, chart and table widgets
  Models/                  domain models + HasSlug concern
  Policies/                authorization for every model
  Services/                ArticleService, CommentService, SettingsService, …
  Support/                 Csv helper, global helpers
config/settings.php        settings schema + defaults
database/migrations        domain schema
database/seeders           AdminUserSeeder, DemoContentSeeder
docs/                      detailed documentation
```

---

## Scheduled publishing

Scheduled articles are published by the `articles:publish-scheduled` command, which is
registered in `routes/console.php` to run every minute:

```sh
php artisan articles:publish-scheduled
```

Run the Laravel scheduler (`php artisan schedule:work` in development, or a cron entry
in production) for automatic publishing.

---

## Documentation

- [`docs/architecture.md`](docs/architecture.md) — code structure and design decisions
- [`docs/roles-and-permissions.md`](docs/roles-and-permissions.md) — the authorization model
- [`docs/content-workflow.md`](docs/content-workflow.md) — article & comment lifecycles
- [`docs/settings.md`](docs/settings.md) — how settings work and how to extend them
- [`docs/media-and-menus.md`](docs/media-and-menus.md) — media library & menu builder
- [`docs/deployment.md`](docs/deployment.md) — production checklist

---

## License

Proprietary. © Samadi Blogs.
