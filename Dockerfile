# =============================================================================
# Stage 1: Dependencias (vendor)
# =============================================================================
FROM php:8.5-fpm AS vendor

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    libssl-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath zip

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json composer.lock ./
COPY --from=spiralscout/php-patches:8.5 /patches /patches

RUN composer install --no-dev --optimize-autoloader --no-interaction

# =============================================================================
# Stage 2: Desarrollo (con dev dependencies + Xdebug)
# =============================================================================
FROM vendor AS development

RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --optimize-autoloader --no-interaction

# =============================================================================
# Stage 3: Producción (imagen final optimizada)
# =============================================================================
FROM php:8.5-fpm AS production

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    postgresql-client \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath zip

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

COPY --from=vendor /var/www/vendor ./vendor

RUN chown -R www-data:www-data /var/www

EXPOSE 9000

CMD ["php-fpm"]
