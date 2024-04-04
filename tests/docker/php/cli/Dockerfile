FROM php:8.1-cli

MAINTAINER  A. Egorov <anat-gor@ya.ru>

RUN pecl install redis && docker-php-ext-enable redis intl pcntl xdebug
RUN rm  -rf /tmp/*

ENV XDEBUG_MODE=coverage

WORKDIR /app
COPY composer.* .
RUN composer install --prefer-dist --no-interaction
COPY /app/* .

ADD . /app
