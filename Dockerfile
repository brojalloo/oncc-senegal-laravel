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
    && chmod -R 777 storage bootstrap/cache database

# Create SQLite database file
RUN touch database/database.sqlite && chmod 666 database/database.sqlite

# Create a startup script inline to avoid CRLF issues
RUN echo '#!/bin/sh' > /start.sh && \
    echo 'cd /app' >> /start.sh && \
    echo 'echo "APP_NAME=ONCC" > .env' >> /start.sh && \
    echo 'echo "APP_ENV=production" >> .env' >> /start.sh && \
    echo 'echo "APP_KEY=${APP_KEY}" >> .env' >> /start.sh && \
    echo 'echo "APP_DEBUG=true" >> .env' >> /start.sh && \
    echo 'echo "DB_CONNECTION=sqlite" >> .env' >> /start.sh && \
    echo 'echo "DB_DATABASE=/app/database/database.sqlite" >> .env' >> /start.sh && \
    echo 'echo "SESSION_DRIVER=cookie" >> .env' >> /start.sh && \
    echo 'echo "CACHE_STORE=file" >> .env' >> /start.sh && \
    echo 'php artisan migrate --force 2>&1 || true' >> /start.sh && \
    echo 'php artisan db:seed --force 2>&1 || true' >> /start.sh && \
    echo 'exec php -S 0.0.0.0:${PORT} -t public' >> /start.sh && \
    chmod +x /start.sh

# Expose port
EXPOSE 8080

WORKDIR /app
CMD ["/start.sh"]
