@extends('layouts.app')

@section('title', 'Ajouter des Données Climatiques')

@section('content')
<div class="container-fluid">
    <!-- Page Header avec particules -->
    <div class="page-header">
        <h1>
            <i class="fas fa-cloud-sun-rain me-2"></i>
            Ajouter des Données Climatiques
        </h1>
        <p>
            <span class="pulse-dot"></span>
            Saisissez les informations relatives aux indicateurs climatiques
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
        <div class="card-header">
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
            <form action="{{ route('data.climate.store') }}" method="POST" class="premium-form">
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
                                <i class="fas fa-thermometer-half text-warning"></i> 
                                Type d'Indicateur <span class="text-danger">*</span>
                            </label>
                            <select name="type_indicateur" id="type_indicateur" class="form-select @error('type_indicateur') is-invalid @enderror" required>
                                <option value="">📊 Sélectionner un indicateur</option>
                                <option value="secheresse" data-unite="indice" {{ old('type_indicateur') == 'secheresse' ? 'selected' : '' }}>☀️ Sécheresse</option>
                                <option value="inondation" data-unite="mm" {{ old('type_indicateur') == 'inondation' ? 'selected' : '' }}>🌊 Inondation</option>
                                <option value="desertification" data-unite="%" {{ old('type_indicateur') == 'desertification' ? 'selected' : '' }}>🏜️ Désertification</option>
                                <option value="temperature" data-unite="°C" {{ old('type_indicateur') == 'temperature' ? 'selected' : '' }}>🌡️ Température</option>
                                <option value="pluviometrie" data-unite="mm" {{ old('type_indicateur') == 'pluviometrie' ? 'selected' : '' }}>🌧️ Pluviométrie</option>
                                <option value="pollution_air" data-unite="µg/m³" {{ old('type_indicateur') == 'pollution_air' ? 'selected' : '' }}>💨 Pollution de l'air</option>
                            </select>
                            @error('type_indicateur')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-hashtag text-success"></i> 
                                Valeur <span class="text-danger">*</span>
                            </label>
                            <input type="number" step="0.01" name="valeur" class="form-control @error('valeur') is-invalid @enderror" 
                                   value="{{ old('valeur') }}" placeholder="0.00" required>
                            @error('valeur')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-ruler text-info"></i> 
                                Unité
                            </label>
                            <input type="text" name="unite" id="unite" class="form-control @error('unite') is-invalid @enderror" 
                                   value="{{ old('unite') }}" placeholder="Auto" readonly style="background-color: var(--oncc-sunk);">
                            @error('unite')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">
                        <i class="fas fa-database text-accent"></i> 
                        Source des Données
                    </label>
                    <input type="text" name="source" class="form-control @error('source') is-invalid @enderror" 
                           value="{{ old('source') }}" placeholder="ex: ANACIM, MEPA, CSE">
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
                    <button type="submit" class="btn-premium primary">
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