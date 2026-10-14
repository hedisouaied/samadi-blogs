---
name: filament-blog-cms
description: Use when working on the Samadi Blogs Laravel + Filament 5 admin CMS — adding or editing Filament resources, schemas, tables, pages, widgets, policies, enums, services, factories or seeders in this repository. Covers Filament 5 API conventions and the specific gotchas discovered while building this panel.
---

# Samadi Blogs admin CMS

Laravel 13 + Filament 5 back office only (no public front end). Panel id `admin`, path
`/admin`. Read `AGENTS.md` and the `docs/` folder for the full picture.

## Non-negotiable conventions

- Articles: model `Article`, table `articles`, comments FK **`article_id`** (never
  `blog_id`).
- Authorization is permission-based: `Permission` enum → `UserRole::permissions()` →
  policies. Never rely on UI checks alone; always enforce server-side.
- Models use PHP attributes `#[Fillable([...])]` / `#[Hidden([...])]` (Laravel 13),
  not `$fillable` / `$hidden` properties.
- Do not add code comments unless asked.
- Do not run destructive database commands. Demo data is seeded by `db:seed`.

## Filament 5 API cheat-sheet

- Page/resource schemas: `Filament\Schemas\Schema` with `->components([...])`.
- Layout components live in `Filament\Schemas\Components\*` (`Section`, `Grid`,
  `Tabs`, `Tabs\Tab`, `Form`, `EmbeddedSchema`).
- Actions: `Filament\Actions\*`. Table actions go in `->recordActions()` /
  `->toolbarActions()` (not `->actions()`).
- Icons: use `Filament\Support\Icons\Heroicon` or the string form `'heroicon-o-...'`.
- Infolist entries: `Filament\Infolists\Components\*`.

### Gotchas that cost time (verified)

- `Filament\Schemas\Components\Form` has **no** `actions()` / `alignEnd()`. Put submit
  buttons in `->footer([...])` (or `->header([...])`), and use
  `Action::make('save')->submit('save')`.
- `TextEntry` has no `->json()`. Use
  `->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : null)`.
- Not every heroicon name exists. `heroicon-o-pin` is invalid; use
  `heroicon-o-map-pin` / `heroicon-o-bookmark` (and `-bookmark-slash`).
- `redirect()` does not perform a Livewire redirect. Use `$this->redirect(...)` in a
  page, or `$livewire->redirect(...)` inside a table action closure.
- Closures in instance methods keep `$this`; **static** contexts (e.g. a static
  `Table::configure`) must not use `$this` — inject `$livewire`.
- A Filament page property `navigationGroup` must be typed
  `string|UnitEnum|null` (narrowing to `string|null` is a fatal error).
- `Resource::canEdit(Model $record)` / `canDelete` must type
  `Illuminate\Database\Eloquent\Model`.
- Avoid `->sortable()` / grouping on dotted relation columns (e.g. `user.name`);
  `searchable(['users.name'])` is fine.
- Livewire `SupportFileDownloads` streams a returned `Responsable`, so returning
  `Csv::stream(...)` from an action works.
- Returning a `StreamedResponse` (CSV export) is fine from a header action.
- `ArticleService` owns article state transitions; call it rather than mutating status
  directly.

## Slugs

`App\Models\Concerns\HasSlug` generates a unique slug on save **only when blank**,
honouring soft deletes. Models set `public static string $slugSource` (e.g. `'title'`
or `'name'`). Factories therefore do not need to pass `slug`.

> Factories rely on model events for slug generation, so seeders must NOT use
> `WithoutModelEvents`.

## Verification (no test suite by design)

After changes, run:

```sh
php -l <changed-files>
php artisan route:list --path=admin
php artisan filament:cache-components
php artisan optimize:clear
```

Then render the affected pages while authenticated (start `php artisan serve`, log in,
GET each route and confirm HTTP 200 and no new entries in `storage/logs/laravel.log`).

## Key files

- `app/Services/{ArticleService,CommentService,SettingsService,MediaService,ActivityLogger}.php`
- `app/Enums/{Permission,UserRole,ArticleStatus,CommentStatus,BannerStatus,BannerPlacement,SubscriberStatus,MenuItemType,UserStatus}.php`
- `config/settings.php` — settings schema + defaults (drives `app/Filament/Pages/Settings.php`)
- `database/seeders/{AdminUserSeeder,DemoContentSeeder}.php`
- `docs/` — architecture, roles, workflow, settings, media/menus, deployment
