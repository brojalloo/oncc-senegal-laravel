<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\DatabaseSize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseSizeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reports_a_size_on_the_connected_driver(): void
    {
        $bytes = DatabaseSize::bytes();

        $this->assertNotNull($bytes, 'Aucune taille obtenue sur '.DB::connection()->getDriverName());
        $this->assertGreaterThan(0, $bytes);
    }

    public function test_it_formats_the_size_for_display(): void
    {
        $this->assertMatchesRegularExpression(
            '/^[\d.,]+ (o|Ko|Mo|Go)$/u',
            DatabaseSize::human()
        );
    }

    public function test_it_never_reports_the_old_permanent_placeholder(): void
    {
        // L'ancienne implémentation interrogeait une connexion MySQL absente
        // et renvoyait « N/A » à chaque appel, l'exception étant avalée.
        $this->assertNotSame('N/A', DatabaseSize::human());
    }

    public function test_it_degrades_gracefully_on_an_unknown_driver(): void
    {
        $original = config('database.default');

        config(['database.connections.inconnue' => [
            'driver' => 'inexistant',
            'database' => ':memory:',
        ]]);
        config(['database.default' => 'inconnue']);

        try {
            $this->assertNull(DatabaseSize::bytes());
            $this->assertSame('Indisponible', DatabaseSize::human());
        } finally {
            // Sans cette restauration, le nettoyage de RefreshDatabase
            // tenterait de revenir en arrière sur une connexion inexistante.
            config(['database.default' => $original]);
        }
    }

    public function test_the_admin_dashboard_shows_the_size(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertDontSee('N/A');
    }
}
