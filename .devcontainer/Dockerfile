FROM mcr.microsoft.com/devcontainers/php:8.3

RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" intl zip gd bcmath \
    && printf 'short_open_tag=Off\n' > /usr/local/etc/php/conf.d/zz-short-open-tag.ini \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*
