# Application settings

Settings are declarative, database-backed and cached. The schema (groups, fields,
types, defaults and validation hints) lives in `config/settings.php`; overrides are
persisted one row per key in the `settings` table.

## How it works

- **Defaults** come from `config/settings.php`.
- **Overrides** are read from the `settings` table and merged over the defaults.
- **Reads** go through `SettingsService`, which caches the merged map with
  `Cache::rememberForever('app.settings.values')`.
- **Writes** use `SettingsService::setMany()` (or `set()`), which performs an
  `updateOrCreate` per key — so there is never more than one row per `group.key` —
  then flushes the cache.

### Reading values

```php
$settings = app(\App\Services\SettingsService::class);

$settings->get('general.site_name', 'Default');   // mixed
$settings->bool('content.allow_comments_by_default'); // bool
$settings->int('content.pagination_size', 15);    // int
```

A global helper `setting('general.site_name')` is also available.

### Writing values

```php
app(\App\Services\SettingsService::class)->setMany([
    'general.site_name' => 'My Blog',
    'content.reading_time_wpm' => 220,
]);
```

> Secrets must never be stored as settings. Use environment variables instead.

## The Settings page

`app/Filament/Pages/Settings.php` builds the form dynamically from the schema:

- Each group becomes a tab.
- Each field's input type is derived from its `type` (`string`, `text`, `integer`,
  `boolean`, `select`, `image`, `user`).
- Field names use a flat `group__key` state path, mapped back to `group.key` on save.
- The page is gated by `Permission::ViewSettings`; saving requires
  `Permission::ManageSettings`.

Field meta keys understood by the page:

| Key | Applies to | Effect |
| --- | --- | --- |
| `label` | all | Field label. |
| `type` | all | Input type (see above). |
| `default` | all | Default value. |
| `required` | all | Marks the field required. |
| `helper` | all | Helper text below the field. |
| `max` | string/text | Maximum length. |
| `min` / `max` | integer | Numeric range. |
| `email` | string | Validate as an email address. |
| `url` | string | Validate as a URL. |
| `options` | select | Either an array of `value => label` or an enum class name exposing `options()`. |

## Adding a setting

1. Add an entry to the relevant group in `config/settings.php`.
2. Read it via `SettingsService` (or `setting()`).

No migration or UI change is required — the settings page picks it up automatically.
