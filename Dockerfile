# syntax=docker/dockerfile:1.7

# ---------------------------------------------------------------------------
# Tahap 1: build aset frontend (Vite + Tailwind)
# ---------------------------------------------------------------------------
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm run build

# ---------------------------------------------------------------------------
# Tahap 2: aplikasi Laravel + Apache + PHP 8.2
# ---------------------------------------------------------------------------
FROM php:8.2-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
    COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        git \
        unzip \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libxml2-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Apache: document root ke /public, izinkan .htaccess, matikan directory listing.
RUN sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri "s!<Directory /var/www/>!<Directory ${APACHE_DOCUMENT_ROOT}/>!g" /etc/apache2/apache2.conf \
    && sed -ri "s!Options Indexes FollowSymLinks!Options -Indexes +FollowSymLinks!g" /etc/apache2/apache2.conf \
    && sed -ri "s!AllowOverride None!AllowOverride All!g" /etc/apache2/apache2.conf \
    && printf '%s\n' \
        'ServerName localhost' \
        'ServerTokens Prod' \
        'ServerSignature Off' \
        > /etc/apache2/conf-available/perkoci.conf \
    && a2enconf perkoci

# Batas upload foto menu/bukti bayar dan performa produksi.
RUN printf '%s\n' \
    'expose_php=Off' \
    'upload_max_filesize=12M' \
    'post_max_size=12M' \
    'memory_limit=256M' \
    'max_execution_time=120' \
    'date.timezone=Asia/Jakarta' \
    'opcache.enable=1' \
    'opcache.validate_timestamps=0' \
    'opcache.memory_consumption=128' \
    'realpath_cache_size=4096K' \
    > /usr/local/etc/php/conf.d/perkoci.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Layer dependensi dipisah agar cache build tetap cepat saat kode berubah.
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --no-autoloader \
        --prefer-dist

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN mkdir -p \
        bootstrap/cache \
        storage/app/private \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
    && composer dump-autoload --optimize --no-dev --no-interaction --no-scripts \
    && php artisan package:discover --ansi \
    && sed -i 's/\r$//' docker/entrypoint.sh \
    && chmod +x docker/entrypoint.sh \
    && cp docker/entrypoint.sh /usr/local/bin/perkoci-entrypoint.sh \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD curl -fsS http://127.0.0.1/up || exit 1

ENTRYPOINT ["/usr/local/bin/perkoci-entrypoint.sh"]
CMD ["apache2-foreground"]
