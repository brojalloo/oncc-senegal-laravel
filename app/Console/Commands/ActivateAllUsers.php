<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ActivateAllUsers extends Command
{
    protected $signature = 'users:activate-all {--force : Ne pas demander de confirmation}';

    protected $description = "Active tous les comptes utilisateurs dont l'email n'est pas encore vérifié";

    public function handle(): int
    {
        $users = User::whereNull('email_verified_at')->get();

        if ($users->isEmpty()) {
            $this->info("Aucun compte en attente d'activation.");

            return self::SUCCESS;
        }

        $this->table(
            ['Email', 'Nom', 'Statut'],
            $users->map(fn (User $user) => [$user->email, "{$user->nom} {$user->prenom}", $user->statut])->all()
        );

        if (! $this->option('force') && ! $this->confirm("Activer ces {$users->count()} compte(s) ?")) {
            $this->warn('Opération annulée.');

            return self::FAILURE;
        }

        foreach ($users as $user) {
            $user->update([
                'email_verified_at' => now(),
                'statut' => 'actif',
                'verification_token' => null,
                'verification_token_expires' => null,
            ]);

            $this->line("✓ Compte activé : {$user->email}");
        }

        $this->info('Tous les comptes ont été activés.');

        return self::SUCCESS;
    }
}
