<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PromoteUserToAdmin extends Command
{
    protected $signature = "users:promote {email : L'email de l'utilisateur à promouvoir}";

    protected $description = 'Promeut un utilisateur au rôle administrateur';

    public function handle(): int
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("Utilisateur non trouvé : {$email}");

            return self::FAILURE;
        }

        $previousRole = $user->role;
        $user->update(['role' => 'admin']);

        $this->info("{$user->nom} {$user->prenom} ({$user->email}) est maintenant administrateur (rôle précédent : {$previousRole}).");

        return self::SUCCESS;
    }
}
