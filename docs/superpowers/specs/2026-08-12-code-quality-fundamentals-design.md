# Fondations qualité de code & tests — Design

Date : 2026-08-12
Statut : Approuvé pour implémentation

## Contexte

Le projet ONCC Sénégal (Laravel 10) a une suite de tests quasi vide (uniquement les
stubs `ExampleTest` générés par défaut), trois scripts PHP ad hoc à la racine qui
bootstrapent Laravel manuellement en dehors d'Artisan, et un workflow CI
(`.github/workflows/ci.yml`, non commité) qui ne teste pas le bon environnement.

Ce travail fait suite à une correction de sécurité déjà effectuée dans une session
précédente : `database/database.sqlite` a été retiré du suivi git et purgé de tout
l'historique (force-push effectué) car il était commité dans un dépôt public.

Un chantier de refactoring plus large (extraction en `FormRequest`, couche service)
est explicitement hors scope ici pour ne pas entrer en conflit avec du travail en
cours dans les contrôleurs/vues. Ce sera une itération suivante (option B).

## 1. CI (`.github/workflows/ci.yml`)

Problème actuel : le job démarre un service MySQL qui n'est jamais utilisé
(`.env.example` force `DB_CONNECTION=sqlite`), et depuis que
`database/database.sqlite` n'est plus versionné, le job échouera faute de fichier.
Le step Pint tourne avec `|| true`, donc un style de code invalide ne fait jamais
échouer le build.

Changements :
- Retirer le service `mysql` (mort du code).
- Ajouter une étape `mkdir -p database && touch database/database.sqlite` avant
  `php artisan migrate --force`.
- Lancer `./vendor/bin/pint` une fois en amont pour reformater le code existant
  (commit séparé), puis configurer l'étape CI en `./vendor/bin/pint --test`
  (échoue si du code n'est pas formaté), sans `|| true`.
- `composer audit` et `npm audit` restent informatifs (`|| true`) — corriger les
  vulnérabilités de dépendances est un chantier séparé.

## 2. Scripts racine → commandes Artisan

Les fichiers `activate-users.php`, `check-admin.php`, `promote-to-admin.php` à la
racine du projet bootstrapent Laravel à la main, ne sont pas testables, et
`promote-to-admin.php` a un email codé en dur (`brotory50@gmail.com`).

Bug découvert dans `activate-users.php` : le script fait
`$user->update(['email_verified' => true, ...])`, mais la colonne réelle est
`email_verified_at` et `email_verified` n'est ni une colonne de la table `users`
ni dans `$fillable` du modèle `User`. Eloquent ignore silencieusement cette clé :
le script ne vérifie donc **jamais** réellement l'email de l'utilisateur, malgré
un message de succès affiché. La commande Artisan de remplacement corrige ce bug.

Nouvelles commandes (`app/Console/Commands/`) :

| Commande | Remplace | Comportement |
|---|---|---|
| `users:list-admins` | `check-admin.php` | Liste les utilisateurs avec `role = admin` ; si aucun, liste tous les utilisateurs (comportement de fallback conservé). |
| `users:activate-all` | `activate-users.php` | Met `email_verified_at = now()`, `statut = 'actif'`, vide les tokens de vérification, pour tous les utilisateurs non vérifiés. Demande confirmation interactive sauf si `--force` est passé. |
| `users:promote {email}` | `promote-to-admin.php` | Passe le `role` de l'utilisateur donné en argument à `admin`. Échoue proprement (exit code 1, message clair) si l'email n'existe pas. |

Une fois les commandes en place et testées, les 3 fichiers racine sont supprimés.

## 3. Suite de tests

Tests Feature (`tests/Feature/`) :
- `AuthTest` : inscription, connexion (succès, mauvais mot de passe, compte
  inactif, email non vérifié), vérification d'email, demande + application de
  reset de mot de passe, déconnexion.
- `AdminMiddlewareTest` : requête admin refusée si non authentifié (redirect
  login), refusée si authentifié mais rôle ≠ admin (403), refusée si admin mais
  `statut = inactif` (déconnexion + redirect), autorisée si admin actif.
- `RateLimitMiddlewareTest` : réponse 429 après dépassement de `maxAttempts`,
  compteur remis à zéro après expiration de la fenêtre (`decayMinutes`).
- `UserCommandsTest` : les 3 nouvelles commandes Artisan — exit code, sortie
  console, effet en base de données (y compris le cas `users:activate-all` qui
  vérifie que le bug de `email_verified_at` est bien corrigé).

Tests Unit (`tests/Unit/`) : uniquement si de la logique non triviale isolée est
identifiée pendant l'implémentation (ex. cast, accessor) ; pas de test unitaire
forcé artificiellement.

Les stubs `ExampleTest` (Feature et Unit) sont supprimés une fois remplacés par de
vrais tests.

## Hors scope

- Extraction des validations inline en classes `FormRequest`.
- Introduction d'une couche service/repository.
- Correction des vulnérabilités remontées par `composer audit` / `npm audit`.
- Persistance de la base SQLite en production (actuellement recréée à chaque
  déploiement via `docker-entrypoint.sh`) — comportement existant non modifié ici.
