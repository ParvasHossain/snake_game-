#!/bin/sh

# Set correct storage and cache permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Clear cached configs and run migrations
php artisan config:clear
php artisan cache:clear
php artisan migrate --force

# Start application
php artisan serve --host=0.0.0.0 --port=8000