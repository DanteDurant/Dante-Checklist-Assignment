# PHP-FPM runtime for Laravel 11 — local dev / evaluator Docker Compose
FROM php:8.3-fpm-bookworm

LABEL maintainer="compliance-checklist"
LABEL php.version="8.3"

# System deps + libraries for Laravel, DomPDF, MySQL, GD
RUN apt-get update && apt-get install -y --no-install-recommends \
    acl \
    curl \
    git \
    unzip \
    zip \
    libicu-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
        xml \
        dom \
    && rm -rf /var/lib/apt/lists/*

# Composer (official image binary)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_NO_INTERACTION=1

# Node.js 20.x (npm run build / optional Vite dev in same container)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/php-local.ini /usr/local/etc/php/conf.d/zzz-local.ini
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint-laravel.sh
RUN chmod +x /usr/local/bin/docker-entrypoint-laravel.sh \
    && chown www-data:www-data /var/www

WORKDIR /var/www/html

ENTRYPOINT ["docker-entrypoint-laravel.sh"]
CMD ["php-fpm"]
