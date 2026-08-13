<?php

namespace Tests\Feature;

use App\Models\Alerte;
use App\Models\DonneeClimatique;
use App\Models\DonneeEconomique;
use App\Models\Region;
use App\Models\User;
use App\Support\RecentActivity;
use Database\Seeders\DonneeClimatiqueSeeder;
use Database\Seeders\RegionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecentActivityTest extends TestCase
{
    use RefreshDatabase;

    private function region(string $nom = 'Dakar'): Region
    {
        return Region::create(['nom' => $nom, 'code' => strtoupper(substr($nom, 0, 3))]);
    }

    public function test_it_is_empty_on_a_fresh_installation(): void
    {
        $this->assertSame([], RecentActivity::latest());
    }

    public function test_it_reports_a_registration(): void
    {
        User::factory()->create(['nom' => 'Ndiaye', 'prenom' => 'Awa']);

        $messages = array_column(RecentActivity::latest(), 'message');

        $this->assertNotEmpty($messages);
        $this->assertStringContainsString('Awa Ndiaye', $messages[0]);
    }

    public function test_it_reports_submitted_climate_data_with_its_region(): void
    {
        $region = $this->region('Thiès');
        DonneeClimatique::create([
            'region_id' => $region->id,
            'annee' => 2026,
            'type_indicateur' => 'temperature',
            'valeur' => 31.5,
            'statut' => 'en_attente',
        ]);

        $messages = array_column(RecentActivity::latest(), 'message');

        $this->assertNotEmpty(
            array_filter($messages, fn ($m) => str_contains($m, 'Thiès') && str_contains($m, 'climatique'))
        );
    }

    public function test_it_reports_a_validation_separately_from_the_submission(): void
    {
        $region = $this->region();
        $donnee = DonneeClimatique::create([
            'region_id' => $region->id,
            'annee' => 2026,
            'type_indicateur' => 'secheresse',
            'valeur' => 4,
            'statut' => 'en_attente',
        ]);

        // created_at n'est pas assignable en masse : on repousse la saisie dans
        // le passé pour refléter la réalité — un administrateur valide bien
        // après coup, jamais dans la même seconde.
        $donnee->forceFill(['created_at' => now()->subHour()])->saveQuietly();

        $donnee->update(['statut' => 'valide']);

        $messages = array_column(RecentActivity::latest(), 'message');

        $this->assertNotEmpty(array_filter($messages, fn ($m) => str_contains($m, 'validée')));
        $this->assertNotEmpty(array_filter($messages, fn ($m) => str_contains($m, 'saisie')));
    }

    public function test_pending_data_is_never_reported_as_validated(): void
    {
        $region = $this->region();
        DonneeEconomique::create([
            'region_id' => $region->id,
            'annee' => 2026,
            'secteur' => 'agriculture',
            'indicateur' => 'rendement',
            'valeur' => 1000,
            'statut' => 'en_attente',
        ]);

        $messages = array_column(RecentActivity::latest(), 'message');

        $this->assertEmpty(array_filter($messages, fn ($m) => str_contains($m, 'validée')));
    }

    public function test_it_orders_newest_first(): void
    {
        $region = $this->region();

        User::factory()->create(['created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)]);
        Alerte::create([
            'region_id' => $region->id,
            'type_alerte' => 'inondation',
            'niveau' => 'eleve',
            'created_at' => now()->subMinutes(2),
            'updated_at' => now()->subMinutes(2),
        ]);

        $activities = RecentActivity::latest();

        $this->assertStringContainsString('Alerte', $activities[0]['message']);
    }

    public function test_it_caps_the_number_of_entries(): void
    {
        User::factory()->count(12)->create();

        $this->assertLessThanOrEqual(6, count(RecentActivity::latest(6)));
    }

    public function test_it_groups_identical_entries_instead_of_repeating_them(): void
    {
        $region = $this->region('Kaolack');

        for ($i = 0; $i < 5; $i++) {
            DonneeClimatique::create([
                'region_id' => $region->id,
                'annee' => 2026,
                'type_indicateur' => 'pluviometrie',
                'valeur' => $i,
                'statut' => 'en_attente',
            ]);
        }

        $messages = array_column(RecentActivity::latest(), 'message');
        $saisies = array_values(array_filter($messages, fn ($m) => str_contains($m, 'Kaolack')));

        // Une seule ligne, portant le nombre — et non cinq lignes identiques.
        $this->assertCount(1, $saisies);
        $this->assertStringContainsString('×5', $saisies[0]);
    }

    public function test_no_activity_is_dated_in_the_future(): void
    {
        // Un fil affichant « dans 13 heures » se lit comme un bug, et il en est
        // un : un événement passé ne peut pas être postérieur à maintenant.
        // Les données de démonstration ont produit ce cas en datant les
        // validations depuis la saisie sans borner le résultat.
        $this->seed(RegionSeeder::class);
        $this->seed(DonneeClimatiqueSeeder::class);

        foreach (RecentActivity::latest(20) as $activity) {
            $this->assertTrue(
                $activity['at']->lessThanOrEqualTo(now()->addMinute()),
                "Activité datée dans le futur : {$activity['message']} ({$activity['at']})"
            );
        }
    }

    public function test_every_entry_carries_a_human_readable_time(): void
    {
        User::factory()->create();

        foreach (RecentActivity::latest() as $activity) {
            $this->assertArrayHasKey('time', $activity);
            $this->assertNotSame('', $activity['time']);
        }
    }

    public function test_the_dashboard_shows_real_activity_not_the_old_placeholders(): void
    {
        $admin = User::factory()->admin()->create(['nom' => 'Sarr', 'prenom' => 'Ousmane']);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        // Ces libellés étaient codés en dur et présentés comme de l'activité réelle.
        $response->assertDontSee('Alerte créée pour Dakar');
        $response->assertDontSee('Rapport généré');
        $response->assertSee('Ousmane Sarr');
    }
}
