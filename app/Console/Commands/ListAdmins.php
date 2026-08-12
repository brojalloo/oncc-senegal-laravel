<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListAdmins extends Command
{
    protected $signature = 'users:list-admins';

    protected $description = 'Liste les utilisateurs ayant le rôle administrateur';

    public function handle(): int
    {
        $admins = User::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            $this->warn('Aucun administrateur trouvé dans la base de données.');
            $this->line('');
            $this->info('Tous les utilisateurs :');

            $allUsers = User::all();

            if ($allUsers->isEmpty()) {
                $this->line('Aucun utilisateur trouvé.');

                return self::SUCCESS;
            }

            $this->table(
                ['ID', 'Nom', 'Email', 'Rôle', 'Statut'],
                $allUsers->map(fn (User $user) => [
                    $user->id, $user->nom, $user->email, $user->role, $user->statut,
                ])->all()
            );

            return self::SUCCESS;
        }

        $this->info("Nombre d'administrateurs : {$admins->count()}");

        $this->table(
            ['ID', 'Nom', 'Email', 'Rôle', 'Statut', 'Email vérifié', 'Créé le'],
            $admins->map(fn (User $admin) => [
                $admin->id,
                $admin->nom,
                $admin->email,
                $admin->role,
                $admin->statut,
                $admin->email_verified_at ? 'Oui' : 'Non',
                $admin->created_at->format('d/m/Y H:i'),
            ])->all()
        );

        return self::SUCCESS;
    }
}
