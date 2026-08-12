<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Comptes de démonstration, destinés au développement uniquement.
     *
     * @var list<array{email: string, nom: string, prenom: string, role: string, region: string, telephone: string}>
     */
    private const DEMO_ACCOUNTS = [
        [
            'email' => 'admin@oncc-sn.com',
            'nom' => 'Administrateur',
            'prenom' => 'Système',
            'role' => 'admin',
            'region' => 'Dakar',
            'telephone' => '771234567',
        ],
        [
            'email' => 'chercheur@oncc-sn.com',
            'nom' => 'Diallo',
            'prenom' => 'Amadou',
            'role' => 'chercheur',
            'region' => 'Dakar',
            'telephone' => '772345678',
        ],
        [
            'email' => 'collectivite@oncc-sn.com',
            'nom' => 'Seck',
            'prenom' => 'Fatou',
            'role' => 'collectivite',
            'region' => 'Thiès',
            'telephone' => '773456789',
        ],
        [
            'email' => 'public@oncc-sn.com',
            'nom' => 'Ndiaye',
            'prenom' => 'Moussa',
            'role' => 'public',
            'region' => 'Saint-Louis',
            'telephone' => '774567890',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ces comptes sont publics par nature : ils ne doivent jamais exister en
        // production. On interroge l'environnement applicatif plutôt que env(),
        // qui renvoie null dès que la configuration est mise en cache.
        if (app()->environment('production')) {
            $this->command?->warn('UserSeeder ignoré : les comptes de démonstration ne sont jamais créés en production.');

            return;
        }

        $password = config('seeding.password');
        $generated = blank($password);

        if ($generated) {
            $password = Str::password(16);
        }

        $hash = Hash::make($password);
        $now = Carbon::now();

        foreach (self::DEMO_ACCOUNTS as $account) {
            DB::table('users')->updateOrInsert(
                ['email' => $account['email']],
                $account + [
                    'password' => $hash,
                    'statut' => 'actif',
                    'email_verified_at' => $now,
                    'verification_token' => null,
                    'verification_token_expires' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $this->report($password, $generated);
    }

    /**
     * Affiche les identifiants créés, sans jamais les écrire dans le dépôt.
     */
    private function report(string $password, bool $generated): void
    {
        if (! $this->command) {
            return;
        }

        $count = count(self::DEMO_ACCOUNTS);
        $this->command->info("{$count} comptes de démonstration créés ou mis à jour.");

        $this->command->table(
            ['Email', 'Rôle'],
            array_map(fn (array $a) => [$a['email'], $a['role']], self::DEMO_ACCOUNTS)
        );

        $this->command->info("Mot de passe commun : {$password}");

        if ($generated) {
            $this->command->comment('Mot de passe généré aléatoirement. Définissez SEED_PASSWORD dans .env pour en fixer un.');
        }
    }
}
