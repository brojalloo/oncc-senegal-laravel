# 🌍 ONCC Sénégal - Déploiement

## 🚀 Options de déploiement

### 1. **Railway** (Recommandé - Gratuit)
```bash
# 1. Créer un compte sur railway.app
# 2. Connecter votre GitHub
# 3. Déployer automatiquement
```

### 2. **Heroku**
```bash
# Installation Heroku CLI puis :
heroku create oncc-senegal-app
git push heroku main
```

### 3. **DigitalOcean App Platform**
```bash
# Connecter directement depuis l'interface web
# Auto-détection Laravel
```

## 📋 Variables d'environnement requises

Pour le déploiement, configurez ces variables :

```env
APP_NAME="ONCC Sénégal"
APP_ENV=production
APP_KEY=base64:YOUR_KEY_HERE
APP_DEBUG=false
APP_URL=https://votre-domaine.com

DB_CONNECTION=sqlite
DB_DATABASE=/app/database/database.sqlite

SESSION_DRIVER=database
SESSION_ENCRYPT=true
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

## 🔧 Configuration automatique

Le site est déjà configuré pour :
- ✅ Création automatique de la base de données
- ✅ Migration et seeders automatiques
- ✅ Optimisation des performances
- ✅ Gestion des erreurs en production
- ✅ Logging des activités

## 📊 Données incluses

- 13 régions du Sénégal
- 4 comptes utilisateurs de test
- 1,560 données climatiques
- 3,612 données économiques
- Alertes climatiques par région

## 🔒 Sécurité

- Middlewares de protection
- Rate limiting
- Headers de sécurité
- Sessions chiffrées
- Validation CSRF

---

**Prêt pour la production !** 🚀