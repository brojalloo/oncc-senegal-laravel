<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Depuis que le layout charge ses assets par @vite, toute vue rendue
        // exige public/build/manifest.json : sans lui, la page renvoie 500 et
        // le test échoue pour une raison qui n'a rien à voir avec ce qu'il
        // vérifie. La suite ne doit pas dépendre d'une construction préalable.
        //
        // withoutVite() remplace les balises par des chaînes vides. Le build
        // reste vérifié là où c'est son rôle : l'étape npm run build de
        // l'intégration continue, et la construction de l'image conteneur.
        $this->withoutVite();
    }
}
