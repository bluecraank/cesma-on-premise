FROM php:8.2-fpm-bullseye AS php-base

LABEL maintainer "Nils Fischer"

ARG NODE_VERSION=20

ENV DEBIAN_FRONTEND noninteractive
ENV TZ="Europe/Berlin"

# Set timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_$NODE_VERSION.x | bash - &&\
    apt-get install -y nodejs

# Update npm
RUN npm install -g npm

# Install composer
RUN curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer

RUN composer self-update

# Install dependencies
RUN apt-get install -y nginx \
    acl \
    # Composer
    # git
    unzip \
    p7zip-full

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Look at this links to see which extensions are supported: https://github.com/mlocati/docker-php-extension-installer#supported-php-extensions
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions zip pdo_mysql

# Clean
RUN apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*


COPY ./container/cesma/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

RUN mkdir -p /var/www/.npm && chown -R 33:33 "/var/www/.npm"

RUN mkdir /docker-entrypoint-init.d/
COPY ./container/cesma/fix-permissions.sh /docker-entrypoint-init.d/0-fix-permissions.sh
COPY ./container/cesma/run-migration.sh /docker-entrypoint-init.d/10-run-migration.sh

# PHP fpm listenes to port 9000
EXPOSE 9000

# Nginx
EXPOSE 80
EXPOSE 443



FROM php-base

RUN install-php-extensions ldap
RUN apt-get update && apt-get install -y fping 

# Clean
RUN apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*


RUN rm /etc/nginx/sites-enabled/default

COPY --chown=www-data:www-data . /var/www/html

COPY ./container/cesma/nginx.conf /etc/nginx/conf.d/default.conf
COPY ./container/cesma/ldap.conf /etc/ldap/ldap.conf

USER www-data

# Install dependencies and optimize autoloader
# RUN composer install --optimize-autoloader --no-dev
# TODO: Faker is a dev dependency
RUN composer install --optimize-autoloader
#RUN npm install
#RUN npm run build && rm -rf node_modules
RUN php artisan key:generate

USER root

# Start the php-fpm as daemon in the background and
# start nginx
ENTRYPOINT [ "docker-entrypoint" ]