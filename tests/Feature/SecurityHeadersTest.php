<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_it_sets_the_baseline_security_headers(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_it_declares_a_content_security_policy(): void
    {
        $response = $this->get('/login');

        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp, 'Aucun en-tête Content-Security-Policy.');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
    }

    public function test_the_production_image_disables_the_php_version_banner(): void
    {
        // X-Powered-By est émis par PHP au niveau du SAPI, hors de portée
        // d'une assertion sur la réponse : un test HTTP passerait à vide, quel
        // que soit l'état réel. La seule protection fiable est expose_php=Off
        // dans l'image ; c'est donc elle qu'on vérifie.
        $dockerfile = file_get_contents(base_path('Dockerfile'));

        $this->assertStringContainsString('expose_php=Off', $dockerfile);
    }

    public function test_it_omits_hsts_over_plain_http(): void
    {
        // Envoyer HSTS sur une réponse HTTP est sans effet et piège le
        // développement local, où le site tourne en clair.
        $response = $this->get('/login');

        $this->assertNull($response->headers->get('Strict-Transport-Security'));
    }

    public function test_it_sends_hsts_over_https(): void
    {
        $response = $this->get('https://localhost/login');

        $hsts = $response->headers->get('Strict-Transport-Security');

        $this->assertNotNull($hsts, 'Aucun en-tête Strict-Transport-Security en HTTPS.');
        $this->assertStringContainsString('max-age=', $hsts);
    }

    public function test_it_sends_hsts_behind_a_tls_terminating_proxy(): void
    {
        // Cas réel du déploiement : la plateforme termine le TLS et transmet
        // la requête en clair au conteneur, avec X-Forwarded-Proto. Sans
        // TrustProxies, $request->secure() renvoie false et HSTS n'est jamais
        // émis — l'en-tête serait du code mort en production.
        $response = $this->get('http://localhost/login', [
            'X-Forwarded-Proto' => 'https',
        ]);

        $this->assertNotNull(
            $response->headers->get('Strict-Transport-Security'),
            'HSTS absent derrière un répartiteur : X-Forwarded-Proto n\'est pas pris en compte.'
        );
    }
}
