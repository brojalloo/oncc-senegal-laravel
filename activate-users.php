<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

// Récupérer tous les utilisateurs
$users = User::all();

if ($users->isEmpty()) {
    echo "Aucun utilisateur trouvé.\n";
    exit;
}

echo "=== Liste des utilisateurs ===\n\n";
foreach ($users as $user) {
    echo "Email: {$user->email}\n";
    echo "Nom: {$user->nom} {$user->prenom}\n";
    echo "Statut: {$user->statut}\n";
    echo "Email vérifié: " . ($user->email_verified ? 'Oui' : 'Non') . "\n";
    echo "---\n";
}

echo "\n=== Activation de tous les comptes ===\n";

foreach ($users as $user) {
    if (!$user->email_verified) {
        $user->update([
            'email_verified' => true,
            'statut' => 'actif',
            'verification_token' => null,
            'verification_token_expires' => null,
        ]);
        echo "✓ Compte activé: {$user->email}\n";
    } else {
        echo "- Déjà actif: {$user->email}\n";
    }
}

echo "\nTous les comptes ont été activés ! Vous pouvez maintenant vous connecter.\n";
