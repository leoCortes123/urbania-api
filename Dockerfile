FROM php:8.5-fpm

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

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN chown -R www-data:www-data /var/www

EXPOSE 9000

CMD ["php-fpm"]
