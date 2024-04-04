FROM php:8.1-cli

RUN apt-get update && apt-get install -y git zip unzip
RUN pecl install redis && docker-php-ext-install pcntl  && docker-php-ext-enable redis pcntl
RUN pecl install xdebug pcov && docker-php-ext-enable xdebug pcov
RUN rm  -rf /tmp/*

ENV XDEBUG_MODE=coverage

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /app
COPY composer.* .
RUN composer install --prefer-dist --no-interaction


ADD . /app
