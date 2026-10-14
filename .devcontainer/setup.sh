#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

echo "==> Installing PHP dependencies"
composer install --no-interaction --prefer-dist --no-progress

if [ ! -f .env ]; then
    cp .env.example .env
fi

echo "==> Configuring SQLite for the Codespace"
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
sed -i 's/^DB_HOST=.*/# DB_HOST=/' .env
sed -i 's/^DB_PORT=.*/# DB_PORT=/' .env
sed -i 's/^DB_DATABASE=.*/# DB_DATABASE=/' .env
sed -i 's/^DB_USERNAME=.*/# DB_USERNAME=/' .env
sed -i 's/^DB_PASSWORD=.*/# DB_PASSWORD=/' .env

if [ -n "${CODESPACE_NAME:-}" ] && [ -n "${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-}" ]; then
    PUBLIC_URL="https://${CODESPACE_NAME}-8000.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}"
    sed -i "s#^APP_URL=.*#APP_URL=${PUBLIC_URL}#" .env
    echo "==> APP_URL set to ${PUBLIC_URL}"
fi

php artisan key:generate --force

mkdir -p database
[ -f database/database.sqlite ] || touch database/database.sqlite

echo "==> Migrating and seeding demo content"
php artisan migrate --seed --force

php artisan storage:link || true

echo "==> Setup complete"
