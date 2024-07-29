FROM php:8.3.0-alpine

ARG HOST_IP=172.19.0.1

# Install PHP extensions and setup
RUN apk --no-cache --update add --virtual .build-deps gcc make g++ \
    zlib-dev libmcrypt-dev libxml2-dev libzip-dev curl git zip \
    unzip oniguruma-dev autoconf \
    # Postgre PDO and PHP intl
    postgresql-dev icu-dev && \
    docker-php-ext-configure intl && \
    docker-php-ext-install intl && \
    docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && \
    docker-php-ext-install pdo pdo_pgsql pgsql && \
    # Composer
    curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
    mv composer.phar /usr/local/bin/composer && \
    # Cleanup
    rm -rf /var/cache/apk/*

CMD ["php-fpm"]
