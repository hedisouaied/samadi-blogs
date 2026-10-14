#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

if ! pgrep -f "artisan serve" >/dev/null 2>&1; then
    echo "==> Starting Laravel on port 8000"
    nohup php artisan serve --host=0.0.0.0 --port=8000 >/tmp/laravel-serve.log 2>&1 &
fi

if ! pgrep -f "cloudflared tunnel" >/dev/null 2>&1; then
    if [ ! -x "$HOME/cloudflared" ]; then
        echo "==> Downloading cloudflared"
        curl -sL -o "$HOME/cloudflared" \
            https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64 || true
        chmod +x "$HOME/cloudflared" || true
    fi
    echo "==> Starting Cloudflare quick tunnel"
    nohup "$HOME/cloudflared" tunnel --url http://localhost:8000 --no-autoupdate \
        > "$HOME/cloudflared.log" 2>&1 &
fi
