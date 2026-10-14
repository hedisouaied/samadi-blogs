#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

if ! pgrep -f "artisan serve" >/dev/null 2>&1; then
    echo "==> Starting Laravel on port 8000"
    nohup php artisan serve --host=0.0.0.0 --port=8000 >/tmp/laravel-serve.log 2>&1 &
fi
