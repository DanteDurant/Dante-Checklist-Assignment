#!/bin/sh
set -e

# Docker Compose loads `.env` via env_file: an empty APP_KEY= becomes a real env var and overrides the
# key stored in the mounted `.env` file. Clear it so Laravel reads APP_KEY from the file on disk.
if [ -z "${APP_KEY:-}" ]; then
    unset APP_KEY
fi

# Writable paths for Laravel (bind-mounted project may be root-owned on host)
if [ -d /var/www/html/storage ]; then
    mkdir -p \
        /var/www/html/storage/logs \
        /var/www/html/storage/framework/sessions \
        /var/www/html/storage/framework/views \
        /var/www/html/storage/framework/cache/data \
        /var/www/html/storage/app/private/exports \
        /var/www/html/bootstrap/cache
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
    chmod -R ug+rwX /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
fi

cd /var/www/html 2>/dev/null || true
if [ -f artisan ] && [ -f vendor/autoload.php ] && [ -f .env ]; then
    if ! grep -qE '^APP_KEY=base64:' .env 2>/dev/null; then
        unset APP_KEY
        php artisan key:generate --force --no-interaction 2>/dev/null || true
    fi
fi

if [ -z "${APP_KEY:-}" ]; then
    unset APP_KEY
fi

exec docker-php-entrypoint "$@"
