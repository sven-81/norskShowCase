FROM php:8.5-fpm

RUN docker-php-ext-install mysqli
RUN pecl install xdebug \
&& docker-php-ext-enable xdebug

# Match host user UID/GID so that generated files (logs etc.) are owned by the host user
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

ENV TZ=Europe/Berlin
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN printf '[PHP]\ndate.timezone = "Europe/Berlin"\n' > /usr/local/etc/php/conf.d/tzone.ini

WORKDIR /app