FROM php:7.2.2-fpm-alpine

# Arguments defined in docker-compose.yml
ARG user
ARG uid

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN addgroup -g $uid -S $user && \
    adduser -u $uid -S $user -G $user

# Set working directory
WORKDIR /var/www

USER $user

# Clear cache
RUN rm -rf /tmp/*
