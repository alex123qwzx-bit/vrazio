FROM php:8.3-cli-alpine

# Install system packages & PHP extensions
RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    git \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pdo_mysql \
        bcmath \
        gd \
        intl \
        zip \
        pcntl \
        opcache

# Copy Composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /workspace

# Copy dependencies manifest and install production packages
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --optimize-autoloader

# Copy application code
COPY . .
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Set permissions and storage link
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && rm -rf public/storage \
    && php artisan storage:link || true \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENV PORT=3000
EXPOSE 3000

# Start command: clear config cache, migrate, seed in background, and serve on port 3000
CMD ["sh", "-c", "php artisan optimize:clear && php artisan migrate --force && (php artisan db:seed --force &) && php artisan serve --host=0.0.0.0 --port=${PORT:-3000}"]
