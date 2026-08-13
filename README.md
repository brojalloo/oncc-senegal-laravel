# 🌍 ONCC Sénégal — Observatoire National sur les Changements Climatiques

![Laravel](https://img.shields.io/badge/Laravel-12.66-red?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2+-777bb4?style=for-the-badge&logo=php)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-336791?style=for-the-badge&logo=postgresql&logoColor=white)
![Tests](https://img.shields.io/badge/tests-118-1a6e42?style=for-the-badge)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

## 📋 À propos

L'**Observatoire National sur les Changements Climatiques du Sénégal (ONCC-SN)**
est une plateforme web développée avec Laravel pour la collecte, l'analyse et la
visualisation des données climatiques et économiques du Sénégal.

## ✨ Fonctionnalités

### 🔐 Authentification et rôles
- Inscription, connexion, vérification d'adresse et réinitialisation de mot de passe
- Quatre rôles : administrateur, chercheur, collectivité, public
- L'inscription publique ne permet pas de se créer un compte administrateur

### 📊 Données
- **Climatiques** : température, précipitations, humidité, vent
- **Économiques** : impact par secteur
- Saisie par les utilisateurs, puis validation ou rejet par un administrateur

### 🗺️ Cartographie
- Carte interactive Leaflet des **14 régions** du Sénégal
- Fond OpenStreetMap et CARTO

### 📈 Visualisation
- Graphiques Chart.js sur le tableau de bord et les pages de visualisation
- Statistiques agrégées par rôle, indicateur et secteur
- Alertes climatiques affichées par région

### ⚙️ Administration
- Gestion des comptes : rôle et statut, avec garde-fou contre le retrait du
  dernier administrateur
- Consultation des journaux applicatifs
- Envoi d'une infolettre, mise en file d'attente

> Ces fonctionnalités sont celles réellement implémentées. La génération de
> rapports planifiés, l'export de données et la création automatique d'alertes
> **n'existent pas** : les alertes proviennent des données de démonstration.

## 🚀 Installation locale

### Prérequis

- PHP 8.2 ou plus
- Composer
- Node.js et npm

SQLite suffit en développement — aucun serveur de base à installer.

### Étapes

```bash
git clone https://github.com/brojalloo/oncc-senegal-laravel.git
cd oncc-senegal-laravel

composer install
npm install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate
php artisan db:seed

npm run build
php artisan serve
```

Le site répond alors sur <http://localhost:8000>.

Pour développer avec rechargement à chaud des assets et suivi des journaux :

```bash
composer dev
```

## 👥 Comptes de démonstration

`php artisan db:seed` crée quatre comptes locaux, un par rôle :

| Rôle | Email |
|------|-------|
| **Admin** | admin@oncc-sn.com |
| **Chercheur** | chercheur@oncc-sn.com |
| **Collectivité** | collectivite@oncc-sn.com |
| **Public** | public@oncc-sn.com |

Ils partagent un mot de passe unique, **affiché dans la console à la fin du
seeding**. Par défaut il est généré aléatoirement à chaque exécution ; pour en
fixer un pendant le développement, renseignez `SEED_PASSWORD` dans votre `.env` :

```env
SEED_PASSWORD=un-mot-de-passe-de-developpement
```

> ⚠️ Ces comptes sont réservés au développement. Le seeder refuse de s'exécuter
> lorsque `APP_ENV=production`, y compris avec `--force`. Ne renseignez jamais
> `SEED_PASSWORD` sur un environnement déployé.

## 🔧 Commandes Artisan

| Commande | Effet |
|---|---|
| `php artisan users:list-admins` | Liste les comptes administrateurs |
| `php artisan users:promote {email}` | Promeut un compte au rôle administrateur |
| `php artisan users:activate-all [--force]` | Active les comptes en attente de vérification |
| `php artisan users:rotate-password [emails...] [--demo] [--generate]` | Change un mot de passe et ferme les sessions ouvertes du compte |

C'est par `users:promote` qu'on désigne le premier administrateur après un
déploiement, l'inscription publique ne le permettant pas.

### Rotation d'un mot de passe compromis

Changer le mot de passe ne suffit pas. Qui s'est déjà connecté avec l'ancien
garde une session ouverte, et un cookie « se souvenir de moi » survit
indépendamment du mot de passe. `users:rotate-password` coupe les trois :
nouveau mot de passe, jeton de persistance regénéré, sessions supprimées.

```bash
# Les quatre comptes de démonstration, en une saisie
php artisan users:rotate-password --demo

# Un compte précis
php artisan users:rotate-password admin@oncc-sn.com

# Sans terminal (déploiement, script) : le mot de passe est tiré au sort
# et affiché une seule fois
php artisan users:rotate-password --demo --generate
```

Le mot de passe ne peut pas être passé en argument : il resterait dans
l'historique du shell, ce qui annulerait l'intérêt de l'opération. La saisie
est masquée et confirmée, et douze caractères sont exigés.

## ✅ Tests

```bash
php artisan test          # 118 tests
./vendor/bin/pint --test  # style de code
```

Les tests s'exécutent sur une base SQLite en mémoire ; ils ne touchent ni votre
base locale ni votre `.env`.

Trois workflows tournent sur chaque proposition de modification : la suite de
tests, le style de code, et **la construction réelle de l'image conteneur**,
exécutée contre un PostgreSQL puis vérifiée sur dix-sept points (processus
supervisés, droits d'écriture des journaux, en-têtes de sécurité, limitation des
tentatives, consommation effective de la file d'attente, persistance des données
après redémarrage).

## 🚢 Déploiement

Le déploiement se fait par conteneur : nginx et PHP-FPM sur PostgreSQL, décrits
par le seul `Dockerfile` à la racine. Voir **[DEPLOYMENT.md](DEPLOYMENT.md)**
pour les variables d'environnement et la procédure.

```bash
docker compose up --build   # pile complète en local, PostgreSQL compris
```

> N'utilisez pas SQLite en production : le disque d'un conteneur est éphémère,
> la base disparaîtrait à chaque redéploiement.

## 📁 Structure du projet

```
├── app/
│   ├── Console/Commands/     # Commandes Artisan (gestion des comptes)
│   ├── Http/Controllers/     # Contrôleurs
│   ├── Http/Middleware/      # Rôle admin, en-têtes de sécurité, proxys
│   ├── Jobs/                 # Traitements en file d'attente
│   ├── Mail/                 # Courriels
│   ├── Models/               # Modèles Eloquent
│   ├── Providers/            # Fournisseurs de services
│   └── Support/              # Utilitaires (taille de base, lecture de journaux)
├── database/
│   ├── factories/            # Fabriques de test
│   ├── migrations/           # Migrations
│   └── seeders/              # Données de démonstration
├── docker/                   # nginx, PHP-FPM, supervisord, entrypoint
├── resources/views/          # Vues Blade, dont les pages d'erreur
├── tests/                    # 118 tests
└── public/                   # Racine web, assets compilés
```

## 🌟 Technologies

- **Backend** : Laravel 12.66, PHP 8.2+
- **Base de données** : PostgreSQL en production, SQLite en développement
- **Frontend** : Bootstrap 5.3, Chart.js, Leaflet
- **Build** : Vite, Tailwind CSS 4
- **Conteneur** : nginx, PHP-FPM, supervisord

## 📧 Configuration des courriels

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@example.com
MAIL_PASSWORD=votre-mot-de-passe-application
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=votre-email@example.com
MAIL_FROM_NAME="ONCC Sénégal"
```

Les courriels partent **en file d'attente**, pas pendant la requête. En
production, un worker `queue:work` tourne dans le conteneur ; sans lui, rien
n'est envoyé et les messages s'accumulent en base. En développement,
`MAIL_MAILER=log` écrit les messages dans `storage/logs/laravel.log`.

Détails dans [CONFIG_EMAIL.md](CONFIG_EMAIL.md).

## 🛡️ Sécurité

Mesures en place, chacune couverte par des tests :

- **Protection CSRF** sur tous les formulaires
- **Mots de passe** hachés avec bcrypt
- **Limitation des tentatives** : 5 requêtes par minute et par IP sur la
  connexion, l'inscription et la réinitialisation
- **En-têtes** : `Content-Security-Policy`, `X-Frame-Options`,
  `X-Content-Type-Options`, `Referrer-Policy`, et HSTS sur connexion chiffrée
- **Sessions chiffrées**, cookie `secure` en production
- **Protocole d'origine** rétabli derrière un répartiteur de charge
- **Journalisation** des tentatives d'accès non autorisées
- **Aucun identifiant** dans le dépôt : les comptes de démonstration reçoivent
  un mot de passe aléatoire et ne sont jamais créés en production

Limite connue : la politique de sécurité du contenu autorise encore
`'unsafe-inline'`, plusieurs vues comportant du script en ligne. Elle bloque
l'injection de scripts distants, l'encadrement en iframe et le détournement de
formulaire, mais pas un script injecté en ligne.

Pour signaler une vulnérabilité, voir [SECURITY.md](SECURITY.md).

## 📝 Licence

Ce projet est sous licence MIT. Voir [LICENSE](LICENSE).

## 🤝 Contribution

Voir [CONTRIBUTING.md](CONTRIBUTING.md). En résumé :

1. Créez une branche depuis `main`
2. Ajoutez des tests pour ce que vous changez
3. Vérifiez avec `php artisan test` et `./vendor/bin/pint`
4. Ouvrez une pull request — les trois workflows doivent passer

## 👨‍💻 Auteur

**ONCC Sénégal Development Team** — [@brojalloo](https://github.com/brojalloo)

## 📞 Support

- [Ouvrir une issue](https://github.com/brojalloo/oncc-senegal-laravel/issues)
- support@oncc-senegal.org

---

<div align="center">
  <strong>🌍 Pour un Sénégal résilient face aux changements climatiques 🌱</strong>
</div>
