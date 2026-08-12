<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserSeederSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_refuses_to_run_in_production_even_with_the_force_flag(): void
    {
        $this->app['env'] = 'production';

        // --force est exactement ce que passaient les configurations de
        // déploiement : il neutralise la confirmation intégrée de Laravel,
        // donc le garde-fou du seeder doit tenir seul.
        $this->artisan('db:seed', [
            '--class' => UserSeeder::class,
            '--force' => true,
        ])->assertExitCode(0);

        $this->assertSame(0, User::count());
    }

    public function test_it_seeds_the_demo_accounts_outside_production(): void
    {
        $this->app['env'] = 'local';

        $this->seed(UserSeeder::class);

        $this->assertSame(4, User::count());
        $this->assertSame('admin', User::where('email', 'admin@oncc-sn.com')->value('role'));
    }

    public function test_it_uses_the_configured_seed_password(): void
    {
        $this->app['env'] = 'local';
        config(['seeding.password' => 'mot-de-passe-de-test']);

        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@oncc-sn.com')->firstOrFail();
        $this->assertTrue(Hash::check('mot-de-passe-de-test', $admin->password));
    }

    public function test_it_generates_a_random_password_when_none_is_configured(): void
    {
        $this->app['env'] = 'local';
        config(['seeding.password' => null]);

        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@oncc-sn.com')->firstOrFail();

        // The historically published password must never be what gets seeded.
        $this->assertFalse(Hash::check('Admin@2026', $admin->password));
    }
}
