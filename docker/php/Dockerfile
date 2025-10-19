FROM php:8.4-fpm-alpine

RUN apk add --no-cache git curl libzip-dev icu-dev oniguruma-dev bash autoconf build-base postgresql-dev \
 && docker-php-ext-install intl mbstring zip pdo pdo_pgsql bcmath \
 && pecl install redis \
 && docker-php-ext-enable redis

WORKDIR /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-custom.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN addgroup -g 1000 app && adduser -G app -g app -s /bin/sh -D app \
 && chown -R app:app /var/www/html

USER app

# Production build steps (uncomment when needed)
# COPY composer.json composer.lock ./
# RUN composer install --no-dev --no-interaction --optimize-autoloader

CMD ["php-fpm", "-F"]
