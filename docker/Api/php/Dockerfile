FROM php:8.3-fpm

ARG HOST_IP=host.docker.internal
ARG UID=1000
ARG GID=1000
ARG PHP_VERSION=8.3

COPY php.ini /etc/php/${PHP_VERSION}/php.ini
COPY php.ini /usr/local/etc/php/php.ini
COPY php-fpm-pool.conf /etc/php/${PHP_VERSION}/pool.d/www.conf

# Install PHP extensions and setup
RUN apt-get -yq update && \
    # Build dependencies
    apt-get install -yq --no-install-recommends vim curl debconf subversion git apt-transport-https apt-utils \
    build-essential locales acl mailutils wget zip unzip \
    gnupg gnupg1 gnupg2 ffmpeg \
    # Postgre PDO and PHP intl
    libpq-dev libicu-dev && \
    docker-php-ext-configure intl && \
    docker-php-ext-install intl && \
    docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && \
    docker-php-ext-install pdo pdo_pgsql pgsql && \
    docker-php-ext-install opcache && \
    docker-php-ext-configure opcache --enable-opcache && \
    # Xdebug
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.idekey=\"PHPSTORM\"" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_host=$HOST_IP" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_log=\"/tmp/xdebug.log\"" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    # OpenTelemetry
    pecl install protobuf opentelemetry && \
    docker-php-ext-enable protobuf opentelemetry && \
    # Composer
    curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
    mv composer.phar /usr/local/bin/composer && \
    # Setup locales
    echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen && \
    # Cleanup
    rm -rf /var/lib/apt/lists/*
    
# Setup a non-root user
RUN groupadd dev -g $GID && useradd dev -u $UID -g dev -d /home/dev -m

WORKDIR /www/

CMD ["php-fpm"]
