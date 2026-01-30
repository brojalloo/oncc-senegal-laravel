# 🌍 ONCC Sénégal - Observatoire National sur les Changements Climatiques

![Laravel](https://img.shields.io/badge/Laravel-10.48-red?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2+-777bb4?style=for-the-badge&logo=php)
![SQLite](https://img.shields.io/badge/SQLite-003b57?style=for-the-badge&logo=sqlite)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

## 📋 À propos

L'**Observatoire National sur les Changements Climatiques du Sénégal (ONCC-SN)** est une plateforme web développée avec Laravel pour la collecte, l'analyse et la visualisation des données climatiques et économiques du Sénégal.

## ✨ Fonctionnalités

### 🔐 **Système d'authentification complet**
- Inscription/Connexion sécurisée
- Vérification d'email
- Réinitialisation de mot de passe
- Gestion des rôles utilisateurs (Admin, Chercheur, Collectivité, Public)

### 📊 **Gestion des données**
- **Données climatiques** : Température, précipitations, humidité, vent
- **Données économiques** : Impact économique par secteur
- **Validation des données** par les administrateurs
- **Alertes** météorologiques automatisées

### 🗺️ **Cartographie interactive**
- Visualisation géographique des données
- Intégration des 14 régions du Sénégal
- Cartes thématiques par indicateur

### 📈 **Tableau de bord et analyses**
- Statistiques en temps réel
- Graphiques de tendances
- Rapports automatisés
- Export des données

### ⚙️ **Administration**
- Gestion des utilisateurs
- Logs système
- Configuration email
- Validation des données

## 🚀 Installation

### Prérequis
- PHP 8.2+
- Composer
- Node.js & NPM
- SQLite

### Étapes d'installation

1. **Cloner le repository**
```bash
git clone https://github.com/brojalloo/oncc-senegal-laravel.git
cd oncc-senegal-laravel
```

2. **Installer les dépendances**
```bash
composer install
npm install
```

3. **Configuration environnement**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Base de données**
```bash
php artisan migrate
php artisan db:seed
```

5. **Compiler les assets**
```bash
npm run build
```

6. **Lancer le serveur**
```bash
php artisan serve
```

## 👥 Comptes de test

Après le seeding, vous pouvez utiliser ces comptes :

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| **Admin** | admin@oncc-sn.com | Admin@2026 |
| **Chercheur** | chercheur@oncc-sn.com | Chercheur@2026 |
| **Collectivité** | collectivite@oncc-sn.com | Collectivite@2026 |
| **Public** | public@oncc-sn.com | Public@2026 |

## 📁 Structure du projet

```
├── app/
│   ├── Http/Controllers/     # Contrôleurs
│   ├── Models/              # Modèles Eloquent
│   ├── Mail/                # Classes de mail
│   └── Http/Middleware/     # Middlewares
├── database/
│   ├── migrations/          # Migrations
│   └── seeders/            # Seeders
├── resources/
│   ├── views/              # Vues Blade
│   ├── css/                # Styles CSS
│   └── js/                 # Scripts JavaScript
└── public/
    ├── css/                # Assets CSS publics
    ├── js/                 # Assets JS publics
    └── img/                # Images
```

## 🌟 Technologies utilisées

- **Backend** : Laravel 10.48, PHP 8.2+
- **Base de données** : SQLite
- **Frontend** : Bootstrap 5.3, JavaScript ES6
- **Cartes** : Leaflet.js
- **Build** : Vite, TailwindCSS 4.0
- **Email** : Laravel Mail

## 📧 Configuration Email

Pour activer l'envoi d'emails, configurez dans `.env` :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-mot-de-passe-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=votre-email@gmail.com
MAIL_FROM_NAME="ONCC Sénégal"
```

## 🛡️ Sécurité

- Protection CSRF sur tous les formulaires
- Validation des données côté serveur
- Hachage sécurisé des mots de passe
- Middleware d'autorisation
- Logging des tentatives d'accès

## 📝 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 👨‍💻 Auteur

**ONCC Sénégal Development Team**
- GitHub: [@brojalloo](https://github.com/brojalloo)

## 🤝 Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. Fork le projet
2. Créez votre branche feature (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

## 📞 Support

Pour toute question ou support :
- Créer une [issue](https://github.com/brojalloo/oncc-senegal-laravel/issues)
- Email : support@oncc-senegal.org

---

<div align="center">
  <strong>🌍 Pour un Sénégal résilient face aux changements climatiques 🌱</strong>
</div>
