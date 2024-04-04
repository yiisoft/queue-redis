FROM php:8.1-cli

RUN pecl install redis && docker-php-ext-install pcntl && docker-php-ext-enable redis pcntl
RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN rm  -rf /tmp/*

ENV XDEBUG_MODE=coverage

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /app
COPY composer.* .

ADD . /app
