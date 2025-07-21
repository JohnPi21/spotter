# Stage 1: PHP deps
FROM composer:2 AS php-deps
WORKDIR /app
COPY composer.json composer.lock ./

# Copy Laravel files
COPY . .

RUN composer install --no-dev --optimize-autoloader

# ===============================
# 1. Node build stage (Vue/Assets)
# ===============================
FROM node:20 AS build-assets

WORKDIR /app

# Copy Ziggy early
COPY --from=php-deps /app/vendor ./vendor

COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# ===============================
# 2. PHP + Laravel stage
# ===============================
FROM php:8.3-fpm

WORKDIR /var/www/html

# Install PHP deps
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis

# Copy app and build assets
COPY --from=php-deps /app /var/www/html
COPY --from=build-assets /app/public/build /var/www/html/public/build
COPY --from=build-assets /app/public/build/manifest.json /var/www/html/public/build/manifest.json

# Ensure proper permissions
RUN mkdir -p storage bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
