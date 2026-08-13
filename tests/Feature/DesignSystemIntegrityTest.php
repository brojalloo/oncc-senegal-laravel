<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Une déclaration CSS qui référence une variable inexistante ne provoque
 * aucune erreur : le navigateur l'ignore, la page se charge, les tests
 * passent, et la règle disparaît en silence. C'est ainsi que le passage
 * à Vite a laissé onze variables mortes et quatorze classes sans style
 * dans des vues qui renvoyaient toutes un code 200.
 *
 * Ces tests couvrent ce mode de panne, que rien d'autre ne détecte.
 */
class DesignSystemIntegrityTest extends TestCase
{
    /**
     * Chemins déduits de l'emplacement du test, et non de base_path() :
     * ce dernier suit le chargeur de classes, qu'un vendor/ lié par
     * jonction fait pointer vers un autre exemplaire du dépôt.
     */
    private function racine(): string
    {
        return dirname(__DIR__, 2);
    }

    /** Variables définies ailleurs que dans app.css et légitimes à ce titre. */
    private const EXTERNES = [
        'bs-',  // Bootstrap, chargé depuis un CDN
        'tw-',  // internes à Tailwind
    ];

    public function test_every_custom_property_used_in_the_stylesheet_is_defined(): void
    {
        $css = file_get_contents($this->racine().'/resources/css/app.css');

        $definies = $this->proprietesDefinies($css);
        $manquantes = array_values(array_diff($this->proprietesUtilisees($css), $definies));

        $this->assertSame([], $manquantes, sprintf(
            "app.css référence %d propriété(s) personnalisée(s) que rien ne définit : %s.\n".
            'Ces déclarations sont ignorées par le navigateur, sans erreur.',
            count($manquantes),
            implode(', ', $manquantes)
        ));
    }

    public function test_views_do_not_reference_undefined_custom_properties(): void
    {
        $definies = $this->proprietesDefinies(file_get_contents($this->racine().'/resources/css/app.css'));
        $fautives = [];

        foreach ($this->vues() as $chemin) {
            $source = file_get_contents($chemin);

            // Les gabarits autonomes — pages d'erreur, courriels — portent
            // délibérément leurs propres variables et ne dépendent pas d'app.css.
            if (preg_match('/:root\s*[,{]/', $source)) {
                continue;
            }

            foreach (array_diff($this->proprietesUtilisees($source), $definies) as $variable) {
                $fautives[] = basename($chemin).' → '.$variable;
            }
        }

        $this->assertSame([], array_values(array_unique($fautives)), sprintf(
            "Des vues référencent des variables qu'app.css ne définit pas :\n%s",
            implode("\n", array_unique($fautives))
        ));
    }

    public function test_the_design_tokens_cover_both_themes(): void
    {
        $css = file_get_contents($this->racine().'/resources/css/app.css');

        preg_match('/:root \{(.+?)\n\}/s', $css, $clair);
        preg_match('/\[data-theme="dark"\] \{(.+?)\n\}/s', $css, $sombre);

        $this->assertNotEmpty($clair, 'Le bloc :root des jetons est introuvable.');
        $this->assertNotEmpty($sombre, 'Le bloc [data-theme="dark"] des jetons est introuvable.');

        // Les jetons de couleur doivent exister dans les deux thèmes ; ceux qui
        // n'en dépendent pas (rayon, familles de police, dégradé) sont hérités.
        $sansEquivalent = array_diff(
            $this->proprietesDefinies($clair[1]),
            $this->proprietesDefinies($sombre[1]),
            ['--oncc-radius', '--oncc-display', '--oncc-body', '--oncc-data', '--oncc-lat']
        );

        $this->assertSame([], array_values($sansEquivalent), sprintf(
            'Ces jetons ne sont définis que pour le thème clair : %s.',
            implode(', ', $sansEquivalent)
        ));
    }

    /** @return list<string> */
    private function proprietesDefinies(string $css): array
    {
        preg_match_all('/(--[a-zA-Z0-9-]+)\s*:/', $css, $m);

        return array_values(array_unique($m[1]));
    }

    /** @return list<string> */
    private function proprietesUtilisees(string $css): array
    {
        preg_match_all('/var\(\s*(--[a-zA-Z0-9-]+)/', $css, $m);

        $utilisees = array_unique($m[1]);

        return array_values(array_filter($utilisees, function (string $variable): bool {
            foreach (self::EXTERNES as $prefixe) {
                if (str_starts_with(ltrim($variable, '-'), $prefixe)) {
                    return false;
                }
            }

            return true;
        }));
    }

    /** @return list<string> */
    private function vues(): array
    {
        $vues = [];
        $iterateur = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->racine().'/resources/views')
        );

        foreach ($iterateur as $fichier) {
            if (! $fichier->isDir() && str_ends_with($fichier->getFilename(), '.blade.php')) {
                $vues[] = $fichier->getPathname();
            }
        }

        return $vues;
    }
}
