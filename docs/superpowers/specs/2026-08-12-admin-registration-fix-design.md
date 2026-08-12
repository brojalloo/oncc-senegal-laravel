# Correction de la faille d'auto-inscription admin — Design

Date : 2026-08-12
Statut : Approuvé pour implémentation

## Contexte

`AuthController::register()` valide le champ `role` du formulaire d'inscription public
avec la règle `'role' => 'required|in:admin,chercheur,collectivite,public'`. N'importe
quel visiteur peut donc s'inscrire directement avec `role=admin` et obtenir les
privilèges administrateur sans validation ni promotion par un administrateur existant.
Cette faille a été identifiée lors du travail sur `docs/superpowers/plans/2026-08-12-code-quality-fundamentals.md`
mais volontairement laissée hors scope de ce plan-là.

## Changement

Dans `app/Http/Controllers/AuthController.php::register()`, restreindre la règle de
validation du rôle aux trois rôles non-privilégiés :

```php
'role' => 'required|in:chercheur,collectivite,public',
```

Un envoi avec `role=admin` (ou toute autre valeur hors de cette liste) échoue la
validation Laravel standard (redirection avec erreurs de session), exactement comme un
rôle invalide aujourd'hui. Aucun utilisateur n'est créé dans ce cas.

La promotion en administrateur reste possible via la commande Artisan existante
`php artisan users:promote {email}` (ajoutée dans le plan précédent), qui n'est pas
touchée par ce changement.

## Test

Ajouter à `tests/Feature/AuthTest.php` un test vérifiant qu'une tentative d'inscription
avec `role=admin` échoue la validation et ne crée aucun utilisateur en base.

## Hors scope

- Retrait complet du champ rôle du formulaire d'inscription (option non retenue).
- Tout autre changement au flux d'authentification.
