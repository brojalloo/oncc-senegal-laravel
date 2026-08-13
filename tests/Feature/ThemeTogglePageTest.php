<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeTogglePageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * La page de connexion ne porte pas la barre haute, donc pas le bouton.
     * Elle applique tout de même le thème : le réglage du système, ou le
     * choix mémorisé lors d'une visite précédente.
     */
    public function test_the_sign_in_page_follows_the_theme_without_offering_the_toggle(): void
    {
        $reponse = $this->get('/login');

        $reponse->assertOk();
        $reponse->assertSee('oncc-theme', escape: false);
        $reponse->assertDontSee('id="basculeTheme"', escape: false);
    }

    public function test_the_toggle_is_present_once_signed_in(): void
    {
        $utilisateur = User::factory()->create(['role' => 'chercheur', 'statut' => 'actif']);

        $reponse = $this->actingAs($utilisateur)->get('/dashboard');

        $reponse->assertOk();
        $reponse->assertSee('id="basculeTheme"', escape: false);
    }

    public function test_the_toggle_announces_its_state_to_assistive_technology(): void
    {
        $utilisateur = User::factory()->create(['role' => 'chercheur', 'statut' => 'actif']);

        $reponse = $this->actingAs($utilisateur)->get('/dashboard');

        // Sans libellé, le bouton n'annonce que ses deux icônes décoratives.
        $reponse->assertSee('aria-pressed', escape: false);
        $reponse->assertSee('aria-label="Passer au thème sombre"', escape: false);
    }
}
