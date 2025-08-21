# ---------- Stage 1: PHP deps (Composer) ----------
FROM composer:2 AS php-deps
WORKDIR /app

# Copy only composer files first for cache efficiency
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

# Bring full app source (excluded by .dockerignore where appropriate)
COPY . .

# ---------- Stage 2: Frontend build (Vite/Vue) ----------
FROM node:20 AS build-assets
WORKDIR /app

# Ziggy (or other vendor-driven steps) need vendor present
COPY --from=php-deps /app/vendor ./vendor

# Install frontend deps with lockfile
COPY package*.json ./
RUN npm ci

# Build assets
COPY . .
RUN npm run build

# ---------- Stage 3: Runtime (PHP-FPM) ----------
FROM php:8.3-fpm

WORKDIR /var/www/html

# System deps (lean) + php extensions + redis + cleanup
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev libfcgi-bin \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath opcache \
    && pecl install redis \
    && docker-php-ext-enable redis opcache \
    && rm -rf /var/lib/apt/lists/*

# Reasonable OPcache defaults
RUN { \
    echo "opcache.enable=1"; \
    echo "opcache.enable_cli=1"; \
    echo "opcache.validate_timestamps=0"; \
    echo "opcache.jit=1255"; \
    echo "opcache.jit_buffer_size=128M"; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Enable php-fpm ping for container healthcheck
RUN { \
    echo "[www]"; \
    echo "ping.path = /ping"; \
    } > /usr/local/etc/php-fpm.d/zz-ping.conf

# Copy app code and built assets
COPY --from=php-deps /app /var/www/html
COPY --from=build-assets /app/public/build /var/www/html/public/build
# (manifest.json is inside /public/build already; no need to copy separately)

# Permissions for Laravel writable dirs
RUN mkdir -p storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

# Healthcheck hits php-fpm ping via fastcgi
HEALTHCHECK --interval=10s --timeout=3s --retries=10 CMD \
    SCRIPT_NAME=/ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000 || exit 1

CMD ["php-fpm", "-F"]
