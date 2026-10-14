#!/usr/bin/env sh
set -e

if [ -z "${APP_KEY:-}" ]; then
    echo "[entrypoint] WARNING: APP_KEY is not set. Set it in your host environment variables."
fi

if [ -z "${DB_CONNECTION:-}" ] && [ -n "${MYSQLHOST:-}" ]; then
    export DB_CONNECTION=mysql
fi

if [ -z "${APP_URL:-}" ] && [ -n "${RAILWAY_PUBLIC_DOMAIN:-}" ]; then
    export APP_URL="https://${RAILWAY_PUBLIC_DOMAIN}"
fi

php artisan storage:link || true

echo "[entrypoint] Running migrations..."
php artisan migrate --force

if [ "${RUN_SEEDER:-true}" = "true" ]; then
    echo "[entrypoint] Seeding content..."
    php artisan db:seed --force || echo "[entrypoint] Seeder failed, continuing."
fi

echo "[entrypoint] Caching configuration and views..."
php artisan config:cache
php artisan view:cache

echo "[entrypoint] Starting server on port ${PORT:-8080}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
