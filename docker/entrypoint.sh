#!/bin/bash

set -e

# Migrate and seed database

composer install --no-interaction --optimize-autoloader

php "/var/www/html/artisan" migrate --seed --force

#php "/var/www/html/artisan" vendor:publish --tag=filament-tables-views --force
# Refresh caches
php "/var/www/html/artisan" optimize:clear
php "/var/www/html/artisan" optimize
#php "/var/www/html/artisan" icon:cache
#php "/var/www/html/artisan" filament:cache-components

# Create storage symlinks
php "/var/www/html/artisan" storage:link

# Start Supervisor
#exec supervisord -c /etc/supervisor/supervisord.conf
exec php-fpm
