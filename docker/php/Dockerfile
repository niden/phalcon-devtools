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

# Editing www.conf
RUN echo "#!/bin/sh\nexit 0" > /usr/sbin/policy-rc.d \
    && sed -i -e "s/^;clear_env = no$/clear_env = no/" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i -e "/listen = .*/c\listen = [::]:8000" /usr/local/etc/php-fpm.d/www.conf

WORKDIR /app

EXPOSE 8000

CMD ["php-fpm", "--nodaemonize", "--fpm-config=/usr/local/etc/php-fpm.d/www.conf"]
