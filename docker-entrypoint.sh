#!/bin/sh
set -e

echo "Waiting for PostgreSQL..."
until pg_isready -h db -p 5432 -U urbania; do
  sleep 1
done

echo "PostgreSQL is ready. Running migrations..."
php artisan migrate --force

echo "Starting PHP-FPM..."
exec php-fpm
