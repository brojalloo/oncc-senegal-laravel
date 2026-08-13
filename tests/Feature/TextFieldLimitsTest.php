<?php

namespace Tests\Feature;

use App\Models\DonneeClimatique;
use App\Models\DonneeEconomique;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Trois champs texte étaient validés sans borne supérieure. Les colonnes sont
 * de type text, donc rien ne s'y opposait côté base : un compte authentifié
 * pouvait écrire des chaînes arbitrairement longues, et le corps d'infolettre
 * est recopié pour chaque destinataire au moment de la mise en file.
 */
class TextFieldLimitsTest extends TestCase
{
    use RefreshDatabase;

    private function region(): Region
    {
        return Region::create([
            'nom' => 'Dakar',
            'code' => 'DK',
            'latitude' => 14.7,
            'longitude' => -17.4,
        ]);
    }

    public function test_a_climate_comment_is_bounded(): void
    {
        $chercheur = User::factory()->create(['role' => 'chercheur', 'statut' => 'actif']);

        $this->actingAs($chercheur)->post('/data/climate', [
            'region_id' => $this->region()->id,
            'annee' => 2026,
            'type_indicateur' => 'secheresse',
            'valeur' => 42,
            'commentaire' => str_repeat('a', 2001),
        ])->assertSessionHasErrors('commentaire');

        $this->assertSame(0, DonneeClimatique::count());
    }

    public function test_a_comment_within_the_limit_still_goes_through(): void
    {
        $chercheur = User::factory()->create(['role' => 'chercheur', 'statut' => 'actif']);

        $this->actingAs($chercheur)->post('/data/climate', [
            'region_id' => $this->region()->id,
            'annee' => 2026,
            'type_indicateur' => 'secheresse',
            'valeur' => 42,
            'commentaire' => str_repeat('a', 2000),
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, DonneeClimatique::count());
    }

    public function test_an_economic_description_is_bounded(): void
    {
        $chercheur = User::factory()->create(['role' => 'chercheur', 'statut' => 'actif']);

        $this->actingAs($chercheur)->post('/data/economic', [
            'region_id' => $this->region()->id,
            'annee' => 2026,
            'secteur' => 'agriculture',
            'indicateur' => 'pertes_agricoles',
            'valeur' => 1000,
            'impact' => 'negatif',
            'description' => str_repeat('a', 2001),
        ])->assertSessionHasErrors('description');

        $this->assertSame(0, DonneeEconomique::count());
    }

    /**
     * Le formulaire économique postait « commentaire » alors que le contrôleur
     * et la colonne s'appellent « description » : le texte saisi était accepté,
     * l'enregistrement créé, et la note perdue en silence.
     */
    public function test_the_economic_form_posts_the_field_the_controller_reads(): void
    {
        $chercheur = User::factory()->create(['role' => 'chercheur', 'statut' => 'actif']);

        $formulaire = $this->actingAs($chercheur)->get('/data/economic/create');
        $formulaire->assertOk();

        preg_match_all('/<textarea[^>]*name="([^"]+)"/', $formulaire->getContent(), $champs);

        $this->assertContains('description', $champs[1],
            "Le formulaire économique n'expose pas de champ « description » : ".
            'ce que la personne écrit ne sera pas enregistré.');

        $this->actingAs($chercheur)->post('/data/economic', [
            'region_id' => $this->region()->id,
            'annee' => 2026,
            'secteur' => 'agriculture',
            'indicateur' => 'pertes_agricoles',
            'valeur' => 1000,
            'impact' => 'negatif',
            'description' => 'Sécheresse prolongée sur la vallée.',
        ])->assertSessionHasNoErrors();

        $this->assertSame(
            'Sécheresse prolongée sur la vallée.',
            DonneeEconomique::first()->description
        );
    }

    public function test_a_newsletter_body_is_bounded(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'statut' => 'actif']);

        $this->actingAs($admin)->post('/admin/emails/newsletter', [
            'subject' => 'Bulletin de janvier',
            'content' => str_repeat('a', 20001),
            'target' => 'all',
        ])->assertSessionHasErrors('content');
    }
}
