FROM php:8.5-apache AS php-base
RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev libonig-dev libicu-dev libzip-dev unzip \
    && docker-php-ext-install pdo_pgsql bcmath mbstring intl zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /var/www/html

FROM php-base AS dependencies
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction --optimize-autoloader

FROM node:22-alpine AS frontend
WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM php-base AS runtime
ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr PORT=10000
COPY . .
COPY --from=dependencies /var/www/html/vendor ./vendor
COPY --from=frontend /build/public/build ./public/build
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
RUN printf 'Listen 10000\n' > /etc/apache2/ports.conf \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
EXPOSE 10000
CMD ["apache2-foreground"]
