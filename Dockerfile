# API Gplace - PHP 8.1 (Laravel 9)
FROM php:8.1-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    xml \
    zip \
    bcmath \
    intl \
    gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/wait-for-mysql.php /usr/local/bin/wait-for-mysql.php

WORKDIR /var/www/html

EXPOSE 8000
# O bind mount do compose substitui a imagem: se no host ainda não há vendor, instala antes do artisan.
CMD ["sh", "-c", "rm -f /var/www/html/bootstrap/cache/config.php && php /usr/local/bin/wait-for-mysql.php && if [ ! -f /var/www/html/vendor/autoload.php ]; then COMPOSER_ALLOW_SUPERUSER=1 composer install --no-interaction --prefer-dist --no-progress; fi && php artisan config:clear && exec php artisan serve --host=0.0.0.0 --port=8000"]
