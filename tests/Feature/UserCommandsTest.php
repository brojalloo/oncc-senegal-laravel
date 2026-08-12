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

    public function test_activate_all_activates_unverified_users_with_the_force_flag(): void
    {
        $user = User::factory()->unverified()->create([
            'statut' => 'inactif',
            'verification_token' => 'abc123',
            'verification_token_expires' => now()->addDay(),
        ]);

        $this->artisan('users:activate-all', ['--force' => true])
            ->assertExitCode(0);

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('actif', $user->statut);
        $this->assertNull($user->verification_token);
    }

    public function test_activate_all_activates_unverified_users_when_confirmed(): void
    {
        $user = User::factory()->unverified()->create();

        $this->artisan('users:activate-all')
            ->expectsConfirmation('Activer ces 1 compte(s) ?', 'yes')
            ->assertExitCode(0);

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_activate_all_does_nothing_when_the_user_declines_confirmation(): void
    {
        $user = User::factory()->unverified()->create();

        $this->artisan('users:activate-all')
            ->expectsConfirmation('Activer ces 1 compte(s) ?', 'no')
            ->assertExitCode(1);

        $this->assertNull($user->refresh()->email_verified_at);
    }

    public function test_activate_all_reports_when_there_is_nothing_to_do(): void
    {
        User::factory()->create();

        $this->artisan('users:activate-all')
            ->assertExitCode(0)
            ->expectsOutputToContain("Aucun compte en attente d'activation.");
    }
}
