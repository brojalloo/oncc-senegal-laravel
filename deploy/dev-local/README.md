# Docker local sur Windows

Dans PowerShell, ouvre le dossier du dépôt sur la branche `dev` et démarre Docker Desktop en mode conteneurs Linux.

```powershell
.\deploy\dev-local\Sync-Dev.ps1
```

Ouvre [127.0.0.1:8080](http://127.0.0.1:8080). La commande télécharge la dernière image validée de `dev` et la démarre par empreinte. Installe la tâche ci-dessous pour récupérer automatiquement les nouvelles versions. Les conteneurs déjà installés redémarrent quand Docker Desktop redémarre, sauf arrêt manuel.

Si le paquet GHCR est privé, lance `docker --context desktop-linux login ghcr.io --username brojalloo` et utilise un jeton GitHub classique avec `read:packages` à l’invite du mot de passe. Tant que la première publication de `dev` n’a pas réussi, utilise la construction locale ci-dessous.

Si une ancienne instance ONCC utilise déjà le port 8080, arrête-la avec `docker --context desktop-linux compose -f docker-compose.yml stop` depuis son propre dossier avant de démarrer celle-ci.

## Synchronisation automatique de la branche dev

Lance une fois cette commande dans Windows PowerShell, depuis un dossier stable du dépôt :

```powershell
.\deploy\dev-local\Install-AutoSync.ps1
Get-ScheduledTaskInfo -TaskName ONCC-Senegal-Dev
```

La tâche tourne sous ton compte Windows à l'ouverture de session et vérifie GHCR toutes les cinq minutes pendant que ta session et Docker Desktop sont disponibles. Si Docker est arrêté, la vérification suivante reprend une fois Docker relancé. Une image est publiée après les tests et les scans Trivy sur `dev`.

Sur cet environnement local, inscris-toi avec une adresse fictive telle que `test.oncc@example.test` puis connecte-toi avec le même mot de passe. Les nouveaux comptes sont actifs dès l'inscription. Pour un compte créé avant cette modification et encore inactif, crée un compte de test avec une nouvelle adresse. Le rôle chercheur permet de tester les deux formulaires de saisie, après le chargement des régions ci-dessous.

## Développer avec Docker

Pour construire tes modifications locales, mets la synchronisation en pause pour éviter que l'image de GHCR remplace ton build. Réactive-la quand tu veux reprendre les livraisons automatiques.

```powershell
Disable-ScheduledTask -TaskName ONCC-Senegal-Dev
.\deploy\dev-local\Sync-Dev.ps1 -Build
```

Pour revenir à la livraison automatique, exécute `Enable-ScheduledTask -TaskName ONCC-Senegal-Dev`.

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
