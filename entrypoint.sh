#!/bin/sh

git config --global --add safe.directory /var/www
cp .env.example .env
composer install
echo "Ожидание готовности MySQL..."
sleep 10
chmod -R 775 storage 
chown -R www-data:www-data storage 
php artisan migrate --force
php-fpm
