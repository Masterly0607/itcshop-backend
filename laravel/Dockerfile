FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Copy everything (but make sure to also include hidden files like .env)
COPY . ./

# 👇 Best practice: explicitly copy .env if needed
COPY .env .env
COPY .env.example .env.example

# Install system dependencies
RUN apt-get update && apt-get install -y \
    zip unzip curl libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP dependencies using Composer
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Start PHP-FPM
CMD ["php-fpm"]
