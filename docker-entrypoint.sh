#!/bin/sh
set -e

echo "Waiting for PostgreSQL..."
until pg_isready -h db -p 5432 -U urbania; do
  sleep 1
done

echo "PostgreSQL is ready. Running migrations..."
php artisan migrate --force

# Generate JWT RSA keys if they don't exist
if [ ! -f /var/www/storage/jwt/private.pem ] || [ ! -f /var/www/storage/jwt/public.pem ]; then
  echo "Generating JWT RSA keys..."
  php artisan jwt:generate
fi

# Ensure PHP-FPM has OPENSSL_CONF environment variable (idempotent)
if ! grep -q 'env\[OPENSSL_CONF\]' /usr/local/etc/php-fpm.d/zz-docker.conf 2>/dev/null; then
  echo "env[OPENSSL_CONF] = /etc/ssl/openssl.cnf" >> /usr/local/etc/php-fpm.d/zz-docker.conf
fi

echo "Starting PHP-FPM..."
exec php-fpm
