<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Le projet charge Bootstrap depuis un CDN puis Tailwind par Vite. Les deux
 * publient des classes utilitaires, et certains noms se recouvrent.
 *
 * La plupart des utilitaires de Bootstrap portent !important, ce qui les
 * protège. Ses composants, eux, ne sont pas protégés : un utilitaire Tailwind
 * du même nom s'applique par-dessus, sans erreur et sans avertissement de
 * construction. C'est ce qui a rendu invisibles le panneau latéral et le menu
 * de la barre haute pendant plusieurs semaines.
 */
class StylesheetCollisionTest extends TestCase
{
    private function racine(): string
    {
        return dirname(__DIR__, 2);
    }

    /**
     * Composants Bootstrap dont le nom est aussi un utilitaire Tailwind, avec
     * la propriété que l'utilitaire imposerait s'il l'emportait.
     */
    private const COMPOSANTS_EXPOSES = [
        'collapse' => 'visibility',
    ];

    public function test_bootstrap_components_are_not_overridden_by_tailwind_utilities(): void
    {
        $bundle = $this->feuilleConstruite();

        foreach (self::COMPOSANTS_EXPOSES as $classe => $propriete) {
            preg_match_all(
                '/\.'.preg_quote($classe, '/').'\{([^}]*)\}/',
                $bundle,
                $regles,
                PREG_OFFSET_CAPTURE
            );

            $this->assertNotEmpty($regles[0], "Aucune règle .{$classe} dans la feuille construite.");

            // Une règle hors couche l'emporte sur toute règle en couche, quel
            // que soit l'ordre des fichiers : c'est là que doit se trouver la
            // correction.
            $correctionHorsCouche = false;

            foreach ($regles[0] as $index => [$regle, $position]) {
                $avant = substr($bundle, 0, $position);
                $enCouche = substr_count($avant, '{') > substr_count($avant, '}');

                if (! $enCouche && str_contains($regles[1][$index][0], $propriete)) {
                    $correctionHorsCouche = true;
                }
            }

            $this->assertTrue($correctionHorsCouche, sprintf(
                "Rien ne protège le composant Bootstrap .%s de l'utilitaire Tailwind du même nom. ".
                'Ajoutez une règle hors couche dans resources/css/app.css qui rétablisse %s.',
                $classe,
                $propriete
            ));
        }
    }

    public function test_the_collapsible_panels_stay_visible_once_open(): void
    {
        $bundle = $this->feuilleConstruite();

        // La déclaration gagnante, et non simplement la présence d'une règle.
        preg_match_all('/\.collapse\{([^}]*)\}/', $bundle, $regles, PREG_OFFSET_CAPTURE);

        $gagnante = null;

        foreach ($regles[0] as $index => [, $position]) {
            $avant = substr($bundle, 0, $position);

            if (substr_count($avant, '{') === substr_count($avant, '}')) {
                $gagnante = $regles[1][$index][0];
            }
        }

        $this->assertNotNull($gagnante, 'Aucune règle .collapse hors couche.');
        $this->assertStringNotContainsString('visibility:collapse', $gagnante);
        $this->assertStringContainsString('visibility:visible', $gagnante);
    }

    /**
     * La feuille réellement servie, lue par le manifeste : un glob prendrait
     * le premier fichier venu, y compris le résidu d'une compilation
     * précédente, et le test vérifierait alors autre chose que ce que le
     * navigateur reçoit.
     */
    private function feuilleConstruite(): string
    {
        $manifeste = $this->racine().'/public/build/manifest.json';

        if (! is_file($manifeste)) {
            $this->markTestSkipped(
                'Aucune compilation présente. Lancez npm run build : cette collision '.
                "n'est visible qu'après compilation, Tailwind ne générant ses ".
                'utilitaires que là.'
            );
        }

        $entrees = json_decode(file_get_contents($manifeste), true);
        $chemin = $entrees['resources/css/app.css']['file'] ?? null;

        $this->assertNotNull($chemin, "Le manifeste ne déclare pas d'entrée pour app.css.");

        return file_get_contents($this->racine().'/public/build/'.$chemin);
    }
}
