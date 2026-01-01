<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$admins = \App\Models\User::where('role', 'admin')->get();

echo "Nombre d'administrateurs: " . $admins->count() . PHP_EOL;
echo "----------------------------------------" . PHP_EOL;

if ($admins->count() > 0) {
    foreach ($admins as $admin) {
        echo "ID: " . $admin->id . PHP_EOL;
        echo "Nom: " . $admin->nom . PHP_EOL;
        echo "Email: " . $admin->email . PHP_EOL;
        echo "Rôle: " . $admin->role . PHP_EOL;
        echo "Statut: " . $admin->statut . PHP_EOL;
        echo "Email vérifié: " . ($admin->email_verified ? 'Oui' : 'Non') . PHP_EOL;
        echo "Créé le: " . $admin->created_at->format('d/m/Y H:i') . PHP_EOL;
        echo "----------------------------------------" . PHP_EOL;
    }
} else {
    echo "Aucun administrateur trouvé dans la base de données." . PHP_EOL;
    echo PHP_EOL;
    echo "Tous les utilisateurs:" . PHP_EOL;
    $allUsers = \App\Models\User::all();
    foreach ($allUsers as $user) {
        echo "- ID: {$user->id} | Nom: {$user->nom} | Email: {$user->email} | Rôle: {$user->role} | Statut: {$user->statut}" . PHP_EOL;
    }
}
