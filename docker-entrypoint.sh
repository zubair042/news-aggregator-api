#!/bin/bash

cd /var/www/html

php artisan migrate
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan optimize:clear

# Start cron in the background
cron &

# Start php-fpm as the main process
exec php-fpm
