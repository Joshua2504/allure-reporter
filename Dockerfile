FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    libzip-dev \
    && docker-php-ext-install zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*