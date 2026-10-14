# Deployment

## Environment

Copy `.env.example` to `.env` and set at minimum:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_KEY=            # php artisan key:generate
APP_URL=https://your-domain.example

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=samadi_blogs
DB_USERNAME=samadi
DB_PASSWORD="strong-secret-here"

FILESYSTEM_DISK=public
```

> Always quote the database password. An unquoted `#` starts a dotenv comment and
> silently truncates the value.

## Deploy steps

```sh
composer install --no-dev --optimize-autoloader
php artisan key:generate          # first deploy only
php artisan migrate --force
php artisan storage:link          # first deploy only
npm ci && npm run build           # if serving front-end assets
php artisan optimize              # config + route + view + event caches
```

`DatabaseSeeder` only seeds demo content outside `production`, so running
`php artisan db:seed --force` in production creates the administrator account **and
nothing else**.

## After deploying

1. **Change the administrator password.** The seeded `admin@admin.com` /
   `admin123` account is for bootstrap only. Update it via Profile immediately.
2. Confirm `/admin/login` is reachable and the panel loads over HTTPS.
3. Ensure the `public/storage` symlink exists.

## Scheduled publishing

Register the Laravel scheduler so scheduled articles are published automatically.
Either run the scheduler as a long-lived process:

```sh
php artisan schedule:work
```

or add a cron entry:

```cron
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

The `articles:publish-scheduled` command runs every minute and is safe to run
manually and repeatedly.

## Caches

The project uses the `database` cache and session stores by default. Running
`php artisan optimize` caches config/routes/views/events; re-run it (or
`php artisan optimize:clear`) after deploys that change configuration.

## Permissions

Ensure the web server user can write to `storage/` and `bootstrap/cache/`:

```sh
chown -R www-data:www-data storage bootstrap/cache
```

## Security checklist

- `APP_DEBUG=false` in production.
- Strong `APP_KEY` and database password; never commit `.env`.
- HTTPS only; set `SESSION_SECURE_COOKIE=true` if serving exclusively over TLS.
- Change the bootstrap administrator password.
- Restrict filesystem permissions to the least required.
