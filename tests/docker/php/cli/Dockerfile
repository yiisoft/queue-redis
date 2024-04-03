FROM php:8.1-cli

MAINTAINER  A. Egorov <anat-gor@ya.ru>

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions


RUN install-php-extensions redis intl pcntl xdebug @composer
RUN rm  -rf /tmp/*

ENV XDEBUG_MODE=coverage

COPY composer.* /app/
WORKDIR /app
RUN composer install --prefer-dist --no-interaction

ADD . /app
