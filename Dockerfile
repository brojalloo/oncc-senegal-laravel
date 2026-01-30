FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libsqlite3-dev \
    zip \
    unzip \
    nodejs \
    npm \
    sqlite3 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (SQLite instead of MySQL)
RUN docker-php-ext-install pdo pdo_sqlite mbstring exif pcntl bcmath gd zip

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
    database \
    && chmod -R 775 storage bootstrap/cache database

# Create SQLite database file
RUN touch database/database.sqlite && chmod 664 database/database.sqlite

# Expose port (Railway uses $PORT)
EXPOSE 8080

# Start script that runs migrations and starts server
WORKDIR /app
CMD php artisan migrate --force && php artisan db:seed --force && php -S 0.0.0.0:${PORT:-8080} -t public public/index.php
