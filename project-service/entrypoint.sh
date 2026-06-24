#!/bin/sh
printenv | grep -E "^(APP_|DB_|JWT_|CACHE_|SESSION_|QUEUE_|MAIL_|AWS_|AUTH_|PROJECT_)" > /var/www/html/.env
mkdir -p /var/www/html/storage/logs
chmod -R 775 /var/www/html/storage
php artisan config:clear
php artisan serve --host=0.0.0.0 --port=8000
