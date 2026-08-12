<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DonneeClimatiqueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = Region::all();
        $indicateurs = [
            'secheresse',
            'inondation',
            'desertification',
            'temperature',
            'pluviometrie',
        ];
        $annees = [2020, 2021, 2022, 2023, 2024, 2025];
        $sources = ['ANACIM', 'MEPA', 'CSE', 'DGPRE', 'DMN', 'OMM'];

        // Données détaillées pour chaque région
        foreach ($regions as $region) {
            foreach ($indicateurs as $indicateur) {
                foreach ($annees as $annee) {
                    // 4 mesures par an (une par trimestre)
                    for ($i = 1; $i <= 4; $i++) {
                        // Générer des valeurs réalistes selon l'indicateur et la région
                        $valeur = match ($indicateur) {
                            'secheresse' => rand(10, 85),
                            'inondation' => rand(5, 70),
                            'desertification' => rand(15, 65),
                            'temperature' => rand(22, 42) + (rand(0, 9) / 10),
                            'pluviometrie' => rand(150, 1500),
                            default => rand(10, 80)
                        };

                        $unite = match ($indicateur) {
                            'temperature' => '°C',
                            'pluviometrie' => 'mm',
                            default => '%'
                        };

                        DB::table('donnees_climatiques')->insert([
                            'region_id' => $region->id,
                            'type_indicateur' => $indicateur,
                            'valeur' => $valeur,
                            'unite' => $unite,
                            'annee' => $annee,
                            'source' => $sources[array_rand($sources)],
                            'statut' => rand(1, 10) > 1 ? 'valide' : 'en_attente',
                            'created_at' => now()->subDays(rand(1, 365)),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        $this->command->info('Données climatiques détaillées insérées avec succès !');
        $this->command->info('Total: '.(count($regions) * count($indicateurs) * count($annees) * 4).' enregistrements créés.');
    }
}
