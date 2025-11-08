# Use PHP 8.2 FPM as base image - FORCE REBUILD 2025-11-08-15-30
FROM php:8.2-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    jpeg-dev \
    freetype-dev \
    libzip-dev \
    postgresql-dev \
    nodejs \
    npm

# Clear cache
RUN rm -rf /var/cache/apk/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mysqli \
    gd \
    zip \
    bcmath \
    opcache \
    mbstring \
    xml \
    ctype


# Configure PHP
RUN sed -i "s/memory_limit = 128M/memory_limit = 512M/" /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY --chown=www-data:www-data . /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Create environment file for deployment first
RUN cp .env.example .env && \
    sed -i 's/APP_URL=http:\/\/localhost/APP_URL=https:\/\/digital-claim-system.onrender.com/' .env && \
    echo "ASSET_URL=https://digital-claim-system.onrender.com" >> .env && \
    sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=pgsql/' .env && \
    echo "DB_HOST=db.surssccnzmejgdufnibl.supabase.co" >> .env && \
    echo "DB_PORT=5432" >> .env && \
    echo "DB_DATABASE=postgres" >> .env && \
    echo "DB_USERNAME=postgres" >> .env && \
    echo "DB_PASSWORD=DigitalClaimSystem@123" >> .env && \
    echo "DB_SSLMODE=require" >> .env && \
    echo "DB_CHARSET=utf8" >> .env && \
    echo "DB_HOST=db.surssccnzmejgdufnibl.supabase.co" >> .env

# Generate application key
RUN php artisan key:generate

# Install Node.js dependencies and build assets with correct URL
RUN npm install \
    && npm run build

# Expose port 8080
EXPOSE 8080

# Create startup script
RUN echo '#!/bin/sh' > /start.sh && \
    echo 'echo "Starting application..."' >> /start.sh && \
    echo 'echo "Running migrations..."' >> /start.sh && \
    echo 'php artisan migrate --force' >> /start.sh && \
    echo 'echo "Starting server..."' >> /start.sh && \
    echo 'php artisan serve --host=0.0.0.0 --port=8080' >> /start.sh && \
    chmod +x /start.sh

# Start the application
CMD ["/start.sh"]