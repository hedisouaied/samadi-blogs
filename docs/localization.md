# Localization

The admin panel and the public site are available in **English**, **French** and
**Arabic** (right-to-left). This page explains how the language is chosen, where the
translations live, and how to add new text or a new language.

Only the interface is translated. Content stored in the database (article titles and
bodies, category names, menu labels, settings such as the site description) is shown
exactly as it was written.

## How the language is chosen

`App\Http\Middleware\SetLocale` runs on every web request and on every admin panel
request. It picks the first supported value from:

1. the visitor's session (`locale`),
2. the `locale` cookie (kept for one year),
3. the **Default language** setting (*Settings → General*),
4. `config('app.locale')`.

Visitors switch language through `GET /locale/{locale}` (route `locale.switch`), which
stores the choice in the session and the cookie, then redirects back. Switchers are
shown:

- on the public site, in the header's top bar and in the mobile menu;
- in the admin panel, in the top bar next to the user menu;
- on the admin login and password-reset pages, below the form.

Supported languages are declared in `config/app.php` under `supported_locales`, with
their native name and text direction. `locale_direction()` returns `rtl` or `ltr` for
the current language and is used for the `dir` attribute on `<html>`.

## Where translations live

| File | Contents |
| --- | --- |
| `lang/fr.json`, `lang/ar.json` | All application text, keyed by the English sentence. |
| `lang/{fr,ar}/*.php` | Laravel's validation, auth, password and pagination messages. |
| `lang/vendor/filament-*/{fr,ar}/*.php` | Strings that Filament's own French and Arabic files are missing. Laravel merges these with Filament's translations. |

Filament ships French and Arabic translations for its own buttons and messages. The
files in `lang/vendor` only fill gaps; don't copy whole Filament files there.

## Writing translatable code

Wrap every user-facing string in `__()` and use the English text as the key:

```php
Notification::make()->title(__('Article published'))->success()->send();
```

```blade
<h2>{{ __('Most read') }}</h2>
<x-frontend.section-heading :eyebrow="__('Latest')" :title="__('Fresh from the desk')" />
```

Use placeholders instead of concatenating, so translators can reorder the sentence:

```php
__('From :date', ['date' => $date->translatedFormat('M j, Y')]);
```

Use `trans_choice()` for counts. Arabic has separate forms for one, two, three to ten
and eleven or more, so its entries use explicit ranges:

```json
":count article|:count articles": "{0} لا توجد مقالات|{1} مقال واحد|{2} مقالان|[3,10] :count مقالات|[11,*] :count مقالًا"
```

Then add the new key to **both** `lang/fr.json` and `lang/ar.json`. Keys without a
translation fall back to English.

### Filament labels

`AppServiceProvider::translateFilamentLabels()` calls `translateLabel()` on every form
field, table column, filter, infolist entry, tab and action. Labels, including those
Filament generates from field names (`TextInput::make('seo_title')` becomes "Seo
title"), are passed through `__()` automatically. You only need to add the English
label to the JSON files.

Other Filament text (section headings, descriptions, helper text, placeholders,
notifications, empty states) must be wrapped in `__()` explicitly.

Static properties can't call `__()`. For resources, the
`App\Filament\Concerns\TranslatesResourceLabels` trait translates `$modelLabel`,
`$pluralModelLabel` and `$navigationLabel` at runtime. Navigation groups use the
`App\Enums\AdminNavigationGroup` enum. Pages, widgets and relation managers override
`getTitle()`, `getHeading()` or `getTableHeading()` instead of setting a static string.

### Dates

Use `format_date()` or Carbon's `translatedFormat()` rather than `format()`, so month
and day names follow the current language. Filament's `date()` and `dateTime()`
columns already do this.

### JavaScript messages

Messages shown by the front-end scripts (form feedback, "Link copied") are passed from
`frontend/layouts/app.blade.php` as `window.i18n`. Add new keys there and read them
with a fallback, e.g. `window.i18n?.sending ?? 'Sending…'`.

## Right-to-left layout

On the public site, use Tailwind's logical utilities (`ms-*`, `me-*`, `ps-*`, `pe-*`,
`start-*`, `end-*`, `border-s`, `text-start`) instead of `ml-*`, `left-*`,
`text-left`, etc. Use the `rtl:` variant for anything that must change in Arabic.
Directional icons (`arrow-left`, `arrow-right`, `chevron-*`) mirror automatically in
`x-frontend.icon`.

The front-end font stacks include Noto Naskh Arabic and IBM Plex Sans Arabic. Browsers
use them only for Arabic characters, so Latin text keeps the existing fonts.

The Filament admin panel handles right-to-left layout by itself.

After changing CSS, classes in Blade views or `vite.config.js`, rebuild the assets with
`npm run build` and commit `public/build`. The deployment does not build them.

## Adding a language

1. Add it to `supported_locales` in `config/app.php`.
2. Add it to the `default_language` options in `config/settings.php`.
3. Create `lang/{code}.json` with the same keys as `lang/fr.json`.
4. Add Laravel's framework translations under `lang/{code}/` (for example from
   [Laravel-Lang](https://github.com/Laravel-Lang/lang)).
5. If Filament's translation for that language is incomplete, add the missing keys
   under `lang/vendor/`.

## Known limitations

- A 404 for a URL that matches no route is rendered before the language middleware
  runs, so it always uses the default language.
- Activity-log descriptions and revision notes are stored in English when they are
  recorded, and are shown as stored.
- Newsletter CSV exports keep English column headings.
