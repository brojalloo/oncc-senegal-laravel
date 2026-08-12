<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Le limiteur s'appuie sur le cache, partagé entre les tests.
        RateLimiter::clear('');
        $this->app['cache']->store()->flush();
    }

    public function test_repeated_failed_logins_are_eventually_blocked(): void
    {
        User::factory()->create([
            'email' => 'cible@test.sn',
            'password' => bcrypt('le-bon-mot-de-passe'),
        ]);

        $attempt = fn () => $this->post('/login', [
            'email' => 'cible@test.sn',
            'password' => 'mauvais-mot-de-passe',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $attempt()->assertStatus(302);
        }

        // La 6e tentative dépasse le quota et doit être refusée par le
        // limiteur, pas par le contrôleur.
        $attempt()->assertStatus(429);
    }

    public function test_throttling_does_not_block_a_correct_password_within_the_quota(): void
    {
        $user = User::factory()->create([
            'email' => 'legitime@test.sn',
            'password' => bcrypt('le-bon-mot-de-passe'),
        ]);

        $this->post('/login', [
            'email' => 'legitime@test.sn',
            'password' => 'mauvais-mot-de-passe',
        ])->assertStatus(302);

        $this->post('/login', [
            'email' => 'legitime@test.sn',
            'password' => 'le-bon-mot-de-passe',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_is_throttled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/register', [
                'email' => "inscrit{$i}@test.sn",
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'nom' => 'Sarr',
                'prenom' => 'Moussa',
                'role' => 'public',
            ])->assertStatus(302);
        }

        $this->post('/register', [
            'email' => 'inscrit-de-trop@test.sn',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nom' => 'Sarr',
            'prenom' => 'Moussa',
            'role' => 'public',
        ])->assertStatus(429);
    }

    public function test_password_reset_requests_are_throttled(): void
    {
        $request = fn () => $this->post('/forgot-password', ['email' => 'cible@test.sn']);

        for ($i = 0; $i < 5; $i++) {
            $request()->assertStatus(302);
        }

        $request()->assertStatus(429);
    }
}
