#!/bin/sh
# Write environment variables to .env so Laravel can read them
printenv | grep -E "^(APP_|DB_|JWT_|CACHE_|SESSION_|QUEUE_|MAIL_|AWS_)" > /var/www/.env
mkdir -p /var/www/storage/logs
chmod -R 775 /var/www/storage
php artisan config:clear
php artisan serve --host=0.0.0.0 --port=8000
