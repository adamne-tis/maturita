FROM php:8.5-fpm-alpine
RUN docker-php-ext-install -j$(nproc) mysqli
