FROM php:8-fpm

#For mac and windows
#ARG HOST_IP=host.docker.internal
#For linux
ARG HOST_IP=172.19.0.1

RUN apt-get update \
    && apt-get install -y --no-install-recommends curl debconf subversion git apt-transport-https apt-utils \
    build-essential locales acl mailutils wget zip unzip \
    gnupg gnupg1 gnupg2 ffmpeg

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
   mv composer.phar /usr/local/bin/composer

# Install Postgre PDO && PHP intl
RUN apt-get -y update \
    && apt-get install -y libpq-dev libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

RUN rm -rf /var/lib/apt/lists/*
RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen

CMD ["php-fpm"]
