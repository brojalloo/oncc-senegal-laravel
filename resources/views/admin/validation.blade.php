@extends('layouts.app')

@section('title', 'Validation des Données')

@section('content')
<div class="container-fluid">
    <!-- Page Header avec particules -->
    <div class="page-header">
        <h1>
            <i class="fas fa-check-double me-2"></i>
            Validation des Données
        </h1>
        <p>
            <span class="pulse-dot"></span>
            Gérez les données en attente de validation
        </p>
        <div class="header-badge">
            <i class="fas fa-clock me-1"></i>
            {{ $climatiques->count() + $economiques->count() }} données en attente
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

    @if(session('error'))
        <div class="alert-premium danger mb-4">
            <div class="alert-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div>
                <strong>Erreur !</strong>
                <p class="mb-0">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Statistiques rapides avec design dashboard -->
    <div class="stats-grid mb-4">
        <div class="stat-card info">
            <div class="trend-badge">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-icon">
                <i class="fas fa-cloud-sun"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Données Climatiques</div>
                <div class="stat-value">{{ $climatiques->count() }}</div>
                <div class="stat-unit">En attente de validation</div>
            </div>
        </div>
        <div class="stat-card success">
            <div class="trend-badge">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-icon">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Données Économiques</div>
                <div class="stat-value">{{ $economiques->count() }}</div>
                <div class="stat-unit">En attente de validation</div>
            </div>
        </div>
    </div>

    <div class="premium-card">
        <div class="card-body p-0">
            <!-- Onglets Premium -->
            <ul class="nav premium-tabs" id="validationTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link tab-btn active" id="climate-tab" data-bs-toggle="tab" 
                            data-bs-target="#climate" type="button" role="tab" aria-controls="climate" aria-selected="true">
                        <i class="fas fa-cloud-sun"></i> 
                        <span>Données Climatiques</span>
                        <span class="badge-count">{{ $climatiques->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link tab-btn" id="economic-tab" data-bs-toggle="tab" 
                            data-bs-target="#economic" type="button" role="tab" aria-controls="economic" aria-selected="false">
                        <i class="fas fa-coins"></i> 
                        <span>Données Économiques</span>
                        <span class="badge-count">{{ $economiques->count() }}</span>
                    </button>
                </li>
            </ul>

            <!-- Contenu des onglets -->
            <div class="tab-content p-4" id="validationTabsContent">
                <!-- Données Climatiques -->
                <div class="tab-pane fade show active" id="climate" role="tabpanel" aria-labelledby="climate-tab">
                    @if($climatiques->count() > 0)
                        <div class="table-responsive">
                            <table class="premium-table glass-table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag me-1"></i> ID</th>
                                        <th><i class="fas fa-map-marker-alt me-1"></i> Région</th>
                                        <th><i class="fas fa-calendar me-1"></i> Année</th>
                                        <th><i class="fas fa-tag me-1"></i> Indicateur</th>
                                        <th><i class="fas fa-chart-bar me-1"></i> Valeur</th>
                                        <th><i class="fas fa-ruler me-1"></i> Unité</th>
                                        <th><i class="fas fa-user me-1"></i> Soumis par</th>
                                        <th><i class="fas fa-clock me-1"></i> Date</th>
                                        <th><i class="fas fa-cogs me-1"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($climatiques as $data)
                                        <tr>
                                            <td><strong>#{{ $data->id }}</strong></td>
                                            <td>{{ $data->region->nom }}</td>
                                            <td>{{ $data->annee }}</td>
                                            <td>
                                                <span class="badge-premium info">
                                                    {{ ucfirst(str_replace('_', ' ', $data->type_indicateur)) }}
                                                </span>
                                            </td>
                                            <td><strong>{{ number_format($data->valeur, 2) }}</strong></td>
                                            <td>{{ $data->unite ?? '-' }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div style="width: 30px; height: 30px; border-radius: 50%; background: var(--oncc-accent-soft); color: var(--oncc-accent); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.75rem;">
                                                        {{ $data->utilisateur ? strtoupper(substr($data->utilisateur->nom, 0, 1)) : 'S' }}
                                                    </div>
                                                    <span>{{ $data->utilisateur ? $data->utilisateur->nom : 'Système' }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $data->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    @if($data->commentaire)
                                                        <button type="button" class="btn-action info" 
                                                                data-bs-toggle="tooltip" 
                                                                title="{{ $data->commentaire }}">
                                                            <i class="fas fa-comment"></i>
                                                        </button>
                                                    @endif
                                                    <form action="{{ route('admin.validate', ['type' => 'climatique', 'id' => $data->id]) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn-action success" 
                                                                onclick="return confirm('Valider cette donnée ?')"
                                                                title="Valider">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.reject', ['type' => 'climatique', 'id' => $data->id]) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn-action danger" 
                                                                onclick="return confirm('Rejeter cette donnée ?')"
                                                                title="Rejeter">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert-premium success">
                            <div class="alert-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <strong>Tout est à jour !</strong>
                                <p class="mb-0">Aucune donnée climatique en attente de validation.</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Données Économiques -->
                <div class="tab-pane fade" id="economic" role="tabpanel" aria-labelledby="economic-tab">
                    @if($economiques->count() > 0)
                        <div class="table-responsive">
                            <table class="premium-table glass-table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag me-1"></i> ID</th>
                                        <th><i class="fas fa-map-marker-alt me-1"></i> Région</th>
                                        <th><i class="fas fa-calendar me-1"></i> Année</th>
                                        <th><i class="fas fa-industry me-1"></i> Secteur</th>
                                        <th><i class="fas fa-balance-scale me-1"></i> Impact</th>
                                        <th><i class="fas fa-money-bill me-1"></i> Valeur (FCFA)</th>
                                        <th><i class="fas fa-user me-1"></i> Soumis par</th>
                                        <th><i class="fas fa-clock me-1"></i> Date</th>
                                        <th><i class="fas fa-cogs me-1"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($economiques as $data)
                                        <tr>
                                            <td><strong>#{{ $data->id }}</strong></td>
                                            <td>{{ $data->region->nom }}</td>
                                            <td>{{ $data->annee }}</td>
                                            <td>
                                                <span class="badge-premium primary">
                                                    {{ ucfirst($data->secteur) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($data->impact === 'positif')
                                                    <span class="badge-premium success">
                                                        <i class="fas fa-arrow-up"></i> Positif
                                                    </span>
                                                @elseif($data->impact === 'negatif')
                                                    <span class="badge-premium danger">
                                                        <i class="fas fa-arrow-down"></i> Négatif
                                                    </span>
                                                @else
                                                    <span class="badge-premium secondary">
                                                        <i class="fas fa-minus"></i> Neutre
                                                    </span>
                                                @endif
                                            </td>
                                            <td><strong>{{ number_format($data->valeur, 0, ',', ' ') }}</strong></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div style="width: 30px; height: 30px; border-radius: 50%; background: var(--oncc-accent-soft); color: var(--oncc-accent); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.75rem;">
                                                        {{ $data->utilisateur ? strtoupper(substr($data->utilisateur->nom, 0, 1)) : 'S' }}
                                                    </div>
                                                    <span>{{ $data->utilisateur ? $data->utilisateur->nom : 'Système' }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $data->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    @if($data->commentaire)
                                                        <button type="button" class="btn-action info" 
                                                                data-bs-toggle="tooltip" 
                                                                title="{{ $data->commentaire }}">
                                                            <i class="fas fa-comment"></i>
                                                        </button>
                                                    @endif
                                                    <form action="{{ route('admin.validate', ['type' => 'economique', 'id' => $data->id]) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn-action success" 
                                                                onclick="return confirm('Valider cette donnée ?')"
                                                                title="Valider">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.reject', ['type' => 'economique', 'id' => $data->id]) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn-action danger" 
                                                                onclick="return confirm('Rejeter cette donnée ?')"
                                                                title="Rejeter">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert-premium success">
                            <div class="alert-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <strong>Tout est à jour !</strong>
                                <p class="mb-0">Aucune donnée économique en attente de validation.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Activer les tooltips Bootstrap
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})
</script>
@endpush
@endsection
