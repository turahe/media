ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-cli

# Install system dependencies and PHP extensions
RUN apt-get update && \
    apt-get install -y --no-install-recommends git unzip libonig-dev libmagickwand-dev libsqlite3-dev libpq-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo pdo_mysql pdo_pgsql pdo_sqlite mbstring gd && \
    pecl install imagick && \
    docker-php-ext-enable imagick && \
    rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# Dependencies are installed per Laravel version by the test matrix.
CMD ["composer", "test"]
