<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Region;
use Carbon\Carbon;

class AlerteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = Region::all();
        $typesAlerte = ['secheresse', 'inondation', 'desertification', 'tempete'];
        $niveaux = ['faible', 'moyen', 'eleve', 'critique'];
        $descriptions = [
            'secheresse' => 'Niveau critique de sécheresse détecté dans la région',
            'inondation' => 'Risque élevé d\'inondation suite à des pluies importantes',
            'tempete' => 'Risque de tempête avec vents violents',
            'desertification' => 'Avancée significative de la désertification'
        ];
        
        $annees = [2020, 2021, 2022, 2023, 2024, 2025];

        // Créer plusieurs alertes sur différentes années
        foreach ($annees as $annee) {
            $nbAlertes = rand(3, 6);
            for ($i = 0; $i < $nbAlertes; $i++) {
                $region = $regions->random();
                $typeAlerte = $typesAlerte[array_rand($typesAlerte)];
                $niveau = $niveaux[array_rand($niveaux)];
                
                // Date de début aléatoire dans l'année
                $dateDebut = Carbon::create($annee, rand(1, 12), rand(1, 28));
                
                DB::table('alertes')->insert([
                    'region_id' => $region->id,
                    'type_alerte' => $typeAlerte,
                    'niveau' => $niveau,
                    'description' => $descriptions[$typeAlerte],
                    'date_debut' => $dateDebut,
                    'date_fin' => $dateDebut->copy()->addDays(rand(5, 60)),
                    'created_at' => $dateDebut,
                    'updated_at' => now()
                ]);
            }
        }

        $this->command->info('Alertes de test insérées avec succès !');
    }
}
