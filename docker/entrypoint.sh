#!/bin/sh
set -e

# Démarrage du conteneur applicatif.
#
# Ce script ne crée jamais de fichier .env et ne sème jamais la base : toute la
# configuration provient des variables d'environnement fournies par
# l'hébergeur. Les comptes de démonstration n'ont rien à faire en production.

: "${PORT:=8080}"

echo "==> Configuration de nginx sur le port ${PORT}"
sed -i "s/__PORT__/${PORT}/" /etc/nginx/nginx.conf

if [ -z "${APP_KEY}" ]; then
    echo "!! APP_KEY est vide. Générez-en une avec 'php artisan key:generate --show'" >&2
    echo "!! puis renseignez-la dans les variables d'environnement de la plateforme." >&2
    exit 1
fi

# Répertoires exigés par Laravel, recréés si le volume est vierge.
mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "==> Migrations"
    # --force : exécution non interactive. Aucun seeding : voir plus haut.
    php artisan migrate --force --no-ansi
else
    echo "==> Migrations ignorées (RUN_MIGRATIONS=false)"
fi

echo "==> Mise en cache de la configuration"
php artisan config:cache --no-ansi
php artisan route:cache --no-ansi
php artisan view:cache --no-ansi

echo "==> Démarrage de nginx et php-fpm"
exec supervisord -c /etc/supervisord.conf
