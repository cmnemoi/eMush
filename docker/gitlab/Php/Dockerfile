FROM php:8.4-fpm

ARG HOST_IP=172.19.0.1

# Install system dependencies, PHP extensions, and clean up
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
    # Git and helpers
    git \
    curl \
    zip \
    unzip \
    # Build dependencies for PHP extensions
    build-essential \
    # Dev packages for extensions
    libpq-dev \
    libicu-dev \
    libzip-dev \
    libxml2-dev \
    libonig-dev \
    ; \
    # Install PHP extensions
    docker-php-ext-install -j"$(nproc)" \
    intl \
    pdo_pgsql \
    zip \
    xml \
    ; \
    # Opcache is bundled, just need to enable it
    docker-php-ext-enable opcache; \
    # Clean up build dependencies
    apt-get purge -y --auto-remove \
    build-essential \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    libxml2-dev \
    libonig-dev \
    ; \
    # Clean up apt cache
    rm -rf /var/lib/apt/lists/*

# Install Composer using the official multi-stage build method
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer