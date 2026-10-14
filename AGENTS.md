# AGENTS.md

Guidance for AI coding agents working in the **Samadi Blogs admin CMS** repository.

## What this project is

A production-minded **admin back office only** for a blog, built with **Laravel 13** and
**Filament 5**. There is no public front end, API, or client application. The panel is
mounted at `/admin`.

For architecture and domain detail see the `docs/` folder and the project skill at
`.opencode/skills/filament-blog-cms/SKILL.md`.

## Before you start

- PHP 8.3+, Composer, MySQL 8. Run `php -v` and `composer -V` to confirm.
- Never expose secrets. Credentials live only in `.env`; keep `.env` out of version
  control. Quote database passwords — an unquoted `#` truncates the value.
- Do **not** run destructive database commands (no `migrate:fresh`, no dropping
  tables). Use `php artisan migrate` and `php artisan db:seed`.

## Core conventions

- Model `Article`, table `articles`, comment FK `article_id` (never `blog_id`).
- Authorization is **permission-based**: `Permission` enum →
  `UserRole::permissions()` → policies. Always enforce server-side, never only in the
  UI.
- Models declare fillable/hidden via PHP attributes (`#[Fillable([...])]`,
  `#[Hidden([...])]`).
- Article lifecycle transitions go through `App\Services\ArticleService`.
- Comment moderation goes through `App\Services\CommentService`.
- Settings are declarative: add to `config/settings.php` and read via
  `SettingsService`; the settings page updates automatically.
- Factories rely on model events for slug generation, so seeders must not use
  `WithoutModelEvents`.
- Do not add code comments unless explicitly asked.

## Filament 5 notes

See `.opencode/skills/filament-blog-cms/SKILL.md` for the full list of verified API
conventions and gotchas (Form `->footer()` not `->actions()`, `TextEntry::json()`
absence, valid heroicon names, `$livewire->redirect()`, `navigationGroup` typing,
etc.).

## Verification

There is no automated test suite by design. After changes run:

```sh
php -l <changed-files>
php artisan route:list --path=admin
php artisan filament:cache-components
php artisan optimize:clear
```

Then start `php artisan serve`, log in, and confirm affected routes return HTTP 200
with no new errors in `storage/logs/laravel.log`.

If you change configuration, tell the user to restart their editor/agent session where
relevant.

## Useful commands

```sh
php artisan serve                    # http://localhost:8000/admin
php artisan migrate
php artisan db:seed                  # admin account + demo content (non-production)
php artisan articles:publish-scheduled
php artisan schedule:work
php artisan storage:link
```
