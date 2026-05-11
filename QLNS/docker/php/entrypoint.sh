#!/bin/sh
set -e

cd /var/www/html

echo "Setting up Laravel..."

# Cài đặt PHP dependencies
echo "1. Running composer install..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Cấp lại quyền cho storage
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Đợi Database sẵn sàng và chạy Migrate
echo "2. Waiting for database and running migrations..."
RETRIES=30
until php artisan migrate --force; do
  echo "Migration failed or database not ready, retrying in 2 seconds..."
  RETRIES=$((RETRIES-1))
  if [ $RETRIES -eq 0 ]; then
    echo "Timed out waiting for database"
    exit 1
  fi
  sleep 2
done

echo "3. Running database seeders..."
php artisan db:seed --force || echo "Seeders already run or failed, continuing..."

echo "4. Creating storage symlink..."
php artisan storage:link --force || echo "Storage link already exists or failed, continuing..."

echo "========================================="
echo "Laravel Environment Setup Completed!"
echo "Starting PHP-FPM..."
echo "========================================="

# Khởi chạy command mặc định (chạy máy chủ php-fpm)
exec "$@"
