<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Rotation d'un mot de passe compromis.
 *
 * Changer le mot de passe ne suffit pas : qui s'est déjà connecté avec
 * l'ancien garde une session ouverte, et un cookie « se souvenir de moi »
 * survit indépendamment du mot de passe. Cette commande coupe les trois.
 *
 * Le mot de passe ne peut pas être passé en argument : il resterait dans
 * l'historique du shell, ce qui annulerait l'intérêt de l'opération.
 */
class RotateUserPassword extends Command
{
    protected $signature = 'users:rotate-password
                            {email?* : Les adresses à traiter}
                            {--demo : Traiter les quatre comptes de démonstration}
                            {--generate : Tirer un mot de passe aléatoire au lieu de le saisir}';

    protected $description = 'Change le mot de passe de comptes et met fin à leurs sessions ouvertes';

    /** Longueur minimale : ces comptes sont partagés et l'un d'eux est administrateur. */
    private const LONGUEUR_MINIMALE = 12;

    public function handle(): int
    {
        $adresses = $this->adressesVisees();

        if ($adresses === []) {
            $this->error('Aucune adresse indiquée. Utilisez --demo ou passez une ou plusieurs adresses.');

            return self::FAILURE;
        }

        /** @var list<User> $comptes */
        $comptes = [];

        foreach ($adresses as $adresse) {
            $compte = User::where('email', $adresse)->first();

            if (! $compte) {
                $this->error("Compte introuvable : {$adresse}");

                return self::FAILURE;
            }

            $comptes[] = $compte;
        }

        $this->line('Comptes concernés :');
        foreach ($comptes as $compte) {
            $this->line("  · {$compte->email} ({$compte->role})");
        }
        $this->newLine();

        $genere = (bool) $this->option('generate');
        $motDePasse = $genere ? Str::password(20) : $this->demanderMotDePasse();

        if ($motDePasse === null) {
            $this->error('Opération annulée.');

            return self::FAILURE;
        }

        $sessionsCoupees = 0;

        foreach ($comptes as $compte) {
            $compte->password = $motDePasse;
            // Invalide les cookies « se souvenir de moi » déjà distribués.
            $compte->remember_token = Str::random(60);
            $compte->save();

            $sessionsCoupees += $this->fermerLesSessions($compte);
        }

        $this->newLine();
        $this->info(sprintf(
            '%d compte(s) mis à jour, %d session(s) ouverte(s) fermée(s).',
            count($comptes),
            $sessionsCoupees
        ));

        if ($genere) {
            $this->newLine();
            $this->warn('Mot de passe tiré au sort — il ne sera pas réaffiché :');
            $this->line('  '.$motDePasse);
            $this->newLine();
            $this->warn('Notez-le maintenant, dans un gestionnaire de mots de passe.');
        }

        return self::SUCCESS;
    }

    /** @return list<string> */
    private function adressesVisees(): array
    {
        if ($this->option('demo')) {
            // Lu depuis le seeder pour que les deux listes ne divergent pas.
            return array_map(
                static fn (array $compte): string => $compte['email'],
                UserSeeder::DEMO_ACCOUNTS
            );
        }

        return array_values(array_filter((array) $this->argument('email')));
    }

    private function demanderMotDePasse(): ?string
    {
        if (! $this->input->isInteractive()) {
            $this->error(
                'Sans terminal interactif, le mot de passe ne peut pas être saisi sans laisser '.
                'de trace. Utilisez --generate.'
            );

            return null;
        }

        $motDePasse = (string) $this->secret('Nouveau mot de passe');

        if (mb_strlen($motDePasse) < self::LONGUEUR_MINIMALE) {
            $this->error(sprintf(
                'Trop court : %d caractères au minimum.',
                self::LONGUEUR_MINIMALE
            ));

            return null;
        }

        if ($motDePasse !== (string) $this->secret('Confirmez le mot de passe')) {
            $this->error('Les deux saisies diffèrent.');

            return null;
        }

        return $motDePasse;
    }

    /**
     * Ferme les sessions ouvertes du compte.
     *
     * Ne concerne que le pilote « database » : avec le pilote fichier, les
     * sessions ne portent pas d'identifiant d'utilisateur exploitable ici.
     */
    private function fermerLesSessions(User $compte): int
    {
        if (config('session.driver') !== 'database' || ! Schema::hasTable('sessions')) {
            return 0;
        }

        return DB::table('sessions')->where('user_id', $compte->id)->delete();
    }
}
