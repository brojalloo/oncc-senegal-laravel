<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_it_responds_ok(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
        $response->assertSee('OK');
    }

    public function test_it_starts_no_session(): void
    {
        // Interrogée toutes les 30 s par le conteneur, cette route ne doit pas
        // créer de session : avec SESSION_DRIVER=database, ce serait des
        // milliers de lignes par jour dans la table `sessions`.
        $response = $this->get('/up');

        $cookies = collect($response->headers->getCookies())
            ->map(fn ($c) => $c->getName());

        $this->assertFalse(
            $cookies->contains(config('session.cookie')),
            'La sonde /up pose un cookie de session : elle traverse le groupe « web ».'
        );
    }

    public function test_it_answers_without_touching_the_database(): void
    {
        // La sonde doit distinguer « serveur web mort » de « base en panne ».
        // On coupe la base : la route doit continuer de répondre.
        config(['database.default' => 'invalide']);

        $this->get('/up')->assertOk();
    }
}
