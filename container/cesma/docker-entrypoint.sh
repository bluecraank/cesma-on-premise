#! /bin/sh

# Run scripts before starting the container
chmod +x /docker-entrypoint-init.d/*
. /docker-entrypoint-init.d/*

sh /docker-entrypoint-init.d/10-run-migration.sh

# Run php-fpm with nginx
php-fpm -D && nginx -g "daemon off;"
