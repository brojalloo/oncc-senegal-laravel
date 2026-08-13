<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RotateUserPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_changes_the_password(): void
    {
        $compte = User::factory()->create(['email' => 'cible@oncc-sn.com']);

        $this->artisan('users:rotate-password', ['email' => ['cible@oncc-sn.com']])
            ->expectsQuestion('Nouveau mot de passe', 'phrase-de-passe-longue')
            ->expectsQuestion('Confirmez le mot de passe', 'phrase-de-passe-longue')
            ->assertSuccessful();

        $this->assertTrue(Hash::check('phrase-de-passe-longue', $compte->fresh()->password));
    }

    /**
     * Le point central : un mot de passe divulgué a pu servir à ouvrir une
     * session, et cette session survivrait à un simple changement.
     */
    public function test_it_closes_open_sessions(): void
    {
        config(['session.driver' => 'database']);
        $compte = User::factory()->create(['email' => 'cible@oncc-sn.com']);

        DB::table('sessions')->insert([
            ['id' => 'session-intruse', 'user_id' => $compte->id, 'ip_address' => '203.0.113.4',
                'user_agent' => 'curl', 'payload' => '', 'last_activity' => time()],
            ['id' => 'session-tierce', 'user_id' => $compte->id + 999, 'ip_address' => '203.0.113.9',
                'user_agent' => 'curl', 'payload' => '', 'last_activity' => time()],
        ]);

        $this->artisan('users:rotate-password', ['email' => ['cible@oncc-sn.com']])
            ->expectsQuestion('Nouveau mot de passe', 'phrase-de-passe-longue')
            ->expectsQuestion('Confirmez le mot de passe', 'phrase-de-passe-longue')
            ->assertSuccessful();

        $this->assertDatabaseMissing('sessions', ['id' => 'session-intruse']);
        // Les sessions des autres comptes ne doivent pas être emportées.
        $this->assertDatabaseHas('sessions', ['id' => 'session-tierce']);
    }

    public function test_it_invalidates_the_remember_me_cookie(): void
    {
        $compte = User::factory()->create([
            'email' => 'cible@oncc-sn.com',
            'remember_token' => 'jeton-connu-de-lattaquant',
        ]);

        $this->artisan('users:rotate-password', ['email' => ['cible@oncc-sn.com']])
            ->expectsQuestion('Nouveau mot de passe', 'phrase-de-passe-longue')
            ->expectsQuestion('Confirmez le mot de passe', 'phrase-de-passe-longue')
            ->assertSuccessful();

        $this->assertNotSame('jeton-connu-de-lattaquant', $compte->fresh()->remember_token);
    }

    public function test_the_demo_flag_covers_every_seeded_account(): void
    {
        foreach (UserSeeder::DEMO_ACCOUNTS as $compte) {
            User::factory()->create(['email' => $compte['email']]);
        }

        $this->artisan('users:rotate-password', ['--demo' => true])
            ->expectsQuestion('Nouveau mot de passe', 'phrase-de-passe-longue')
            ->expectsQuestion('Confirmez le mot de passe', 'phrase-de-passe-longue')
            ->assertSuccessful();

        foreach (UserSeeder::DEMO_ACCOUNTS as $donnees) {
            $this->assertTrue(
                Hash::check('phrase-de-passe-longue', User::where('email', $donnees['email'])->first()->password),
                "Le compte {$donnees['email']} n'a pas été traité."
            );
        }
    }

    public function test_it_refuses_a_password_that_is_too_short(): void
    {
        $compte = User::factory()->create([
            'email' => 'cible@oncc-sn.com',
            'password' => Hash::make('ancien-mot-de-passe'),
        ]);

        $this->artisan('users:rotate-password', ['email' => ['cible@oncc-sn.com']])
            ->expectsQuestion('Nouveau mot de passe', 'court')
            ->assertFailed();

        $this->assertTrue(Hash::check('ancien-mot-de-passe', $compte->fresh()->password));
    }

    public function test_it_refuses_when_the_two_entries_differ(): void
    {
        $compte = User::factory()->create([
            'email' => 'cible@oncc-sn.com',
            'password' => Hash::make('ancien-mot-de-passe'),
        ]);

        $this->artisan('users:rotate-password', ['email' => ['cible@oncc-sn.com']])
            ->expectsQuestion('Nouveau mot de passe', 'phrase-de-passe-longue')
            ->expectsQuestion('Confirmez le mot de passe', 'phrase-de-passe-differente')
            ->assertFailed();

        $this->assertTrue(Hash::check('ancien-mot-de-passe', $compte->fresh()->password));
    }

    public function test_it_stops_before_touching_anything_when_an_account_is_unknown(): void
    {
        $connu = User::factory()->create([
            'email' => 'connu@oncc-sn.com',
            'password' => Hash::make('ancien-mot-de-passe'),
        ]);

        $this->artisan('users:rotate-password', [
            'email' => ['connu@oncc-sn.com', 'inconnu@oncc-sn.com'],
        ])->assertFailed();

        // Rien ne doit avoir changé : une rotation partielle laisserait
        // l'opérateur croire que tout est traité.
        $this->assertTrue(Hash::check('ancien-mot-de-passe', $connu->fresh()->password));
    }

    public function test_it_requires_a_target(): void
    {
        $this->artisan('users:rotate-password')->assertFailed();
    }
}
