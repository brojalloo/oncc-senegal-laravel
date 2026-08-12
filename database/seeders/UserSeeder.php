<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'email' => 'admin@oncc-sn.com',
                'password' => Hash::make('Admin@2026'),
                'nom' => 'Administrateur',
                'prenom' => 'Système',
                'role' => 'admin',
                'region' => 'Dakar',
                'telephone' => '771234567',
                'statut' => 'actif',
                'email_verified_at' => Carbon::now(),
                'verification_token' => null,
                'verification_token_expires' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'email' => 'chercheur@oncc-sn.com',
                'password' => Hash::make('Chercheur@2026'),
                'nom' => 'Diallo',
                'prenom' => 'Amadou',
                'role' => 'chercheur',
                'region' => 'Dakar',
                'telephone' => '772345678',
                'statut' => 'actif',
                'email_verified_at' => Carbon::now(),
                'verification_token' => null,
                'verification_token_expires' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'email' => 'collectivite@oncc-sn.com',
                'password' => Hash::make('Collectivite@2026'),
                'nom' => 'Seck',
                'prenom' => 'Fatou',
                'role' => 'collectivite',
                'region' => 'Thiès',
                'telephone' => '773456789',
                'statut' => 'actif',
                'email_verified_at' => Carbon::now(),
                'verification_token' => null,
                'verification_token_expires' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'email' => 'public@oncc-sn.com',
                'password' => Hash::make('Public@2026'),
                'nom' => 'Ndiaye',
                'prenom' => 'Moussa',
                'role' => 'public',
                'region' => 'Saint-Louis',
                'telephone' => '774567890',
                'statut' => 'actif',
                'email_verified_at' => Carbon::now(),
                'verification_token' => null,
                'verification_token_expires' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                $user
            );
        }

        $this->command->info('✅ 4 utilisateurs de test créés/mis à jour avec succès !');
        $this->command->info('');
        $this->command->info('📧 Identifiants de connexion :');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('👤 ADMIN:');
        $this->command->info('   Email: admin@oncc-sn.com');
        $this->command->info('   Mot de passe: Admin@2026');
        $this->command->info('');
        $this->command->info('👤 CHERCHEUR:');
        $this->command->info('   Email: chercheur@oncc-sn.com');
        $this->command->info('   Mot de passe: Chercheur@2026');
        $this->command->info('');
        $this->command->info('👤 COLLECTIVITÉ:');
        $this->command->info('   Email: collectivite@oncc-sn.com');
        $this->command->info('   Mot de passe: Collectivite@2026');
        $this->command->info('');
        $this->command->info('👤 PUBLIC:');
        $this->command->info('   Email: public@oncc-sn.com');
        $this->command->info('   Mot de passe: Public@2026');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
        $this->command->info('✅ Tous les comptes sont PRÉ-VÉRIFIÉS et ACTIFS !');
    }
}
