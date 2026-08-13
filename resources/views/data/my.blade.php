@extends('layouts.app')

@section('title', 'Mes Données')

@push('styles')
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header avec particules -->
    <div class="page-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); position: relative;">
        <h1>
            <i class="fas fa-database me-2"></i>
            Mes Données
        </h1>
        <p>
            <span class="pulse-dot"></span>
            Gérez et consultez l'ensemble de vos contributions à la plateforme
        </p>
        <div class="header-badge">
            <i class="fas fa-chart-bar me-1"></i>
            {{ $climatiques->total() + $economiques->total() }} contributions
        </div>
    </div>

    <!-- Statistiques avec design dashboard -->
    <div class="stats-grid mb-4">
        <div class="stat-card info">
            <div class="trend-badge">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-icon">
                <i class="fas fa-cloud-sun"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Données Climatiques</div>
                <div class="stat-value">{{ $climatiques->total() }}</div>
                <div class="stat-unit">Enregistrements</div>
            </div>
        </div>
        <div class="stat-card success">
            <div class="trend-badge">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Données Économiques</div>
                <div class="stat-value">{{ $economiques->total() }}</div>
                <div class="stat-unit">Enregistrements</div>
            </div>
        </div>
    </div>

    <!-- Carte principale avec onglets -->
    <div class="premium-card">
        <div class="card-body p-0">
            <!-- Onglets Premium -->
            <div class="premium-tabs">
                <button class="tab-btn active" id="climate-tab" data-bs-toggle="tab" 
                        data-bs-target="#climate" type="button" role="tab">
                    <i class="fas fa-cloud-sun"></i> 
                    <span>Données Climatiques</span>
                    <span class="badge-count">{{ $climatiques->total() }}</span>
                </button>
                <button class="tab-btn" id="economic-tab" data-bs-toggle="tab" 
                        data-bs-target="#economic" type="button" role="tab">
                    <i class="fas fa-chart-line"></i> 
                    <span>Données Économiques</span>
                    <span class="badge-count">{{ $economiques->total() }}</span>
                </button>
            </div>

            <!-- Contenu des onglets -->
            <div class="tab-content p-4">
                <!-- Données Climatiques -->
                <div class="tab-pane fade show active" id="climate" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0" style="font-weight: 600; color: var(--text-dark);">
                            <i class="fas fa-snowflake text-info me-2"></i>Mes Données Climatiques
                        </h5>
                        <a href="{{ route('data.climate.create') }}" class="btn-premium primary">
                            <i class="fas fa-plus"></i> Ajouter des données
                        </a>
                    </div>

                    @if($climatiques->count() > 0)
                        <div class="table-responsive">
                            <table class="premium-table glass-table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-map-marker-alt me-1"></i> Région</th>
                                        <th><i class="fas fa-calendar me-1"></i> Année</th>
                                        <th><i class="fas fa-tag me-1"></i> Indicateur</th>
                                        <th><i class="fas fa-chart-bar me-1"></i> Valeur</th>
                                        <th><i class="fas fa-ruler me-1"></i> Unité</th>
                                        <th><i class="fas fa-database me-1"></i> Source</th>
                                        <th><i class="fas fa-info-circle me-1"></i> Statut</th>
                                        <th><i class="fas fa-clock me-1"></i> Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($climatiques as $data)
                                        <tr>
                                            <td><strong>{{ $data->region->nom }}</strong></td>
                                            <td>{{ $data->annee }}</td>
                                            <td>
                                                <span class="badge-premium info">
                                                    {{ ucfirst(str_replace('_', ' ', $data->type_indicateur)) }}
                                                </span>
                                            </td>
                                            <td><strong>{{ number_format($data->valeur, 2) }}</strong></td>
                                            <td>{{ $data->unite ?? '-' }}</td>
                                            <td>{{ $data->source ?? '-' }}</td>
                                            <td>
                                                @if($data->statut === 'valide')
                                                    <span class="badge-premium success">
                                                        <i class="fas fa-check"></i> Validé
                                                    </span>
                                                @elseif($data->statut === 'rejete')
                                                    <span class="badge-premium danger">
                                                        <i class="fas fa-times"></i> Rejeté
                                                    </span>
                                                @else
                                                    <span class="badge-premium warning">
                                                        <i class="fas fa-clock"></i> En attente
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $data->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $climatiques->links() }}
                        </div>
                    @else
                        <div class="alert-premium info">
                            <div class="alert-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div>
                                <strong>Aucune donnée</strong>
                                <p class="mb-0">Vous n'avez pas encore ajouté de données climatiques.
                                    <a href="{{ route('data.climate.create') }}" class="alert-link">Cliquez ici pour commencer</a>.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Données Économiques -->
                <div class="tab-pane fade" id="economic" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0" style="font-weight: 600; color: var(--text-dark);">
                            <i class="fas fa-coins text-success me-2"></i>Mes Données Économiques
                        </h5>
                        <a href="{{ route('data.economic.create') }}" class="btn-premium success">
                            <i class="fas fa-plus"></i> Ajouter des données
                        </a>
                    </div>

                    @if($economiques->count() > 0)
                        <div class="table-responsive">
                            <table class="premium-table glass-table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-map-marker-alt me-1"></i> Région</th>
                                        <th><i class="fas fa-calendar me-1"></i> Année</th>
                                        <th><i class="fas fa-industry me-1"></i> Secteur</th>
                                        <th><i class="fas fa-balance-scale me-1"></i> Impact</th>
                                        <th><i class="fas fa-money-bill me-1"></i> Valeur (FCFA)</th>
                                        <th><i class="fas fa-database me-1"></i> Source</th>
                                        <th><i class="fas fa-info-circle me-1"></i> Statut</th>
                                        <th><i class="fas fa-clock me-1"></i> Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($economiques as $data)
                                        <tr>
                                            <td><strong>{{ $data->region->nom }}</strong></td>
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
                                            <td>{{ $data->source ?? '-' }}</td>
                                            <td>
                                                @if($data->statut === 'valide')
                                                    <span class="badge-premium success">
                                                        <i class="fas fa-check"></i> Validé
                                                    </span>
                                                @elseif($data->statut === 'rejete')
                                                    <span class="badge-premium danger">
                                                        <i class="fas fa-times"></i> Rejeté
                                                    </span>
                                                @else
                                                    <span class="badge-premium warning">
                                                        <i class="fas fa-clock"></i> En attente
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $data->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $economiques->links() }}
                        </div>
                    @else
                        <div class="alert-premium info">
                            <div class="alert-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div>
                                <strong>Aucune donnée</strong>
                                <p class="mb-0">Vous n'avez pas encore ajouté de données économiques.
                                    <a href="{{ route('data.economic.create') }}" class="alert-link">Cliquez ici pour commencer</a>.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
