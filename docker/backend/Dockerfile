# Backend Dockerfile for Laravel (PHP-FPM)
# Based on php:fpm-alpine for a compact image.

FROM php:8.1-fpm-alpine

# Arguments
ARG COMPOSER_ALLOW_SUPERUSER=1
ARG APP_ENV=production

ENV APP_ENV=${APP_ENV}
ENV PATH="/root/.composer/vendor/bin:/usr/local/bin:$PATH"

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    bash \
    git \
    curl \
    icu-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    autoconf \
    build-base \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    openssl \
    ca-certificates \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql zip bcmath opcache intl gd

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copy composer files and install dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-progress || composer install --no-interaction

# Copy application source
COPY . .

# Set permissions for Laravel storage and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Optimize (optional)
RUN php artisan config:cache || true
RUN php artisan route:cache || true
RUN php artisan view:cache || true

EXPOSE 9000

# Use php-fpm as entry
CMD ["php-fpm"]
