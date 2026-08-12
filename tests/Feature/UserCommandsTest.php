<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_admins_shows_only_admin_users(): void
    {
        User::factory()->admin()->create(['email' => 'admin@test.sn']);
        User::factory()->create(['role' => 'public', 'email' => 'public@test.sn']);

        $this->artisan('users:list-admins')
            ->assertExitCode(0)
            ->expectsOutputToContain('admin@test.sn')
            ->expectsOutputToContain("Nombre d'administrateurs : 1");
    }

    public function test_list_admins_falls_back_to_all_users_when_none_are_admin(): void
    {
        User::factory()->create(['role' => 'public', 'email' => 'public@test.sn']);

        $this->artisan('users:list-admins')
            ->assertExitCode(0)
            ->expectsOutputToContain('Aucun administrateur trouvé')
            ->expectsOutputToContain('public@test.sn');
    }
}
