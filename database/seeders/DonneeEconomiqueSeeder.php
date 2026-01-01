<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Region;

class DonneeEconomiqueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = Region::all();
        $secteurs = [
            'agriculture', 
            'peche', 
            'tourisme', 
            'energie',
            'elevage',
            'foret'
        ];
        $indicateurs = [
            'Production agricole',
            'Revenus du secteur',
            'Emplois créés',
            'Investissements',
            'Exportations',
            'Pertes économiques',
            'Rendement',
            'Chiffre d\'affaires',
            'Productivité',
            'Croissance'
        ];
        $annees = [2020, 2021, 2022, 2023, 2024, 2025];
        $impacts = ['positif', 'negatif', 'neutre'];
        $unites = ['FCFA', 'tonnes', 'personnes', '%', 'hectares'];

        // Données détaillées pour chaque région
        foreach ($regions as $region) {
            foreach ($secteurs as $secteur) {
                // 3-5 indicateurs différents par secteur
                $nbIndicateurs = rand(3, 5);
                $selectedIndicateurs = array_rand(array_flip($indicateurs), $nbIndicateurs);
                
                foreach ($selectedIndicateurs as $indicateur) {
                    foreach ($annees as $annee) {
                        // 2 mesures par an (semestriel)
                        for ($i = 1; $i <= 2; $i++) {
                            // Générer des valeurs réalistes
                            $valeur = rand(1000000, 500000000);
                            $unite = $unites[array_rand($unites)];
                            
                            // Ajuster selon l'unité
                            if ($unite === 'tonnes') {
                                $valeur = rand(100, 50000);
                            } elseif ($unite === 'personnes') {
                                $valeur = rand(50, 10000);
                            } elseif ($unite === '%') {
                                $valeur = rand(5, 95);
                            } elseif ($unite === 'hectares') {
                                $valeur = rand(100, 100000);
                            }
                            
                            // Variation selon le secteur et la région
                            if ($secteur === 'agriculture' && in_array($region->nom, ['Kaolack', 'Fatick', 'Kaffrine'])) {
                                $valeur = $valeur * 1.3;
                            }
                            if ($secteur === 'peche' && in_array($region->nom, ['Dakar', 'Thiès', 'Saint-Louis'])) {
                                $valeur = $valeur * 1.4;
                            }
                            if ($secteur === 'tourisme' && in_array($region->nom, ['Dakar', 'Ziguinchor'])) {
                                $valeur = $valeur * 1.5;
                            }

                            $description = "Données sur " . strtolower($indicateur) . " du secteur " . $secteur . " pour la région " . $region->nom;

                            DB::table('donnees_economiques')->insert([
                                'region_id' => $region->id,
                                'secteur' => $secteur,
                                'indicateur' => $indicateur,
                                'valeur' => round($valeur, 2),
                                'unite' => $unite,
                                'annee' => $annee,
                                'impact' => $impacts[array_rand($impacts)],
                                'description' => $description,
                                'statut' => rand(1, 10) > 1 ? 'valide' : 'en_attente',
                                'utilisateur_id' => rand(1, 4),
                                'created_at' => now()->subDays(rand(1, 365)),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            }
        }

        $this->command->info('Données économiques détaillées insérées avec succès !');
        $count = DB::table('donnees_economiques')->count();
        $this->command->info('Total: ' . $count . ' enregistrements dans la base.');
    }
}
