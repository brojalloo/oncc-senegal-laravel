<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mot de passe des comptes de démonstration
    |--------------------------------------------------------------------------
    |
    | Mot de passe attribué aux comptes créés par UserSeeder. Laissé vide, un
    | mot de passe aléatoire est généré à chaque exécution et affiché dans la
    | console. Aucun mot de passe ne doit être écrit en dur dans le dépôt : les
    | identifiants de démonstration ont déjà fuité une fois par ce biais.
    |
    | Ce seeder ne s'exécute jamais en production (voir UserSeeder::run).
    |
    */

    'password' => env('SEED_PASSWORD'),

];
