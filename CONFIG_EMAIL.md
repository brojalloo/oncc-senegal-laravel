# Configuration de l'envoi d'emails - ONCC Sénégal

## ✅ Système actuel

Le système d'envoi d'emails est maintenant **entièrement implémenté** ! Les emails sont créés avec :

- ✉️ Email de vérification lors de l'inscription
- 🔐 Email de réinitialisation de mot de passe
- 🎨 Templates HTML professionnels
- 🔗 Liens de vérification sécurisés

## 📧 Configuration pour l'envoi réel d'emails

### Option 1 : Gmail (Recommandé pour le développement)

1. **Activer l'authentification à 2 facteurs sur votre compte Gmail**
   - Aller sur https://myaccount.google.com/security
   - Activer la validation en 2 étapes

2. **Créer un mot de passe d'application**
   - Aller sur https://myaccount.google.com/apppasswords
   - Sélectionner "Autre" comme application
   - Copier le mot de passe généré (16 caractères)

3. **Configurer le fichier `.env`** :
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=votre_email@gmail.com
   MAIL_PASSWORD=votre_mot_de_passe_application
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=votre_email@gmail.com
   MAIL_FROM_NAME="ONCC Sénégal"
   ```

4. **Redémarrer le serveur Laravel**
   ```bash
   # Arrêter le serveur (Ctrl+C)
   php artisan config:clear
   php artisan serve
   ```

### Option 2 : Mailtrap (Pour les tests)

Mailtrap est parfait pour tester sans envoyer de vrais emails.

1. **Créer un compte sur https://mailtrap.io**

2. **Récupérer vos identifiants SMTP**

3. **Configurer le fichier `.env`** :
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=sandbox.smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=votre_username_mailtrap
   MAIL_PASSWORD=votre_password_mailtrap
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=noreply@oncc-senegal.sn
   MAIL_FROM_NAME="ONCC Sénégal"
   ```

### Option 3 : Mode Log (Actuel)

**C'est le mode actuel !** Les emails ne sont pas envoyés mais enregistrés dans les logs.

```env
MAIL_MAILER=log
```

Pour voir les emails :
```bash
# Lire le fichier de log
Get-Content storage/logs/laravel.log -Tail 100
```

## 🧪 Tester l'envoi d'emails

### 1. Créer un nouveau compte

1. Aller sur http://localhost:8000/register
2. Remplir le formulaire d'inscription
3. Cliquer sur "S'inscrire"

### 2. Vérifier l'email

**Si vous utilisez le mode Log** :
```bash
# Voir le dernier email dans les logs
Get-Content storage/logs/laravel.log -Tail 50 | Select-String -Pattern "verification"
```

**Si vous utilisez Gmail ou Mailtrap** :
- Vérifier votre boîte mail
- Cliquer sur le lien de vérification

### 3. Le lien de vérification

Le lien ressemble à :
```
http://localhost:8000/verify-email/abc123def456...
```

Cliquer dessus activera automatiquement le compte !

## 📝 Fonctionnement

### Inscription :
1. L'utilisateur remplit le formulaire
2. Un compte est créé avec `statut = 'inactif'` et `email_verified = false`
3. Un token de vérification unique est généré
4. Un email est envoyé avec un lien contenant ce token
5. Le lien est valide 24 heures

### Vérification :
1. L'utilisateur clique sur le lien dans l'email
2. Laravel vérifie le token
3. Le compte passe à `statut = 'actif'` et `email_verified = true`
4. L'utilisateur peut maintenant se connecter

### Réinitialisation de mot de passe :
1. Cliquer sur "Mot de passe oublié"
2. Entrer l'email
3. Recevoir un email avec un lien (valide 1 heure)
4. Créer un nouveau mot de passe

## 🔧 Commandes utiles

```bash
# Voir les logs en temps réel
Get-Content storage/logs/laravel.log -Wait -Tail 10

# Nettoyer le cache de configuration
php artisan config:clear

# Activer manuellement tous les comptes (pour le développement)
php activate-users.php

# Tester l'envoi d'email via Tinker
php artisan tinker
>>> $user = App\Models\User::first();
>>> Mail::to($user->email)->send(new App\Mail\VerifyEmail($user));
```

## ⚠️ Dépannage

### Erreur "Connection refused"
- Vérifier que le serveur SMTP est correct
- Vérifier le port (587 pour TLS, 465 pour SSL)

### Erreur "Authentication failed"
- Vérifier le username et password
- Pour Gmail, utiliser un mot de passe d'application

### Email non reçu
- Vérifier les spams
- Vérifier que MAIL_FROM_ADDRESS est configuré
- Utiliser Mailtrap pour les tests

## 🎯 Recommandation

**Pour le développement** : Utiliser Mailtrap
**Pour la production** : Utiliser un service professionnel comme :
- SendGrid
- Mailgun
- Amazon SES
- Brevo (ex-Sendinblue, français)

Ces services offrent de meilleures garanties de délivrabilité.

---

**✨ Félicitations ! Votre système d'email est maintenant professionnel et sécurisé !**
