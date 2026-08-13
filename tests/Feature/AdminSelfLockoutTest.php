<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSelfLockoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_only_admin_cannot_demote_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->put(route('admin.user.role', $admin->id), ['role' => 'public']);

        $response->assertSessionHasErrors();
        $this->assertSame('admin', $admin->refresh()->role);
    }

    public function test_an_admin_cannot_demote_the_last_remaining_admin(): void
    {
        $lastAdmin = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();

        // On retire d'abord le second administrateur, en laissant `$lastAdmin`
        // seul, puis on tente de le rétrograder depuis son propre compte.
        $other->update(['role' => 'chercheur']);

        $response = $this->actingAs($lastAdmin)
            ->put(route('admin.user.role', $lastAdmin->id), ['role' => 'chercheur']);

        $response->assertSessionHasErrors();
        $this->assertSame('admin', $lastAdmin->refresh()->role);
    }

    public function test_an_admin_can_be_demoted_while_another_admin_remains(): void
    {
        $actor = User::factory()->admin()->create();
        $target = User::factory()->admin()->create();

        $response = $this->actingAs($actor)
            ->put(route('admin.user.role', $target->id), ['role' => 'chercheur']);

        $response->assertSessionHasNoErrors();
        $this->assertSame('chercheur', $target->refresh()->role);
    }

    public function test_promoting_a_user_to_admin_still_works(): void
    {
        $actor = User::factory()->admin()->create();
        $target = User::factory()->create(['role' => 'public']);

        $response = $this->actingAs($actor)
            ->put(route('admin.user.role', $target->id), ['role' => 'admin']);

        $response->assertSessionHasNoErrors();
        $this->assertSame('admin', $target->refresh()->role);
    }

    public function test_the_last_admin_cannot_be_deactivated(): void
    {
        $actor = User::factory()->admin()->create();
        $lastAdmin = User::factory()->admin()->create();
        $actor->update(['role' => 'chercheur']);

        // `$lastAdmin` est désormais seul administrateur ; le désactiver
        // fermerait l'administration à tout le monde.
        $this->actingAs($lastAdmin);
        $response = $this->actingAs($lastAdmin)
            ->put(route('admin.user.status', $lastAdmin->id), ['statut' => 'inactif']);

        $response->assertSessionHasErrors();
        $this->assertSame('actif', $lastAdmin->refresh()->statut);
    }
}
