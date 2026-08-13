@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header avec particules -->
    <div class="page-header admin-header">
        <h1>
            <i class="fas fa-shield-alt me-2"></i>
            Tableau de bord administrateur
        </h1>
        <p>
            <span class="pulse-dot"></span>
            Gérez et surveillez l'ensemble de la plateforme ONCC-SN
        </p>
        <div class="header-badge">
            <i class="fas fa-user-shield me-1"></i>
            Mode Administrateur
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="trend-badge">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-label">Utilisateurs</div>
            <div class="stat-value">{{ $stats['users'] }}</div>
            <div class="stat-unit">Total inscrits</div>
            <a href="{{ route('admin.users') }}" class="stretched-link"></a>
        </div>
        
        <div class="stat-card success">
            <div class="trend-badge">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-icon">
                <i class="fas fa-cloud-sun"></i>
            </div>
            <div class="stat-label">Données climatiques</div>
            <div class="stat-value">{{ $stats['climate_data'] }}</div>
            <div class="stat-unit">Enregistrements</div>
            <a href="{{ route('data.my') }}" class="stretched-link"></a>
        </div>
        
        <div class="stat-card warning">
            <div class="trend-badge">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div class="stat-label">Données économiques</div>
            <div class="stat-value">{{ $stats['economic_data'] }}</div>
            <div class="stat-unit">Enregistrements</div>
            <a href="{{ route('data.my') }}" class="stretched-link"></a>
        </div>
        
        <div class="stat-card danger">
            <div class="trend-badge">
                <i class="fas fa-bell"></i>
            </div>
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-label">Alertes</div>
            <div class="stat-value">{{ $stats['alerts'] }}</div>
            <div class="stat-unit">Actives</div>
            <a href="{{ route('cartography') }}" class="stretched-link"></a>
        </div>
    </div>
    
    <!-- Two Columns -->
    <div class="row">
        <!-- Actions rapides -->
        <div class="col-lg-6 mb-4">
            <div class="premium-card">
                <div class="card-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <div>
                            <h5>Actions rapides</h5>
                            <small>Accès aux fonctionnalités principales</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="actions-grid">
                        <a href="{{ route('register') }}" class="action-btn primary">
                            <div class="action-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="action-text">
                                <span class="action-title">Ajouter utilisateur</span>
                                <span class="action-desc">Créer un nouveau compte</span>
                            </div>
                        </a>
                        <a href="{{ route('data.climate.create') }}" class="action-btn success">
                            <div class="action-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="action-text">
                                <span class="action-title">Ajouter données</span>
                                <span class="action-desc">Saisir des données</span>
                            </div>
                        </a>
                        <a href="{{ route('admin.reports') }}" class="action-btn info">
                            <div class="action-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="action-text">
                                <span class="action-title">Rapports</span>
                                <span class="action-desc">Voir les rapports</span>
                            </div>
                        </a>
                        <a href="{{ route('admin.logs') }}" class="action-btn warning">
                            <div class="action-icon">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <div class="action-text">
                                <span class="action-title">Logs système</span>
                                <span class="action-desc">Journaux d'activité</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Statut système -->
            <div class="premium-card mt-4">
                <div class="card-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-server"></i>
                        </div>
                        <div>
                            <h5>Statut système</h5>
                            <small>Informations techniques</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded" style="background: var(--gray-50);">
                        <span class="fw-semibold"><i class="fab fa-php me-2 text-primary"></i>Version PHP</span>
                        <span class="badge-premium info">{{ $systemInfo['php_version'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded" style="background: var(--gray-50);">
                        <span class="fw-semibold"><i class="fab fa-laravel me-2 text-danger"></i>Version Laravel</span>
                        <span class="badge-premium danger">{{ $systemInfo['laravel_version'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-3 rounded" style="background: var(--gray-50);">
                        <span class="fw-semibold"><i class="fas fa-database me-2 text-success"></i>Base de données</span>
                        <span class="badge-premium success">{{ $systemInfo['database_size'] }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Derniers utilisateurs -->
        <div class="col-lg-6 mb-4">
            <div class="premium-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h5>Derniers utilisateurs</h5>
                            <small>Inscriptions récentes</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.users') }}" class="btn btn-light btn-sm rounded-pill px-3">
                        <i class="fas fa-eye me-1"></i>Voir tout
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table premium-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentUsers as $user)
                                    <tr>
                                        <td><strong>{{ $user->name }}</strong></td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->role == 'admin')
                                                <span class="badge-premium danger"><i class="fas fa-crown"></i> Admin</span>
                                            @elseif($user->role == 'chercheur')
                                                <span class="badge-premium warning"><i class="fas fa-flask"></i> Chercheur</span>
                                            @elseif($user->role == 'collectivite')
                                                <span class="badge-premium info"><i class="fas fa-building"></i> Collectivité</span>
                                            @else
                                                <span class="badge-premium primary"><i class="fas fa-user"></i> {{ $user->role }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->email_verified_at)
                                                <span class="badge-premium success"><i class="fas fa-check"></i> Vérifié</span>
                                            @else
                                                <span class="badge-premium warning"><i class="fas fa-clock"></i> En attente</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">
                                            <div class="empty-state py-3">
                                                <i class="fas fa-users" style="font-size: 2rem;"></i>
                                                <p class="mb-0 mt-2">Aucun utilisateur trouvé</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Dernières activités -->
            <div class="premium-card mt-4">
                <div class="card-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <div>
                            <h5>Activités récentes</h5>
                            <small>Journal des dernières actions</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="premium-timeline">
                        @forelse($recentActivities as $activity)
                            <div class="timeline-item">
                                <small class="text-muted d-block mb-1">
                                    <i class="fas fa-clock me-1"></i>{{ $activity['time'] }}
                                </small>
                                <p class="mb-0 fw-medium">{{ $activity['message'] }}</p>
                            </div>
                        @empty
                            <div class="timeline-item">
                                <small class="text-muted">Aucune activité récente</small>
                                <p class="mb-0">Le système est en attente d'activités</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
