@extends('layouts.app')

@section('title', 'Rapports système')

@section('content')
<div class="container-fluid">
    <!-- Page Header avec particules -->
    <div class="page-header">
        <h1>
            <i class="fas fa-chart-pie me-2"></i>
            Rapports Système
        </h1>
        <p>
            <span class="pulse-dot"></span>
            Statistiques et rapports détaillés de l'application ONCC-SN
        </p>
        <div class="header-badge">
            <i class="fas fa-analytics me-1"></i>
            Rapports & Analytiques
        </div>
    </div>

    <div class="row">
        <!-- Statistiques des utilisateurs -->
        <div class="col-lg-6 mb-4">
            <div class="premium-card h-100">
                <div class="card-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h5>Statistiques des Utilisateurs</h5>
                            <small>Vue d'ensemble des comptes</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-6">
                            <div class="stat-mini">
                                <div class="stat-value" style="font-size: 1.8rem;">{{ $stats['users']['total'] }}</div>
                                <small class="text-muted">Total utilisateurs</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-mini">
                                <div class="stat-value" style="font-size: 1.8rem;">{{ $stats['users']['active'] }}</div>
                                <small class="text-muted">Utilisateurs actifs</small>
                            </div>
                        </div>
                    </div>

                    @if(!empty($stats['users']['by_role']))
                    <h6 class="mb-3"><i class="fas fa-user-tag me-2"></i>Répartition par rôle :</h6>
                    <div class="mb-2">
                        @foreach($stats['users']['by_role'] as $roleData)
                            @php
                                $roleLabels = [
                                    'admin' => 'Administrateurs',
                                    'chercheur' => 'Chercheurs',
                                    'collectivite' => 'Collectivités',
                                    'public' => 'Public'
                                ];
                                $badgeClass = [
                                    'admin' => 'danger',
                                    'chercheur' => 'info',
                                    'collectivite' => 'warning',
                                    'public' => 'secondary'
                                ];
                            @endphp
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded bg-sunk">
                                <span><i class="fas fa-circle text-{{ $badgeClass[$roleData->role] ?? 'secondary' }} me-2" style="font-size: 0.5rem;"></i>{{ $roleLabels[$roleData->role] ?? $roleData->role }}</span>
                                <span class="badge-premium {{ $badgeClass[$roleData->role] ?? 'secondary' }}">{{ $roleData->count }}</span>
                            </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistiques des données -->
        <div class="col-lg-6 mb-4">
            <div class="premium-card h-100">
                <div class="card-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <div>
                            <h5>Statistiques des Données</h5>
                            <small>Volume des données collectées</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-6">
                            <div class="stat-mini">
                                <div class="stat-value" style="font-size: 1.8rem;">{{ $stats['climate']['total'] }}</div>
                                <small class="text-muted">Données climatiques</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-mini">
                                <div class="stat-value" style="font-size: 1.8rem;">{{ $stats['economic']['total'] }}</div>
                                <small class="text-muted">Données économiques</small>
                            </div>
                        </div>
                    </div>

                    @if(!empty($stats['climate']['by_indicator']))
                    <h6 class="mb-3"><i class="fas fa-tags me-2"></i>Par indicateur climatique :</h6>
                    <div style="max-height: 200px; overflow-y: auto;">
                        @foreach($stats['climate']['by_indicator'] as $item)
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded bg-sunk">
                                <small>{{ ucfirst($item->type_indicateur) }}</small>
                                <span class="badge-premium success">{{ $item->count }}</span>
                            </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations système -->
        <div class="col-lg-6 mb-4">
            <div class="premium-card h-100">
                <div class="card-header">
                    <div class="header-content">
                        <div class="header-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-server"></i>
                        </div>
                        <div>
                            <h5>Informations Système</h5>
                            <small class="opacity-75">Environnement technique</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center p-3 mb-2 rounded bg-sunk">
                        <span><i class="fab fa-php text-info me-2"></i><strong>Version PHP</strong></span>
                        <span class="badge-premium info">{{ $stats['system']['php_version'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-3 mb-2 rounded bg-sunk">
                        <span><i class="fab fa-laravel text-danger me-2"></i><strong>Version Laravel</strong></span>
                        <span class="badge-premium danger">{{ $stats['system']['laravel_version'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-3 mb-2 rounded bg-sunk">
                        <span><i class="fas fa-database text-success me-2"></i><strong>Base de données</strong></span>
                        <span class="text-muted">{{ $stats['system']['database'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-3 mb-2 rounded bg-sunk">
                        <span><i class="fas fa-hdd text-warning me-2"></i><strong>Taille BDD</strong></span>
                        <span class="badge-premium warning">{{ $stats['system']['database_size'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-3 rounded bg-sunk">
                        <span><i class="fas fa-clock text-secondary me-2"></i><strong>Date du rapport</strong></span>
                        <span class="text-muted">{{ now()->format('d/m/Y H:i:s') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions disponibles -->
        <div class="col-lg-6 mb-4">
            <div class="premium-card h-100">
                <div class="card-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div>
                            <h5>Actions Disponibles</h5>
                            <small>Raccourcis d'administration</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="{{ route('admin.logs') }}" class="btn-premium primary" style="text-align: center; text-decoration: none;">
                            <i class="fas fa-clipboard-list me-2"></i>
                            Consulter les logs système
                        </a>
                        <a href="{{ route('admin.users') }}" class="btn-premium success" style="text-align: center; text-decoration: none;">
                            <i class="fas fa-users me-2"></i>
                            Gérer les utilisateurs
                        </a>
                        <a href="{{ route('data.my') }}" class="btn-premium warning" style="text-align: center; text-decoration: none;">
                            <i class="fas fa-database me-2"></i>
                            Voir les données système
                        </a>
                        <a href="{{ route('admin.emails') }}" class="btn-premium info" style="text-align: center; text-decoration: none;">
                            <i class="fas fa-envelope me-2"></i>
                            Gestion des emails
                        </a>
                        <button class="btn-premium secondary" onclick="window.print()" style="border: none;">
                            <i class="fas fa-print me-2"></i>
                            Imprimer ce rapport
                        </button>
                    </div>

                    <div class="text-center mt-4 p-3 rounded bg-sunk">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Ce rapport est généré automatiquement
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="premium-card">
                <div class="card-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div>
                            <h5>Répartition par Rôle</h5>
                            <small>Distribution des utilisateurs</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="rolesChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="premium-card">
                <div class="card-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div>
                            <h5>Données par Indicateur</h5>
                            <small>Répartition des données climatiques</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="climateChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Graphique des rôles
const rolesCtx = document.getElementById('rolesChart').getContext('2d');
const rolesChart = new Chart(rolesCtx, {
    type: 'doughnut',
    data: {
        labels: @json($stats['users']['by_role']->pluck('role')),
        datasets: [{
            data: @json($stats['users']['by_role']->pluck('count')),
            backgroundColor: [
                'rgba(239, 68, 68, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(6, 182, 212, 0.8)',
                'rgba(107, 114, 128, 0.8)'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            }
        },
        cutout: '60%'
    }
});

// Graphique des données climatiques
const climateCtx = document.getElementById('climateChart').getContext('2d');
const climateChart = new Chart(climateCtx, {
    type: 'bar',
    data: {
        labels: @json($stats['climate']['by_indicator']->pluck('type_indicateur')),
        datasets: [{
            label: 'Nombre de données',
            data: @json($stats['climate']['by_indicator']->pluck('count')),
            backgroundColor: 'rgba(16, 185, 129, 0.8)',
            borderColor: 'rgba(16, 185, 129, 1)',
            borderWidth: 0,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    display: true,
                    color: 'rgba(0,0,0,0.05)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});
</script>
@endpush
@endsection
