# 🤝 Guide de Contribution - ONCC Sénégal

Merci de votre intérêt à contribuer au projet ONCC Sénégal ! Ce guide vous aidera à commencer.

## 🚀 Comment contribuer

### 1. **Fork le projet**
```bash
# Cliquez sur "Fork" sur GitHub
# Clonez votre fork
git clone https://github.com/VOTRE-USERNAME/oncc-senegal-laravel.git
cd oncc-senegal-laravel
```

### 2. **Configuration du projet**
```bash
# Installer les dépendances
composer install
npm install

# Configuration environnement
cp .env.example .env
php artisan key:generate

# Base de données
php artisan migrate
php artisan db:seed

# Compiler les assets
npm run build
```

### 3. **Créer une branche**
```bash
# Créez une branche pour votre feature
git checkout -b feature/ma-nouvelle-fonctionnalite

# Ou pour un bugfix
git checkout -b fix/correction-probleme
```

### 4. **Développer votre contribution**
- 📝 Écrivez du code propre et bien documenté
- 🧪 Ajoutez des tests si nécessaire
- 🎯 Suivez les conventions Laravel
- 📱 Assurez-vous de la responsivité

### 5. **Tester votre code**
```bash
# Tests unitaires
php artisan test

# Vérifications de style
php artisan pint

# Tests frontend
npm run test
```

### 6. **Commiter vos changements**
```bash
# Ajoutez vos fichiers
git add .

# Commit avec un message descriptif
git commit -m "✨ Ajout: Nouvelle fonctionnalité de cartographie interactive

- Implémentation des cartes Leaflet
- Ajout des marqueurs par région
- Interface responsive
- Tests unitaires inclus"
```

### 7. **Pousser et créer une Pull Request**
```bash
# Poussez votre branche
git push origin feature/ma-nouvelle-fonctionnalite

# Créez une Pull Request sur GitHub
```

## 📋 Types de contributions

### 🐛 **Corrections de bugs**
- Identifiez le problème
- Créez une issue si elle n'existe pas
- Proposez une solution avec tests

### ✨ **Nouvelles fonctionnalités**
- Discutez d'abord dans une issue
- Suivez les spécifications existantes
- Documentez votre code

### 📚 **Documentation**
- Améliorez le README
- Commentez le code
- Créez des guides utilisateur

### 🎨 **Interface utilisateur**
- Respectez le design existant
- Assurez-vous de la responsivité
- Testez sur différents navigateurs

## 🎯 Standards de code

### **PHP/Laravel**
```php
// Utilisez les conventions Laravel
class MonController extends Controller
{
    /**
     * Méthode bien documentée
     */
    public function maMethode(Request $request): JsonResponse
    {
        // Code propre et lisible
    }
}
```

### **Frontend**
```javascript
// Code JavaScript moderne
const maFonction = async () => {
    // Utilisez des noms explicites
    const donneesClimatiques = await fetch('/api/climate-data');
    return donneesClimatiques.json();
};
```

### **CSS**
```css
/* Utilisez des classes descriptives */
.climate-chart-container {
    padding: 1rem;
    border-radius: 0.5rem;
}
```

## 📝 Messages de commit

Utilisez le format suivant :

```
🎯 Type: Titre concis (max 50 caractères)

Description détaillée si nécessaire :
- Point 1
- Point 2
- Point 3

Fixes #123
```

### **Types de commit :**
- ✨ `Ajout`: Nouvelle fonctionnalité
- 🐛 `Fix`: Correction de bug
- 📚 `Doc`: Documentation
- 🎨 `Style`: Formatage, style
- ♻️ `Refactor`: Refactorisation
- 🧪 `Test`: Tests
- 🔧 `Config`: Configuration

## 🧪 Tests

### **Tests PHP**
```bash
# Lancer tous les tests
php artisan test

# Tests spécifiques
php artisan test --filter AuthTest
```

### **Tests Frontend**
```bash
# Tests JavaScript
npm run test

# Tests E2E
npm run test:e2e
```

## 🔍 Code Review

Votre Pull Request sera reviewée selon :

### **Critères techniques**
- ✅ Code fonctionnel
- ✅ Tests passants
- ✅ Pas de régressions
- ✅ Performance optimisée

### **Critères qualité**
- ✅ Code lisible
- ✅ Documentation adéquate
- ✅ Respect des conventions
- ✅ Sécurité appropriée

## 🚫 À éviter

- ❌ Commits avec des fichiers sensibles (.env, clés)
- ❌ Code non testé
- ❌ Messages de commit vagues
- ❌ Modifications non liées mélangées
- ❌ Code dupliqué
- ❌ Violations de sécurité

## 💬 Communication

### **Issues**
- 🐛 **Bug Report** : Utilisez le template fourni
- 💡 **Feature Request** : Décrivez le besoin
- ❓ **Question** : Posez vos questions

### **Discussions**
- 💭 Utilisez les Discussions GitHub
- 🗣️ Participez aux reviews
- 🤝 Soyez respectueux et constructif

## 📞 Aide

Besoin d'aide ? Nous sommes là !

- 📋 **Issues** : https://github.com/brojalloo/oncc-senegal-laravel/issues
- 💬 **Discussions** : https://github.com/brojalloo/oncc-senegal-laravel/discussions
- 📧 **Email** : dev@oncc-senegal.org

## 🏆 Reconnaissance

Tous les contributeurs seront mentionnés dans :
- 📋 Le fichier CONTRIBUTORS.md
- 🏅 Les releases notes
- 💫 La section remerciements

---

**Merci de contribuer à l'ONCC Sénégal ! 🌍🌱**

Ensemble, construisons un outil robuste pour lutter contre les changements climatiques au Sénégal.