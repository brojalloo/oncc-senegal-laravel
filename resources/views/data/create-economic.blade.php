@extends('layouts.app')

@section('title', 'Ajouter des Données Économiques')

@push('styles')
<style>
.premium-card .card-body {
    padding: 2rem 2.5rem;
}
@media (max-width: 768px) {
    .premium-card .card-body {
        padding: 1.5rem;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header avec particules -->
    <div class="page-header economic-header" style="position: relative;">
        <div class="particles-container">
            @for($i = 0; $i < 8; $i++)
            <div class="particle" style="left: {{ rand(0, 100) }}%; animation-delay: {{ rand(0, 10) }}s; animation-duration: {{ rand(10, 20) }}s;"></div>
            @endfor
        </div>
        <h1>
            <i class="fas fa-coins me-2"></i>
            Ajouter des Données Économiques
        </h1>
        <p>
            <span class="pulse-dot"></span>
            Saisissez les informations relatives aux impacts économiques par secteur
        </p>
        <div class="header-badge">
            <i class="fas fa-edit me-1"></i>
            Nouveau enregistrement
        </div>
    </div>

    @if(session('success'))
        <div class="alert-premium success mb-4">
            <div class="alert-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <strong>Succès !</strong>
                <p class="mb-0">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="premium-card">
        <div class="card-header gradient-success">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fas fa-edit"></i>
                </div>
                <div>
                    <h5>Formulaire de saisie</h5>
                    <small>Remplissez tous les champs requis (*)</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('data.economic.store') }}" method="POST" class="premium-form">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-map-marker-alt text-danger"></i> 
                                Région <span class="text-danger">*</span>
                            </label>
                            <select name="region_id" class="form-select @error('region_id') is-invalid @enderror" required>
                                <option value="">🌍 Sélectionner une région</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->id }}" {{ old('region_id') == $region->id ? 'selected' : '' }}>
                                        📍 {{ $region->nom }}
                                    </option>
                                @endforeach
                            </select>
                            @error('region_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-calendar-alt text-primary"></i> 
                                Année <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="annee" class="form-control @error('annee') is-invalid @enderror" 
                                   value="{{ old('annee', date('Y')) }}" min="1900" max="{{ date('Y') + 10 }}" required>
                            @error('annee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-industry text-success"></i> 
                                Secteur <span class="text-danger">*</span>
                            </label>
                            <select name="secteur" class="form-select @error('secteur') is-invalid @enderror" required>
                                <option value="">📊 Sélectionner un secteur</option>
                                <option value="agriculture" {{ old('secteur') == 'agriculture' ? 'selected' : '' }}>🚜 Agriculture</option>
                                <option value="peche" {{ old('secteur') == 'peche' ? 'selected' : '' }}>🐟 Pêche</option>
                                <option value="tourisme" {{ old('secteur') == 'tourisme' ? 'selected' : '' }}>✈️ Tourisme</option>
                                <option value="energie" {{ old('secteur') == 'energie' ? 'selected' : '' }}>⚡ Énergie</option>
                                <option value="elevage" {{ old('secteur') == 'elevage' ? 'selected' : '' }}>🐄 Élevage</option>
                                <option value="foret" {{ old('secteur') == 'foret' ? 'selected' : '' }}>🌲 Forêt</option>
                            </select>
                            @error('secteur')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-balance-scale text-warning"></i> 
                                Impact <span class="text-danger">*</span>
                            </label>
                            <select name="impact" class="form-select @error('impact') is-invalid @enderror" required>
                                <option value="">📈 Sélectionner le type d'impact</option>
                                <option value="positif" {{ old('impact') == 'positif' ? 'selected' : '' }}>✅ Positif</option>
                                <option value="negatif" {{ old('impact') == 'negatif' ? 'selected' : '' }}>❌ Négatif</option>
                                <option value="neutre" {{ old('impact') == 'neutre' ? 'selected' : '' }}>➖ Neutre</option>
                            </select>
                            @error('impact')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-tag text-info"></i> 
                                Type d'Indicateur <span class="text-danger">*</span>
                            </label>
                            <select name="type_indicateur" id="type_indicateur" class="form-select @error('type_indicateur') is-invalid @enderror" required>
                                <option value="">📊 Sélectionner un indicateur</option>
                                <option value="pertes_agricoles" data-unite="FCFA" {{ old('type_indicateur') == 'pertes_agricoles' ? 'selected' : '' }}>🌾 Pertes agricoles</option>
                                <option value="pib_regional" data-unite="Milliards FCFA" {{ old('type_indicateur') == 'pib_regional' ? 'selected' : '' }}>📈 PIB régional</option>
                                <option value="revenus_peche" data-unite="FCFA" {{ old('type_indicateur') == 'revenus_peche' ? 'selected' : '' }}>🐟 Revenus de la pêche</option>
                                <option value="revenus_tourisme" data-unite="FCFA" {{ old('type_indicateur') == 'revenus_tourisme' ? 'selected' : '' }}>✈️ Revenus du tourisme</option>
                                <option value="couts_energie" data-unite="FCFA" {{ old('type_indicateur') == 'couts_energie' ? 'selected' : '' }}>⚡ Coûts énergétiques</option>
                                <option value="pertes_elevage" data-unite="FCFA" {{ old('type_indicateur') == 'pertes_elevage' ? 'selected' : '' }}>🐄 Pertes élevage</option>
                                <option value="production_forestiere" data-unite="FCFA" {{ old('type_indicateur') == 'production_forestiere' ? 'selected' : '' }}>🌲 Production forestière</option>
                                <option value="investissements_climat" data-unite="FCFA" {{ old('type_indicateur') == 'investissements_climat' ? 'selected' : '' }}>💰 Investissements climat</option>
                                <option value="emplois_verts" data-unite="Emplois" {{ old('type_indicateur') == 'emplois_verts' ? 'selected' : '' }}>👷 Emplois verts</option>
                                <option value="exportations" data-unite="FCFA" {{ old('type_indicateur') == 'exportations' ? 'selected' : '' }}>📦 Exportations</option>
                            </select>
                            @error('type_indicateur')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-money-bill-wave text-success"></i> 
                                Valeur <span class="text-danger">*</span>
                            </label>
                            <input type="number" step="0.01" name="valeur" class="form-control @error('valeur') is-invalid @enderror" 
                                   value="{{ old('valeur') }}" placeholder="Montant" required>
                            @error('valeur')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-ruler text-warning"></i> 
                                Unité
                            </label>
                            <input type="text" name="unite" id="unite" class="form-control @error('unite') is-invalid @enderror" 
                                   value="{{ old('unite') }}" placeholder="Auto" readonly style="background-color: #f8fafc;">
                            @error('unite')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">
                        <i class="fas fa-database text-purple"></i> 
                        Source des Données
                    </label>
                    <input type="text" name="source" class="form-control @error('source') is-invalid @enderror" 
                           value="{{ old('source') }}" placeholder="ex: ANSD, Ministère de l'Économie">
                    @error('source')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">
                        <i class="fas fa-comment-alt text-secondary"></i> 
                        Commentaire
                    </label>
                    <textarea name="commentaire" class="form-control @error('commentaire') is-invalid @enderror" 
                              rows="4" placeholder="Informations complémentaires...">{{ old('commentaire') }}</textarea>
                    @error('commentaire')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn-premium success">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeIndicateur = document.getElementById('type_indicateur');
    const uniteInput = document.getElementById('unite');
    
    // Fonction pour mettre à jour l'unité
    function updateUnite() {
        const selectedOption = typeIndicateur.options[typeIndicateur.selectedIndex];
        const unite = selectedOption.getAttribute('data-unite');
        
        if (unite) {
            uniteInput.value = unite;
            uniteInput.style.backgroundColor = '#f0fdf4';
            uniteInput.style.borderColor = '#10b981';
            uniteInput.style.color = '#059669';
            uniteInput.style.fontWeight = '600';
        } else {
            uniteInput.value = '';
            uniteInput.style.backgroundColor = '#f8fafc';
            uniteInput.style.borderColor = '';
            uniteInput.style.color = '';
            uniteInput.style.fontWeight = '';
        }
    }
    
    // Écouter le changement de sélection
    typeIndicateur.addEventListener('change', updateUnite);
    
    // Appliquer au chargement si une valeur est déjà sélectionnée
    if (typeIndicateur.value) {
        updateUnite();
    }
});
</script>
@endpush