# Docker local sur Windows

Dans PowerShell, ouvre le dossier du dépôt sur la branche `dev` et démarre Docker Desktop en mode conteneurs Linux.

```powershell
.\deploy\dev-local\Sync-Dev.ps1
```

Ouvre [127.0.0.1:8080](http://127.0.0.1:8080). La commande télécharge la dernière image validée de `dev` et la démarre par empreinte. Relance-la pour récupérer une nouvelle version. Les conteneurs déjà installés redémarrent quand Docker Desktop redémarre, sauf arrêt manuel.

Si le paquet GHCR est privé, lance `docker --context desktop-linux login ghcr.io --username brojalloo` et utilise un jeton GitHub classique avec `read:packages` à l’invite du mot de passe. Tant que la première publication de `dev` n’a pas réussi, utilise la construction locale ci-dessous.

Si une ancienne instance ONCC utilise déjà le port 8080, arrête-la avec `docker --context desktop-linux compose -f docker-compose.yml stop` depuis son propre dossier avant de démarrer celle-ci.

## Développer avec Docker

Après avoir modifié le code, reconstruis l’image depuis le dépôt.

```powershell
.\deploy\dev-local\Sync-Dev.ps1 -Build
```

Pour créer les comptes et les données de démonstration, exécute cette commande. Le seeder affiche les accès générés dans le terminal.

```powershell
docker --context desktop-linux compose --env-file deploy/dev-local/.env -f deploy/dev-local/compose.yaml exec app php artisan db:seed
```

Réserve cette commande aux données fictives. La relancer peut réinitialiser des comptes de démonstration.

Les volumes de cette instance sont dédiés au développement. La base et les fichiers restent en place lors d’une reconstruction. Conserve aussi `deploy/dev-local/.env`, qui contient la clé locale générée au premier démarrage. Les données d’une ancienne installation Compose vivent dans leurs volumes d’origine. Utilise une sauvegarde et une restauration pour les transférer.

Pour voir les journaux ou arrêter l’instance en conservant les volumes :

```powershell
docker --context desktop-linux compose --env-file deploy/dev-local/.env -f deploy/dev-local/compose.yaml logs --tail 100
docker --context desktop-linux compose --env-file deploy/dev-local/.env -f deploy/dev-local/compose.yaml stop
```

Les pull requests vers `dev` construisent et démarrent cette configuration dans GitHub Actions. Après validation d’un push sur `dev`, le workflow publie les images de développement sur GHCR. La mise en service sur un serveur reste une étape distincte.
