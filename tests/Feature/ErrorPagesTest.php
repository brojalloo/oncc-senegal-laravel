<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_unknown_url_renders_the_custom_404_page(): void
    {
        $response = $this->get('/une-adresse-qui-nexiste-pas');

        $response->assertNotFound();
        $response->assertSee('Page introuvable');
        $response->assertSee('Retour à l\'accueil', false);
    }

    public function test_a_forbidden_page_renders_the_custom_403_page(): void
    {
        $user = User::factory()->create(['role' => 'chercheur']);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertForbidden();
        $response->assertSee('Accès non autorisé');
    }

    #[DataProvider('errorViews')]
    public function test_each_error_view_renders_standalone(string $view, string $expected): void
    {
        // Les pages d'erreur ne doivent dépendre ni de la session ni de la
        // base : elles s'affichent précisément quand quelque chose est cassé.
        $html = view("errors.{$view}")->render();

        $this->assertStringContainsString($expected, $html);
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
    }

    public static function errorViews(): array
    {
        return [
            '404' => ['404', 'Page introuvable'],
            '403' => ['403', 'Accès non autorisé'],
            '419' => ['419', 'Session expirée'],
            '429' => ['429', 'Trop de tentatives'],
            '500' => ['500', 'Erreur interne'],
            '503' => ['503', 'Maintenance en cours'],
        ];
    }
}
