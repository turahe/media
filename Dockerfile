FROM php:8.4-cli

# Install system dependencies and PHP extensions
RUN apt-get update && \
    apt-get install -y git unzip libmagickwand-dev && \
    docker-php-ext-install pdo pdo_mysql mbstring && \
    pecl install imagick && \
    docker-php-ext-enable imagick

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# Install all dependencies (including dev)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Default command: run tests
CMD ["composer", "test"]