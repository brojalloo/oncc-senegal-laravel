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

    /**
     * Les couleurs écrites dans un attribut style="" échappent au système :
     * elles ne suivent ni le thème, ni un changement de palette, et rien ne
     * les signale. C'est par là que quatre bandeaux de titre ont gardé des
     * dégradés vert, violet, orange et ardoise longtemps après que le reste
     * de l'interface avait changé d'identité.
     */
    public function test_views_do_not_hardcode_colours_in_style_attributes(): void
    {
        $fautives = [];

        foreach ($this->vues() as $chemin) {
            $source = file_get_contents($chemin);

            // Les gabarits autonomes ne peuvent pas dépendre de la feuille.
            if (preg_match('/:root\s*[,{]/', $source)) {
                continue;
            }

            // Les courriels non plus : les clients de messagerie ne gèrent pas
            // les propriétés personnalisées, une couleur en dur y est le choix
            // correct et non un oubli.
            if (str_contains(str_replace('\\', '/', $chemin), '/views/emails/')) {
                continue;
            }

            preg_match_all('/style="([^"]*)"/', $source, $attributs);

            foreach ($attributs[1] as $declaration) {
                if (preg_match('/linear-gradient|#[0-9A-Fa-f]{3,8}\b|:\s*white\b/', $declaration)) {
                    $fautives[] = basename($chemin).' → '.mb_substr($declaration, 0, 70);
                }
            }
        }

        $this->assertSame([], $fautives, sprintf(
            "%d attribut(s) style= portent une couleur en dur, hors du système :\n%s",
            count($fautives),
            implode("\n", $fautives)
        ));
    }

    /**
     * Un <option> ne rend que du texte : aucune icône ne peut s'y afficher,
     * d'où le recours à l'emoji. Mais l'emoji ne suit ni la police, ni la
     * palette, ni le thème — il est rendu par le système d'exploitation et
     * change d'aspect d'une machine à l'autre. Les libellés portent donc leur
     * icône, et les options restent du texte.
     *
     * Les courriels gardent les leurs : ils s'affichent dans un client de
     * messagerie, hors du système.
     */
    public function test_application_views_do_not_use_emoji_as_icons(): void
    {
        $fautives = [];

        foreach ($this->vues() as $chemin) {
            if (str_contains(str_replace('\\', '/', $chemin), '/views/emails/')) {
                continue;
            }

            foreach (explode("\n", file_get_contents($chemin)) as $numero => $ligne) {
                // Les traces de développement ne parviennent pas à l'écran.
                if (str_contains($ligne, 'console.')) {
                    continue;
                }

                if (preg_match('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $ligne)) {
                    $fautives[] = basename($chemin).':'.($numero + 1);
                }
            }
        }

        $this->assertSame([], $fautives, sprintf(
            "%d ligne(s) utilisent un emoji comme icône :\n%s",
            count($fautives),
            implode("\n", $fautives)
        ));
    }

    public function test_every_page_header_uses_the_shared_band(): void
    {
        $variantes = [];

        foreach ($this->vues() as $chemin) {
            preg_match_all('/class="page-header([^"]*)"/', $chemin ? file_get_contents($chemin) : '', $usages);

            foreach ($usages[1] as $suffixe) {
                if (trim($suffixe) !== '') {
                    $variantes[] = basename($chemin).' → page-header'.$suffixe;
                }
            }
        }

        $this->assertSame([], $variantes, sprintf(
            "Des bandeaux de titre portent une classe de variante ; il n'y en a plus qu'un :\n%s",
            implode("\n", $variantes)
        ));
    }

    public function test_the_chrome_band_stays_dark_in_both_themes(): void
    {
        // Six bandeaux utilisent --oncc-chrome en fond avec du texte clair
        // posé dessus. Un jeton clair de ce côté rendrait ce texte invisible.
        foreach (['clair' => $this->jeton('--oncc-chrome', false),
            'sombre' => $this->jeton('--oncc-chrome', true)] as $theme => $couleur) {
            $this->assertLessThan(0.2, $this->luminance($couleur), sprintf(
                'Le bandeau de chrome du thème %s (%s) est trop clair pour porter du texte clair.',
                $theme,
                $couleur
            ));
        }
    }

    /**
     * Les couples texte/fond du système tiennent le seuil AA de 4,5:1 dans
     * les deux thèmes. Sans ce test, un ajustement de teinte peut faire
     * passer un libellé sous le seuil sans que rien ne le signale.
     */
    public function test_the_token_pairs_meet_the_aa_contrast_threshold(): void
    {
        $couples = [
            'texte sur surface' => ['--oncc-ink', '--oncc-surface'],
            'texte doux sur surface' => ['--oncc-ink-soft', '--oncc-surface'],
            'texte sur fond' => ['--oncc-ink', '--oncc-ground'],
            'accent sur surface' => ['--oncc-accent', '--oncc-surface'],
            'niveau faible' => ['--oncc-faible', '--oncc-faible-bg'],
            'niveau moyen' => ['--oncc-moyen', '--oncc-moyen-bg'],
            'niveau élevé' => ['--oncc-eleve', '--oncc-eleve-bg'],
            'niveau critique' => ['--oncc-critique', '--oncc-critique-bg'],
        ];

        $insuffisants = [];

        foreach ([false, true] as $sombre) {
            foreach ($couples as $nom => [$texte, $fond]) {
                $rapport = $this->contraste($this->jeton($texte, $sombre), $this->jeton($fond, $sombre));

                if ($rapport < 4.5) {
                    $insuffisants[] = sprintf(
                        '%s (thème %s) : %.2f:1',
                        $nom,
                        $sombre ? 'sombre' : 'clair',
                        $rapport
                    );
                }
            }
        }

        $this->assertSame([], $insuffisants, "Couples sous le seuil AA :\n".implode("\n", $insuffisants));
    }

    public function test_the_theme_is_applied_before_the_first_paint(): void
    {
        // Chargé comme module Vite, le script s'exécuterait après le premier
        // rendu : la page apparaîtrait en clair avant de basculer.
        $partiel = $this->racine().'/resources/views/layouts/theme.blade.php';
        $this->assertFileExists($partiel);

        // Les trois vues qui portent leur propre <head>.
        foreach (['layouts/app', 'auth/login', 'auth/register'] as $vue) {
            $source = file_get_contents($this->racine()."/resources/views/{$vue}.blade.php");

            $this->assertStringContainsString("@include('layouts.theme')", $source, sprintf(
                "La vue %s porte son propre <head> mais n'inclut pas le choix de thème : ".
                'la page y scintillerait en clair avant de basculer.',
                $vue
            ));

            $positionTheme = strpos($source, "@include('layouts.theme')");
            $positionVite = strpos($source, '@vite(');

            $this->assertLessThan($positionVite, $positionTheme, sprintf(
                'Dans %s, le choix de thème doit précéder @vite.',
                $vue
            ));
        }
    }

    private function jeton(string $nom, bool $sombre): string
    {
        $css = file_get_contents($this->racine().'/resources/css/app.css');

        $motif = $sombre
            ? '/\[data-theme="dark"\] \{(.+?)\n\}/s'
            : '/:root \{(.+?)\n\}/s';

        preg_match($motif, $css, $bloc);
        preg_match('/'.preg_quote($nom, '/').':\s*(#[0-9A-Fa-f]{6})/', $bloc[1] ?? '', $valeur);

        $this->assertNotEmpty($valeur, sprintf(
            'Jeton %s introuvable dans le thème %s.',
            $nom,
            $sombre ? 'sombre' : 'clair'
        ));

        return $valeur[1];
    }

    private function luminance(string $hexadecimal): float
    {
        $canaux = [];

        foreach ([0, 2, 4] as $decalage) {
            $canal = hexdec(substr(ltrim($hexadecimal, '#'), $decalage, 2)) / 255;
            $canaux[] = $canal <= 0.03928
                ? $canal / 12.92
                : (($canal + 0.055) / 1.055) ** 2.4;
        }

        return 0.2126 * $canaux[0] + 0.7152 * $canaux[1] + 0.0722 * $canaux[2];
    }

    private function contraste(string $premier, string $second): float
    {
        $a = $this->luminance($premier);
        $b = $this->luminance($second);

        return (max($a, $b) + 0.05) / (min($a, $b) + 0.05);
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
