<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            ['code' => 'DK', 'nom' => 'Dakar', 'latitude' => 14.7167, 'longitude' => -17.4677, 'chef_lieu' => 'Dakar'],
            ['code' => 'TH', 'nom' => 'Thiès', 'latitude' => 14.7913, 'longitude' => -16.9252, 'chef_lieu' => 'Thiès'],
            ['code' => 'DL', 'nom' => 'Diourbel', 'latitude' => 14.6566, 'longitude' => -16.2329, 'chef_lieu' => 'Diourbel'],
            ['code' => 'ST', 'nom' => 'Saint-Louis', 'latitude' => 16.0246, 'longitude' => -16.4896, 'chef_lieu' => 'Saint-Louis'],
            ['code' => 'KA', 'nom' => 'Kaolack', 'latitude' => 14.1444, 'longitude' => -16.0785, 'chef_lieu' => 'Kaolack'],
            ['code' => 'LG', 'nom' => 'Louga', 'latitude' => 15.6142, 'longitude' => -16.2215, 'chef_lieu' => 'Louga'],
            ['code' => 'FK', 'nom' => 'Fatick', 'latitude' => 14.3390, 'longitude' => -16.4111, 'chef_lieu' => 'Fatick'],
            ['code' => 'KD', 'nom' => 'Kolda', 'latitude' => 12.9107, 'longitude' => -14.9506, 'chef_lieu' => 'Kolda'],
            ['code' => 'ZG', 'nom' => 'Ziguinchor', 'latitude' => 12.5641, 'longitude' => -16.2639, 'chef_lieu' => 'Ziguinchor'],
            ['code' => 'TC', 'nom' => 'Tambacounda', 'latitude' => 13.7726, 'longitude' => -13.6714, 'chef_lieu' => 'Tambacounda'],
            ['code' => 'MT', 'nom' => 'Matam', 'latitude' => 15.6566, 'longitude' => -13.2577, 'chef_lieu' => 'Matam'],
            ['code' => 'SL', 'nom' => 'Sédhiou', 'latitude' => 12.7046, 'longitude' => -15.5563, 'chef_lieu' => 'Sédhiou'],
            ['code' => 'KDG', 'nom' => 'Kédougou', 'latitude' => 12.5516, 'longitude' => -12.1749, 'chef_lieu' => 'Kédougou']
        ];

        foreach ($regions as $region) {
            DB::table('regions')->updateOrInsert(
                ['code' => $region['code']],
                [
                    'nom' => $region['nom'],
                    'latitude' => $region['latitude'],
                    'longitude' => $region['longitude'],
                    'chef_lieu' => $region['chef_lieu'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $this->command->info('13 régions du Sénégal insérées avec succès !');
    }
}
