# Déploiement — ONCC Sénégal

L'application se déploie par conteneur. Le `Dockerfile` à la racine est la
**seule** description du déploiement : il fonctionne sur toute plateforme
capable de lancer un conteneur et d'injecter un port (Railway, Render, Fly.io,
Scaleway, Clever Cloud, ou un VPS avec Docker).

Les configurations spécifiques à une plateforme (`railway.json`, `render.yaml`,
`nixpacks.toml`, `app.json`, `Procfile`) ont été retirées : elles se
contredisaient entre elles et rendaient impossible de savoir ce qui tournait
réellement en production.

## Ce que fait l'image

| Étape | Détail |
|---|---|
| Build | Dépendances PHP sans `--dev`, assets compilés par Vite, autoloader optimisé |
| Serveur | nginx en frontal, PHP-FPM derrière, supervisé par supervisord |
| Démarrage | Migrations, puis mise en cache de la configuration, des routes et des vues |
| Base | PostgreSQL, via variables d'environnement |

L'image **ne sème jamais de données** et **ne génère jamais de `.env`**. Toute
la configuration vient de l'environnement fourni par l'hébergeur.

## Variables d'environnement requises

```env
APP_NAME="ONCC Sénégal"
APP_ENV=production
APP_KEY=base64:...          # php artisan key:generate --show
APP_DEBUG=false
APP_URL=https://votre-domaine.example
APP_LOCALE=fr

DB_CONNECTION=pgsql
DB_HOST=...                 # fourni par la base managée
DB_PORT=5432
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=...
MAIL_FROM_NAME="ONCC Sénégal"
```

`PORT` est généralement injecté par la plateforme ; à défaut, l'image écoute
sur 8080.

Le conteneur refuse de démarrer si `APP_KEY` est vide, plutôt que de servir des
sessions et des données chiffrées avec une clé absente.

### Variables optionnelles

| Variable | Défaut | Effet |
|---|---|---|
| `RUN_MIGRATIONS` | `true` | Passez à `false` pour piloter les migrations depuis une étape de release dédiée |
| `SEED_PASSWORD` | — | Développement uniquement. Le seeder de démonstration refuse de s'exécuter en production |

## Base de données

Utilisez une base **PostgreSQL managée**, proposée par toutes les plateformes
citées. Le stockage local d'un conteneur est éphémère : une base SQLite posée
dans l'image disparaît à chaque redéploiement, avec tous les comptes et toutes
les données saisies.

SQLite reste le choix par défaut en développement local, où cette contrainte
n'existe pas.

## Essai local

```bash
php artisan key:generate --show   # copiez la valeur dans un .env à la racine
docker compose up --build
```

L'application répond sur <http://localhost:8080>, avec un PostgreSQL identique
à celui de production.

## Premier administrateur

Aucun compte n'est créé automatiquement. Après le premier déploiement,
inscrivez-vous par le formulaire public, puis promouvez ce compte :

```bash
php artisan users:promote votre-email@example.com
```

Exécutez cette commande dans le conteneur (`docker exec`, ou la console de la
plateforme). L'inscription publique ne permet pas de se créer directement un
compte administrateur.

## Ce qui n'est pas encore en place

Ces points relèvent de la phase 2 du diagnostic et ne sont **pas** couverts par
cette configuration :

- Limitation du nombre de tentatives de connexion
- En-têtes de sécurité HTTP (CSP, HSTS, X-Frame-Options)
- Redirection HTTPS forcée côté application
- Traitement des vulnérabilités remontées par `composer audit`
