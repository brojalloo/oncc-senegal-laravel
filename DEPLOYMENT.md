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
| File d'attente | Un worker `queue:work` supervisé dans le même conteneur |
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
SESSION_ENCRYPT=true
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

## Emails et file d'attente

Les emails de vérification d'adresse, de réinitialisation de mot de passe et
l'infolettre partent **en file d'attente**, pas pendant la requête HTTP. Un
worker `queue:work` tourne dans le conteneur aux côtés de nginx et PHP-FPM.

Conséquence à connaître : **si ce worker ne tourne pas, aucun email ne part** —
les messages s'accumulent en base sans erreur visible. Après un déploiement,
vérifiez que la table `jobs` ne grossit pas indéfiniment :

```bash
php artisan queue:monitor database   # alerte si la file s'allonge
php artisan queue:failed             # travaux en échec
```

L'infolettre est envoyée par lots de 100 destinataires ; un destinataire
refusé par le serveur SMTP est journalisé sans interrompre le reste du lot.

## Protections en place

- **Limitation des tentatives** : 5 requêtes par minute et par IP sur la
  connexion, l'inscription et la réinitialisation de mot de passe.
- **En-têtes de sécurité** : CSP, `X-Frame-Options`, `X-Content-Type-Options`,
  `Referrer-Policy` sur toutes les réponses ; HSTS dès que la connexion est
  chiffrée.
- **HTTPS forcé** hors développement local, pour que les URL générées ne
  retombent pas en clair derrière un répartiteur de charge.
- **Sessions chiffrées**, cookie `secure` à activer via `SESSION_SECURE_COOKIE`.

## Ce qui n'est pas encore en place

- **Trois avis de sécurité sur `laravel/framework`**, dont un de niveau élevé
  (injection CRLF dans la règle de validation `email`, utilisée par les
  formulaires de connexion, d'inscription et de réinitialisation). Ils ne sont
  corrigés qu'à partir de Laravel 12.60 : aucune version des lignes 10 ou 11
  n'apporte de correctif. Fermer ces avis suppose une montée de version majeure
  du framework.
- **La CSP autorise `'unsafe-inline'`** pour les scripts et les styles, neuf
  vues comportant encore du code en ligne. La politique bloque l'injection de
  scripts distants, l'encadrement en iframe et le détournement de formulaire,
  mais pas un script injecté en ligne.
- Optimisation des images et migration du framework vers Laravel 12 (phases
  suivantes).
