<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_for_a_signed_in_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/user/profile')->assertOk();
    }

    public function test_the_days_active_counter_is_a_whole_number(): void
    {
        // Carbon 3 a changé diffInDays : la méthode renvoie désormais un
        // flottant signé, là où Carbon 2 donnait un entier absolu. Sans
        // arrondi, le compteur s'affiche « 5.2916666761806 ».
        $user = User::factory()->create([
            'created_at' => now()->subDays(5)->subHours(7),
        ]);

        $html = $this->actingAs($user)->get('/user/profile')->getContent();

        $this->assertMatchesRegularExpression(
            '/<div class="value">\s*5\s*<\/div>\s*<div class="label">Jours actif<\/div>/',
            $html,
            'Le compteur « Jours actif » n\'affiche pas un entier.'
        );
    }
}
