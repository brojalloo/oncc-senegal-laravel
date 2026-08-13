<?php

namespace Tests\Feature;

use App\Models\DonneeClimatique;
use App\Models\DonneeEconomique;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Deux contrôles n'existaient que dans les vues.
 *
 * Le tableau de bord masquait le bouton de saisie aux comptes « public », et
 * la connexion refusait un compte désactivé — mais les routes acceptaient la
 * requête dans le premier cas, et ne revérifiaient jamais le statut dans le
 * second. Masquer un bouton n'est pas une autorisation, et un contrôle qui ne
 * s'exécute qu'à la connexion ne protège rien pendant les deux heures de vie
 * d'une session.
 */
class ServerSideAuthorizationTest extends TestCase
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

    private function compte(string $role, string $statut = 'actif'): User
    {
        return User::factory()->create(['role' => $role, 'statut' => $statut]);
    }

    /** @return array<string, mixed> */
    private function donneeClimatique(): array
    {
        return [
            'region_id' => $this->region()->id,
            'annee' => 2026,
            'type_indicateur' => 'secheresse',
            'valeur' => 42,
        ];
    }

    public function test_a_read_only_account_cannot_submit_climate_data(): void
    {
        $public = $this->compte('public');

        $this->actingAs($public)
            ->post('/data/climate', $this->donneeClimatique())
            ->assertForbidden();

        $this->assertDatabaseCount('donnees_climatiques', 0);
    }

    public function test_a_read_only_account_cannot_submit_economic_data(): void
    {
        $public = $this->compte('public');

        $this->actingAs($public)->post('/data/economic', [
            'region_id' => $this->region()->id,
            'annee' => 2026,
            'secteur' => 'agriculture',
            'indicateur' => 'pertes_agricoles',
            'valeur' => 1000,
            'impact' => 'negatif',
        ])->assertForbidden();

        $this->assertDatabaseCount('donnees_economiques', 0);
    }

    public function test_a_read_only_account_cannot_even_open_the_form(): void
    {
        $public = $this->compte('public');

        $this->actingAs($public)->get('/data/climate/create')->assertForbidden();
        $this->actingAs($public)->get('/data/economic/create')->assertForbidden();
    }

    /**
     * Le formulaire d'inscription annonce que la collectivité saisit les
     * données économiques : le climatique ne lui revient pas.
     */
    public function test_a_local_authority_may_submit_economic_but_not_climate_data(): void
    {
        $collectivite = $this->compte('collectivite');

        $this->actingAs($collectivite)
            ->post('/data/climate', $this->donneeClimatique())
            ->assertForbidden();

        $this->actingAs($collectivite)->post('/data/economic', [
            'region_id' => Region::first()->id,
            'annee' => 2026,
            'secteur' => 'agriculture',
            'indicateur' => 'pertes_agricoles',
            'valeur' => 1000,
            'impact' => 'negatif',
        ])->assertRedirect();

        $this->assertDatabaseCount('donnees_climatiques', 0);
        $this->assertSame(1, DonneeEconomique::count());
    }

    public function test_a_researcher_may_still_submit_both(): void
    {
        $chercheur = $this->compte('chercheur');

        $this->actingAs($chercheur)
            ->post('/data/climate', $this->donneeClimatique())
            ->assertRedirect();

        $this->assertSame(1, DonneeClimatique::count());
    }

    public function test_everyone_keeps_access_to_their_own_submissions(): void
    {
        $public = $this->compte('public');

        $this->actingAs($public)->get('/data/my-data')->assertOk();
    }

    /**
     * Le cœur du second défaut : la désactivation devait prendre effet
     * immédiatement, pas à l'expiration de la session.
     */
    public function test_deactivating_an_account_ends_its_session_at_once(): void
    {
        $utilisateur = $this->compte('chercheur');

        $this->actingAs($utilisateur)->get('/dashboard')->assertOk();

        $utilisateur->update(['statut' => 'inactif']);

        $this->actingAs($utilisateur)
            ->get('/dashboard')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_a_deactivated_account_cannot_write_either(): void
    {
        $utilisateur = $this->compte('chercheur');
        $donnee = $this->donneeClimatique();

        $utilisateur->update(['statut' => 'inactif']);

        $this->actingAs($utilisateur)->post('/data/climate', $donnee)->assertRedirect(route('login'));

        $this->assertDatabaseCount('donnees_climatiques', 0);
    }

    public function test_an_active_account_is_left_alone(): void
    {
        $this->actingAs($this->compte('chercheur'))->get('/dashboard')->assertOk();
        $this->assertAuthenticated();
    }

    /**
     * Le défaut d'origine venait de l'écart entre ce que l'interface propose
     * et ce que le serveur accepte. Corriger le serveur sans corriger les vues
     * ne ferait que déplacer le problème : l'utilisateur cliquerait sur un
     * lien pour tomber sur une page 403.
     *
     * Ce test suit les liens de saisie effectivement affichés à chaque rôle et
     * vérifie qu'ils s'ouvrent.
     *
     * @return list<array{string}>
     */
    public static function rolesProvider(): array
    {
        return [['public'], ['collectivite'], ['chercheur'], ['admin']];
    }

    #[DataProvider('rolesProvider')]
    public function test_no_visible_link_leads_to_a_refusal(string $role): void
    {
        $compte = $this->compte($role);

        // Balayer plusieurs pages, et pas seulement le tableau de bord : la
        // fuite d'origine venait du pied de page et des appels à l'action de
        // « mes données », pas des raccourcis.
        $pagesAVisiter = ['/dashboard', '/data/my-data', '/cartography'];

        if ($role === 'admin') {
            $pagesAVisiter[] = '/admin/dashboard';
        }

        $tousLesLiens = [];

        foreach ($pagesAVisiter as $page) {
            $reponse = $this->actingAs($compte)->get($page);
            $reponse->assertOk();

            preg_match_all(
                '#href="[^"]*(/data/(?:climate|economic)/create)"#',
                $reponse->getContent(),
                $liens
            );

            foreach (array_unique($liens[1]) as $lien) {
                $tousLesLiens[$lien] = $page;
            }
        }

        foreach ($tousLesLiens as $lien => $page) {
            $this->actingAs($compte)->get($lien)->assertOk(
                "Le rôle « {$role} » voit sur {$page} un lien vers {$lien} que le serveur refuse."
            );
        }

        // Le rôle en lecture seule ne doit se voir proposer aucun des deux.
        if ($role === 'public') {
            $this->assertSame([], array_keys($tousLesLiens));
        }
    }
}
