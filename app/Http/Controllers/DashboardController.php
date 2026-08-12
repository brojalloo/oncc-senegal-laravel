<?php

namespace App\Http\Controllers;

use App\Models\Alerte;
use App\Models\DonneeClimatique;
use App\Models\DonneeEconomique;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // Dashboard principal
    public function index(Request $request)
    {
        $user = Auth::user();
        $regionId = $request->get('region_id');

        // Statistiques globales
        $stats = [
            'total_regions' => Region::count(),
            'total_donnees_climatiques' => DonneeClimatique::count(),
            'total_donnees_economiques' => DonneeEconomique::count(),
            'alertes_actives' => Alerte::where('date_fin', '>=', now())->count(),
        ];

        // Statistiques climatiques par indicateur
        $climateStats = DonneeClimatique::select(
            'type_indicateur',
            DB::raw('AVG(valeur) as moyenne'),
            DB::raw('COUNT(*) as total')
        )
            ->where('statut', 'valide')
            ->when($regionId, function ($query) use ($regionId) {
                return $query->where('region_id', $regionId);
            })
            ->groupBy('type_indicateur')
            ->get()
            ->keyBy('type_indicateur');

        // Statistiques économiques récentes
        $economicStats = DonneeEconomique::select(
            'secteur',
            DB::raw('SUM(valeur) as total'),
            DB::raw('AVG(valeur) as moyenne')
        )
            ->where('statut', 'valide')
            ->when($regionId, function ($query) use ($regionId) {
                return $query->where('region_id', $regionId);
            })
            ->groupBy('secteur')
            ->get()
            ->keyBy('secteur');

        // Pertes agricoles par région
        $agriculturalLosses = DonneeEconomique::select(
            'regions.nom as region',
            DB::raw('SUM(donnees_economiques.valeur) as pertes_total')
        )
            ->join('regions', 'donnees_economiques.region_id', '=', 'regions.id')
            ->where('donnees_economiques.secteur', 'agriculture')
            ->where('donnees_economiques.impact', 'negatif')
            ->where('donnees_economiques.statut', 'valide')
            ->groupBy('regions.id', 'regions.nom')
            ->orderBy('pertes_total', 'desc')
            ->limit(10)
            ->get();

        // Données pour la carte
        $mapData = Region::with(['donneesClimatiques' => function ($query) {
            $query->where('statut', 'valide')
                ->latest()
                ->limit(1);
        }])
            ->get()
            ->map(function ($region) {
                $climatData = $region->donneesClimatiques->groupBy('type_indicateur');

                return [
                    'region' => $region->nom,
                    'lat' => $region->latitude,
                    'lng' => $region->longitude,
                    'secheresse' => $climatData->get('secheresse')?->first()?->valeur,
                    'inondation' => $climatData->get('inondation')?->first()?->valeur,
                    'desertification' => $climatData->get('desertification')?->first()?->valeur,
                    'temperature' => $climatData->get('temperature')?->first()?->valeur,
                ];
            });

        // Statistiques utilisateurs par rôle
        $userStats = DB::table('users')
            ->select('role', DB::raw('COUNT(*) as count'))
            ->groupBy('role')
            ->get();

        // Récentes alertes
        $alertes = Alerte::with('region')
            ->where('date_fin', '>=', now())
            ->orderBy('niveau', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Liste des régions pour le filtre
        $regions = Region::orderBy('nom')->get();

        return view('dashboard.index', compact(
            'stats',
            'climateStats',
            'economicStats',
            'agriculturalLosses',
            'mapData',
            'userStats',
            'alertes',
            'user',
            'regions',
            'regionId'
        ));
    }

    // Visualisation climatique
    public function visualizationClimate(Request $request)
    {
        $regionId = $request->get('region_id');

        $climateEvolution = DonneeClimatique::select(
            'annee',
            'type_indicateur',
            DB::raw('AVG(valeur) as moyenne')
        )
            ->when($regionId, function ($query) use ($regionId) {
                return $query->where('region_id', $regionId);
            })
            ->groupBy('annee', 'type_indicateur')
            ->orderBy('annee')
            ->get();

        $climateStats = DonneeClimatique::select(
            'type_indicateur',
            DB::raw('COUNT(*) as count'),
            DB::raw('AVG(valeur) as moyenne'),
            DB::raw('MAX(valeur) as max'),
            DB::raw('MIN(valeur) as min')
        )
            ->when($regionId, function ($query) use ($regionId) {
                return $query->where('region_id', $regionId);
            })
            ->groupBy('type_indicateur')
            ->get();

        $regions = Region::all();

        return view('dashboard.visualization-climate', compact('climateEvolution', 'climateStats', 'regions', 'regionId'));
    }

    // Visualisation économique
    public function visualizationEconomic(Request $request)
    {
        $regionId = $request->get('region_id');

        $economicEvolution = DonneeEconomique::select(
            'annee',
            'secteur',
            DB::raw('SUM(valeur) as total')
        )
            ->when($regionId, function ($query) use ($regionId) {
                return $query->where('region_id', $regionId);
            })
            ->groupBy('annee', 'secteur')
            ->orderBy('annee')
            ->get();

        $economicImpacts = DonneeEconomique::select(
            'secteur',
            'impact',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(valeur) as total')
        )
            ->when($regionId, function ($query) use ($regionId) {
                return $query->where('region_id', $regionId);
            })
            ->groupBy('secteur', 'impact')
            ->get();

        $regions = Region::all();

        return view('dashboard.visualization-economic', compact('economicEvolution', 'economicImpacts', 'regions', 'regionId'));
    }

    // Cartographie
    public function cartography(Request $request)
    {
        // Récupérer l'année et l'indicateur depuis la requête
        $year = $request->input('year', date('Y'));
        $indicator = $request->input('indicator', 'secheresse');

        $regions = Region::with(['alertes' => function ($query) use ($year) {
            $query->whereYear('date_debut', $year)
                ->orWhereYear('date_fin', $year);
        }])->get();

        // Données des régions pour la carte avec indicateurs climatiques
        $regionsData = Region::select(
            'regions.id',
            'regions.nom',
            'regions.latitude',
            'regions.longitude',
            DB::raw('AVG(donnees_climatiques.valeur) as valeur'),
            DB::raw('MAX(donnees_climatiques.annee) as last_year'),
            DB::raw('COUNT(donnees_climatiques.id) as data_count')
        )
            ->leftJoin('donnees_climatiques', 'regions.id', '=', 'donnees_climatiques.region_id')
            ->where('donnees_climatiques.statut', 'valide')
            ->where('donnees_climatiques.type_indicateur', $indicator)
            ->where('donnees_climatiques.annee', $year)
            ->groupBy('regions.id', 'regions.nom', 'regions.latitude', 'regions.longitude')
            ->get();

        // Alertes actives pour l'année sélectionnée
        $alertes = Alerte::with('region')
            ->whereYear('date_debut', $year)
            ->orWhereYear('date_fin', $year)
            ->orderBy('date_debut', 'desc')
            ->take(50)
            ->get();

        return view('dashboard.cartography', compact('regions', 'regionsData', 'alertes', 'year', 'indicator'));
    }

    // API pour récupérer les données de la carte en AJAX
    public function getMapData(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $indicator = $request->input('indicator', 'secheresse');

        // Données des régions pour la carte
        $regionsData = Region::select(
            'regions.id',
            'regions.nom',
            'regions.latitude',
            'regions.longitude',
            DB::raw('AVG(donnees_climatiques.valeur) as valeur'),
            DB::raw('MAX(donnees_climatiques.annee) as last_year'),
            DB::raw('COUNT(donnees_climatiques.id) as data_count')
        )
            ->leftJoin('donnees_climatiques', 'regions.id', '=', 'donnees_climatiques.region_id')
            ->where('donnees_climatiques.statut', 'valide')
            ->where('donnees_climatiques.type_indicateur', $indicator)
            ->where('donnees_climatiques.annee', $year)
            ->groupBy('regions.id', 'regions.nom', 'regions.latitude', 'regions.longitude')
            ->get();

        // Alertes pour l'année sélectionnée
        $alertes = Alerte::with('region')
            ->where(function ($query) use ($year) {
                $query->whereYear('date_debut', $year)
                    ->orWhereYear('date_fin', $year);
            })
            ->orderBy('date_debut', 'desc')
            ->take(50)
            ->get();

        return response()->json([
            'success' => true,
            'regionsData' => $regionsData,
            'alertes' => $alertes,
            'year' => $year,
            'indicator' => $indicator,
        ]);
    }
}
