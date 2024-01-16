#
# Dockerfile for custom BNETDocs php container.
#
FROM php:8.2-fpm-alpine

RUN apk update && \
    apk add git && \
    git config --system --add safe.directory /var/www/html

RUN docker-php-ext-install pdo pdo_mysql && \
    docker-php-ext-enable pdo pdo_mysql
