FROM php:8.5-cli

RUN apt-get update && apt-get install -y git unzip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer global require rector/rector --prefer-dist

WORKDIR /app
