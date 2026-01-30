# Security Policy

## Supported Versions

Nous supportons actuellement les versions suivantes avec des mises à jour de sécurité :

| Version | Supportée          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Signaler une Vulnérabilité

La sécurité de l'ONCC Sénégal est notre priorité. Si vous découvrez une vulnérabilité de sécurité, veuillez nous en informer de manière responsable.

### 📧 Contact Sécurité

**Email** : security@oncc-senegal.org

### 🔒 Processus de Signalement

1. **Ne publiez PAS** la vulnérabilité publiquement
2. Envoyez un email détaillé à security@oncc-senegal.org
3. Incluez une description complète du problème
4. Fournissez des étapes pour reproduire si possible

### 📋 Informations à Inclure

- Type de vulnérabilité (XSS, SQLi, CSRF, etc.)
- Localisation du problème (URL, fichier, ligne)
- Impact potentiel
- Étapes pour reproduire
- Proof of concept (si disponible)

### ⏱️ Délai de Réponse

- **Accusé de réception** : Sous 48h
- **Évaluation initiale** : Sous 5 jours ouvrables
- **Mise à jour de statut** : Chaque semaine
- **Correction** : Selon la criticité

### 🏆 Reconnaissance

Les chercheurs en sécurité qui signalent des vulnérabilités de manière responsable seront :

- Mentionnés dans les notes de version (si souhaité)
- Ajoutés à notre hall of fame sécurité
- Contactés pour coordonner la divulgation

### 🚨 Critères de Criticité

- **Critique** : Accès admin, exécution de code à distance
- **Élevé** : Accès aux données utilisateur, contournement d'auth
- **Moyen** : Divulgation d'informations limitée
- **Faible** : Problèmes de configuration mineurs

## Bonnes Pratiques Sécurité

### 🔐 Pour les Développeurs

- Toujours valider les entrées utilisateur
- Utiliser les requêtes préparées (Eloquent ORM)
- Implémenter la protection CSRF
- Chiffrer les données sensibles
- Suivre les guidelines OWASP

### 🛡️ Pour les Déployements

- Utiliser HTTPS en production
- Configurer les headers de sécurité
- Mettre à jour régulièrement les dépendances
- Monitorer les logs de sécurité
- Sauvegarder régulièrement

## Contact

Pour toute question liée à la sécurité :

- **Email** : security@oncc-senegal.org
- **Issues** : Uniquement pour les problèmes non-sensibles
- **GPG Key** : [Disponible sur demande]

---

**Merci d'aider à sécuriser l'ONCC Sénégal ! 🔒**