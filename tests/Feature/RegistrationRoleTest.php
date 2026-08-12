<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_rejects_the_admin_role(): void
    {
        $response = $this->post('/register', [
            'email' => 'wouldbeadmin@test.sn',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nom' => 'Sarr',
            'prenom' => 'Moussa',
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertSame(0, User::where('email', 'wouldbeadmin@test.sn')->count());
    }

    public function test_registration_still_accepts_the_three_public_roles(): void
    {
        foreach (['chercheur', 'collectivite', 'public'] as $role) {
            $response = $this->post('/register', [
                'email' => "{$role}@test.sn",
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'nom' => 'Sarr',
                'prenom' => 'Moussa',
                'role' => $role,
            ]);

            $response->assertSessionDoesntHaveErrors('role');
            $this->assertSame(1, User::where('email', "{$role}@test.sn")->count());
        }
    }
}
