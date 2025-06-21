FROM php:8.3-fpm

RUN docker-php-ext-install mysqli
RUN pecl install xdebug \
&& docker-php-ext-enable xdebug

ENV TZ=Europe/Berlin
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN printf '[PHP]\ndate.timezone = "Europe/Berlin"\n' > /usr/local/etc/php/conf.d/tzone.ini

WORKDIR /app