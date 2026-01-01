# ONCC Sénégal - Application Laravel

Observatoire National sur les Changements Climatiques du Sénégal

## 📋 Description

Cette application Laravel permet de gérer et visualiser les données climatiques et économiques du Sénégal, avec un système de cartographie interactive et d'alertes.

## 🚀 Installation

### Prérequis

- PHP >= 8.2
- Composer
- MySQL/MariaDB
- Node.js & NPM (optionnel pour le frontend)

### Étapes d'installation

1. **Configuration de la base de données**

   Créez une base de données MySQL :
   ```sql
   CREATE DATABASE oncc_senegal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Configuration de l'environnement**

   Le fichier `.env` est déjà configuré. Modifiez si nécessaire :
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=oncc_senegal
   DB_USERNAME=root
   DB_PASSWORD=votre_mot_de_passe
   ```

3. **Installation des dépendances**

   ```bash
   cd laravel-app
   composer install
   ```

4. **Migration de la base de données**

   ```bash
   php artisan migrate
   ```

5. **Démarrage du serveur**

   ```bash
   php artisan serve
   ```

   L'application sera accessible sur `http://localhost:8000`

## 📊 Structure du projet

### Modèles Eloquent

- **User** : Gestion des utilisateurs avec rôles (admin, chercheur, collectivite, public)
- **Region** : Régions du Sénégal avec coordonnées GPS
- **DonneeClimatique** : Données climatiques (température, pluviométrie, etc.)
- **DonneeEconomique** : Données économiques par secteur
- **Rapport** : Rapports générés
- **Alerte** : Alertes climatiques

### Controllers

- **AuthController** : Authentification (login, register, password reset, email verification)
- **DashboardController** : Tableau de bord et visualisations
- **AdminController** : Gestion admin (validation données, utilisateurs)
- **DataController** : Ajout et gestion des données
- **UserController** : Gestion du profil utilisateur

### Routes principales

- `/login` - Connexion
- `/register` - Inscription
- `/dashboard` - Tableau de bord
- `/visualization/climate` - Visualisation climatique
- `/visualization/economic` - Visualisation économique
- `/cartography` - Cartographie
- `/data/climate/create` - Ajouter données climatiques
- `/data/economic/create` - Ajouter données économiques
- `/admin/validation` - Validation des données (admin)
- `/admin/users` - Gestion des utilisateurs (admin)

## 🔐 Système d'authentification

L'application utilise le système d'authentification natif de Laravel avec :

- ✅ Inscription avec vérification par email
- ✅ Connexion sécurisée
- ✅ Réinitialisation de mot de passe
- ✅ Gestion des rôles (admin, chercheur, collectivite, public)
- ✅ Middleware de protection des routes

## 📧 Configuration Email

Pour activer l'envoi d'emails, configurez dans `.env` :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre_email@gmail.com
MAIL_PASSWORD=votre_mot_de_passe
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=votre_email@gmail.com
MAIL_FROM_NAME="ONCC Sénégal"
```

## 👥 Rôles et Permissions

- **Public** : Consultation des données
- **Chercheur** : Ajout de données + consultation
- **Collectivité** : Ajout de données + consultation
- **Admin** : Toutes les permissions + validation des données + gestion des utilisateurs

## 🗄️ Base de données

Les migrations créent les tables suivantes :

1. `users` - Utilisateurs
2. `regions` - Régions du Sénégal
3. `donnees_climatiques` - Données climatiques
4. `donnees_economiques` - Données économiques
5. `rapports` - Rapports
6. `alertes` - Alertes climatiques
7. Tables système Laravel (sessions, cache, jobs, etc.)

## 🎨 Frontend

L'application utilise :

- Bootstrap 5.3
- Font Awesome 6.4
- Chart.js pour les graphiques
- Blade pour les templates

## 📝 Prochaines étapes

1. Créer les vues manquantes pour les données et l'admin
2. Implémenter l'envoi d'emails
3. Ajouter les graphiques et visualisations
4. Créer la cartographie interactive
5. Ajouter les tests unitaires

## 🛠️ Commandes utiles

```bash
# Créer un nouveau controller
php artisan make:controller NomController

# Créer un nouveau modèle avec migration
php artisan make:model NomModele -m

# Créer une nouvelle migration
php artisan make:migration nom_migration

# Rafraîchir la base de données
php artisan migrate:refresh

# Créer un seeder
php artisan make:seeder NomSeeder

# Nettoyer le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 📄 Licence

Ce projet est développé pour l'Observatoire National sur les Changements Climatiques du Sénégal.

## 👨‍💻 Auteur

Développé avec Laravel 12
