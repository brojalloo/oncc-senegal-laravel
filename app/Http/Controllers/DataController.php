<?php

namespace App\Http\Controllers;

use App\Models\DonneeClimatique;
use App\Models\DonneeEconomique;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataController extends Controller
{
    // Formulaire d'ajout de données climatiques
    public function createClimate()
    {
        $regions = Region::all();
        return view('data.create-climate', compact('regions'));
    }

    // Enregistrer les données climatiques
    public function storeClimate(Request $request)
    {
        $request->validate([
            'region_id' => 'required|exists:regions,id',
            'annee' => 'required|integer|min:1900|max:' . (date('Y') + 10),
            'type_indicateur' => 'required|in:secheresse,inondation,desertification,temperature,pluviometrie',
            'valeur' => 'required|numeric',
            'unite' => 'nullable|string|max:50',
            'source' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string',
        ]);

        DonneeClimatique::create([
            'region_id' => $request->region_id,
            'annee' => $request->annee,
            'type_indicateur' => $request->type_indicateur,
            'valeur' => $request->valeur,
            'unite' => $request->unite,
            'source' => $request->source,
            'commentaire' => $request->commentaire,
            'utilisateur_id' => Auth::id(),
        ]);

        return redirect()->route('data.climate.create')
                       ->with('success', 'Données climatiques ajoutées avec succès.');
    }

    // Formulaire d'ajout de données économiques
    public function createEconomic()
    {
        $regions = Region::all();
        return view('data.create-economic', compact('regions'));
    }

    // Enregistrer les données économiques
    public function storeEconomic(Request $request)
    {
        $request->validate([
            'region_id' => 'required|exists:regions,id',
            'annee' => 'required|integer|min:1900|max:' . (date('Y') + 10),
            'secteur' => 'required|in:agriculture,peche,tourisme,energie,elevage,foret',
            'indicateur' => 'required|string|max:100',
            'valeur' => 'required|numeric',
            'unite' => 'nullable|string|max:50',
            'impact' => 'nullable|in:positif,negatif,neutre',
            'description' => 'nullable|string',
        ]);

        DonneeEconomique::create([
            'region_id' => $request->region_id,
            'annee' => $request->annee,
            'secteur' => $request->secteur,
            'indicateur' => $request->indicateur,
            'valeur' => $request->valeur,
            'unite' => $request->unite ?? 'FCFA',
            'impact' => $request->impact,
            'description' => $request->description,
            'utilisateur_id' => Auth::id(),
        ]);

        return redirect()->route('data.economic.create')
                       ->with('success', 'Données économiques ajoutées avec succès.');
    }

    // Liste des données de l'utilisateur
    public function myData()
    {
        $climatiques = DonneeClimatique::with('region')
                                     ->where('utilisateur_id', Auth::id())
                                     ->orderBy('created_at', 'desc')
                                     ->paginate(10, ['*'], 'climatiques');

        $economiques = DonneeEconomique::with('region')
                                     ->where('utilisateur_id', Auth::id())
                                     ->orderBy('created_at', 'desc')
                                     ->paginate(10, ['*'], 'economiques');

        return view('data.my', compact('climatiques', 'economiques'));
    }
}
