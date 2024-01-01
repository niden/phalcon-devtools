FROM composer:2.4.2 as composer
FROM php:8.0-fpm

COPY ./extra.ini /usr/local/etc/php/conf.d/

# Update
RUN apt update -y && \
    apt install -y \
        apt-utils \
        git \
        libpq-dev \
        libzip-dev \
        nano \
        sudo \
        wget \
        zip

# PECL Packages
RUN pecl install \
      phalcon \
      xdebug

RUN docker-php-ext-install \
        pdo_mysql \
        pdo_pgsql \
        zip

# Install PHP extensions
RUN docker-php-ext-enable \
        phalcon \
        xdebug

# Composer
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
# Bash script with helper aliases
COPY ./.bashrc /root/.bashrc

CMD ["php-fpm"]
