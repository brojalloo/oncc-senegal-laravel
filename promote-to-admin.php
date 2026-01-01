<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "========================================" . PHP_EOL;
echo "  PROMOTION EN ADMINISTRATEUR" . PHP_EOL;
echo "========================================" . PHP_EOL . PHP_EOL;

// Trouver l'utilisateur avec l'email brotory50@gmail.com
$user = \App\Models\User::where('email', 'brotory50@gmail.com')->first();

if (!$user) {
    echo "❌ Utilisateur non trouvé." . PHP_EOL;
    exit(1);
}

echo "Utilisateur trouvé:" . PHP_EOL;
echo "  - ID: " . $user->id . PHP_EOL;
echo "  - Nom: " . $user->nom . PHP_EOL;
echo "  - Email: " . $user->email . PHP_EOL;
echo "  - Rôle actuel: " . $user->role . PHP_EOL;
echo "  - Statut: " . $user->statut . PHP_EOL;
echo PHP_EOL;

// Changer le rôle en admin
$user->role = 'admin';
$user->save();

echo "✅ Rôle mis à jour avec succès !" . PHP_EOL;
echo "  - Nouveau rôle: " . $user->role . PHP_EOL;
echo PHP_EOL;

echo "========================================" . PHP_EOL;
echo "L'utilisateur " . $user->nom . " est maintenant ADMINISTRATEUR." . PHP_EOL;
echo "========================================" . PHP_EOL;
