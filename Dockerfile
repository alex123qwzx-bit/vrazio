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
RUN if [ ! -f .env ]; then \
        cp .env.example .env && \
        sed -i 's|^APP_KEY=.*|APP_KEY=base64:MsSubg1hnz426grHFbFVGOKiJRekcf+FQM2qSvDg2Qs=|' .env && \
        sed -i 's|^DB_CONNECTION=.*|DB_CONNECTION=pgsql|' .env && \
        sed -i 's|^DB_PORT=.*|DB_PORT=5432|' .env && \
        sed -i 's|^APP_DEBUG=.*|APP_DEBUG=false|' .env && \
        sed -i 's|^APP_ENV=.*|APP_ENV=production|' .env && \
        sed -i 's|^LOG_CHANNEL=.*|LOG_CHANNEL=stderr|' .env && \
        sed -i 's|^RAZORPAY_KEY=.*|RAZORPAY_KEY=rzp_test_TxIRu4ikTk6H9S|' .env && \
        sed -i 's|^RAZORPAY_SECRET=.*|RAZORPAY_SECRET=5AjwGkluZW7mcnOzQw3jQaXy|' .env; \
    fi && \
    sed -i 's|^RAZORPAY_KEY=.*|RAZORPAY_KEY=rzp_test_TxIRu4ikTk6H9S|' .env && \
    sed -i 's|^RAZORPAY_SECRET=.*|RAZORPAY_SECRET=5AjwGkluZW7mcnOzQw3jQaXy|' .env

# Restore production Vite build assets into public/build from public/css and public/js
RUN mkdir -p public/build/assets public/build/.vite \
    && cp -f public/css/app.css public/build/assets/app-Bvlct9fP.css \
    && cp -f public/js/app.js public/build/assets/app-B1y9mKpQ.js \
    && echo '{"resources/css/app.css":{"file":"assets/app-Bvlct9fP.css","src":"resources/css/app.css","isEntry":true},"resources/js/app.js":{"file":"assets/app-B1y9mKpQ.js","name":"app","src":"resources/js/app.js","isEntry":true}}' > public/build/manifest.json \
    && cp -f public/build/manifest.json public/build/.vite/manifest.json

# Set permissions and storage link
RUN rm -f bootstrap/cache/*.php \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && rm -rf public/storage \
    && php artisan storage:link || true \
    && php artisan package:discover --ansi || true \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENV PORT=3000
EXPOSE 3000

# Start command: clear config cache, migrate/seed safely, and serve with native PHP router
CMD ["sh", "-c", "php artisan optimize:clear && (php artisan migrate --force && php artisan db:seed --force || true) && php -S 0.0.0.0:${PORT:-3000} server.php"]
