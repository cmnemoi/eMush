FROM php:8.3-fpm

ARG HOST_IP=172.19.0.1

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
    # Composer
    curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
    mv composer.phar /usr/local/bin/composer && \
    # Setup locales
    echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen && \
    # Cleanup
    rm -rf /var/lib/apt/lists/*

CMD ["php-fpm"]
