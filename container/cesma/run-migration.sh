#! /bin/sh

# This script is used to run the migration script in the container
sleep 30 && php /var/www/html/artisan migrate --seed --force && php /var/www/html/artisan key:generate && php /var/www/html/artisan cache:clear && php /var/www/html/artisan config:clear && php /var/www/html/artisan view:clear && php artisan optimize
