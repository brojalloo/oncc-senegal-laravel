<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Vérifie que la politique de sécurité autorise ce que l'application charge
 * réellement.
 *
 * Les autres tests de CSP contrôlent la présence des directives ; aucun ne
 * disait si elles laissaient passer les ressources du projet. La politique a
 * ainsi bloqué pendant un temps les polices Google importées par style.css :
 * l'en-tête était bien là, complet et parfaitement inutile à cet endroit.
 *
 * Ce test part donc des fichiers CSS eux-mêmes. Toute feuille qui ajoutera
 * demain un domaine externe fera échouer la suite tant que la CSP ne l'aura
 * pas déclaré.
 */
class ContentSecurityPolicyCoverageTest extends TestCase
{
    public function test_every_host_referenced_by_the_stylesheets_is_allowed(): void
    {
        $policy = $this->policy();
        $missing = [];

        foreach ($this->stylesheetHosts() as $host => $files) {
            if (! $this->policyAllows($policy, $host)) {
                $missing[$host] = $files;
            }
        }

        $this->assertSame([], $missing, $this->explain($missing));
    }

    public function test_the_stylesheets_actually_reference_external_hosts(): void
    {
        // Garde-fou : si l'extraction cesse de trouver quoi que ce soit, le
        // test précédent passerait à vide.
        $this->assertNotEmpty(
            $this->stylesheetHosts(),
            'Aucun domaine externe trouvé dans les CSS : extraction probablement cassée.'
        );
    }

    private function policy(): string
    {
        return $this->get('/login')->headers->get('Content-Security-Policy') ?? '';
    }

    /**
     * Domaines chargés par les CSS, via @import ou url().
     *
     * @return array<string, list<string>> hôte => fichiers qui le référencent
     */
    private function stylesheetHosts(): array
    {
        $hosts = [];

        foreach (glob(public_path('css/*.css')) as $file) {
            $css = file_get_contents($file);

            preg_match_all('#url\(\s*[\'"]?(https://[^\'")\s]+)#i', $css, $urls);
            preg_match_all('#@import\s+url\(\s*[\'"]?(https://[^\'")\s]+)#i', $css, $imports);

            foreach (array_merge($urls[1], $imports[1]) as $url) {
                $host = parse_url($url, PHP_URL_HOST);
                if ($host) {
                    $hosts[$host][basename($file)] = basename($file);
                }
            }
        }

        return array_map('array_values', $hosts);
    }

    private function policyAllows(string $policy, string $host): bool
    {
        if (str_contains($policy, "https://{$host}")) {
            return true;
        }

        // Un joker de sous-domaine, tel https://*.tile.openstreetmap.org.
        $parts = explode('.', $host);
        while (count($parts) > 2) {
            array_shift($parts);
            if (str_contains($policy, 'https://*.'.implode('.', $parts))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, list<string>>  $missing
     */
    private function explain(array $missing): string
    {
        if ($missing === []) {
            return '';
        }

        $lines = ["Domaines chargés par les CSS mais absents de la CSP :\n"];
        foreach ($missing as $host => $files) {
            $lines[] = sprintf('  %-32s référencé par %s', $host, implode(', ', $files));
        }
        $lines[] = "\nAjoutez-les à SecurityHeaders, ou retirez la dépendance du CSS.";

        return implode("\n", $lines);
    }
}
