# syntax=docker/dockerfile:1

# Image portable : nginx + PHP-FPM, pilotée entièrement par variables
# d'environnement. Fonctionne sur toute plateforme sachant lancer un conteneur
# et injecter un PORT (Railway, Render, Fly.io, Scaleway, un VPS...).
#
# Aucune donnée n'est semée au démarrage et aucun .env n'est généré : la
# configuration vient exclusivement de l'environnement fourni par l'hébergeur.


# --------------------------------------------------------------------------
# 1. Dépendances PHP
# --------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader


# --------------------------------------------------------------------------
# 2. Assets front
# --------------------------------------------------------------------------
FROM node:18-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources
RUN npm run build


# --------------------------------------------------------------------------
# 3. Image d'exécution
# --------------------------------------------------------------------------
FROM php:8.2-fpm-alpine AS runtime

RUN apk add --no-cache \
        nginx \
        supervisor \
        postgresql-dev \
        libzip-dev \
        libpng-dev \
        icu-dev \
        oniguruma-dev \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pdo_mysql \
        mbstring \
        bcmath \
        exif \
        gd \
        zip \
        opcache \
    && apk del postgresql-dev libzip-dev libpng-dev icu-dev oniguruma-dev \
    && apk add --no-cache postgresql-libs libzip libpng icu-libs oniguruma

# OPcache : recommandé en production, sans revalidation puisque le code est figé
# dans l'image.
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=0'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.validate_timestamps=0'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

RUN { \
        echo 'expose_php=Off'; \
        echo 'memory_limit=256M'; \
        echo 'upload_max_filesize=16M'; \
        echo 'post_max_size=16M'; \
    } > /usr/local/etc/php/conf.d/app.ini

WORKDIR /app

COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY . .

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

# Composer a été lancé sans scripts (le code n'était pas encore copié) : on
# régénère l'autoloader et on laisse Laravel découvrir ses paquets.
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative \
    || true

RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && mkdir -p /var/lib/nginx/tmp /var/log/nginx \
    && chown -R www-data:www-data /var/lib/nginx /var/log/nginx

ENV PORT=8080
EXPOSE 8080

# Si nginx ou php-fpm finit par abandonner, supervisord garde le conteneur en
# vie mais celui-ci ne sert plus rien. Le healthcheck le rend visible pour que
# la plateforme redémarre le conteneur plutôt que de router vers un service
# mort.
HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1:".(getenv("PORT")?:"8080")."/login") === false ? 1 : 0);'

ENTRYPOINT ["entrypoint"]
