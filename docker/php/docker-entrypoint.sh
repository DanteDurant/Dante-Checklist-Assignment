#!/bin/sh
set -e

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

exec docker-php-entrypoint "$@"
