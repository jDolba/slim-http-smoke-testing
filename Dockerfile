FROM php:8.0.15-cli-buster as base

COPY --from=composer /usr/bin/composer /usr/bin/composer

# install required tools
# git for computing diffs
# unzip to ommit composer zip packages corruption
RUN apt-get update && \
    apt-get install -y \
    unzip git \
    && apt-get clean

# install and enable xdebug for code coverage
RUN pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    echo xdebug.mode=coverage > /usr/local/etc/php/conf.d/xdebug.ini

WORKDIR /var/www/html
