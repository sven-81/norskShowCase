FROM php:8.5-fpm

RUN apt-get update && apt-get install -y git unzip

RUN docker-php-ext-install mysqli
RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
