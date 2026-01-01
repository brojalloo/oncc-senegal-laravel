FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts --ignore-platform-reqs

# Copy all application files
COPY . .

# Run composer scripts after copying all files
RUN composer dump-autoload --optimize

# Install Node dependencies and build assets
RUN npm install && (npm run build || true)

# Create storage directories and set permissions
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Cache Laravel config
RUN php artisan config:clear || true
RUN php artisan view:clear || true

# Expose port (Railway uses PORT env variable)
EXPOSE 8080

# Use PHP built-in server directly to avoid Laravel ServeCommand type issue
WORKDIR /app/public
CMD php -S 0.0.0.0:${PORT:-8080} /app/public/index.php
