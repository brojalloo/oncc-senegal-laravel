@extends('layouts.app')

@section('title', 'Dashboard - ONCC Sénégal')


@section('content')
<!-- Dashboard Header Premium -->
<div class="dashboard-header">
    <h1>
        <i class="fas fa-chart-network me-2"></i>
        Tableau de bord ONCC-SN
    </h1>
    <p>
        Observatoire Numérique du Changement Climatique et Économique du Sénégal
    </p>
    <div class="user-badge">
        <span class="role-badge">
            <i class="fas fa-shield-alt me-1"></i>{{ ucfirst($user->role) }}
        </span>
        <span>
            <i class="fas fa-user-circle me-1"></i>{{ $user->nom }}
        </span>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <form method="get" class="d-flex align-items-center gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-filter text-primary"></i>
            <label for="region_id" class="fw-semibold mb-0">Filtrer par région :</label>
        </div>
        <select name="region_id" id="region_id" class="form-select w-auto" onchange="this.form.submit()">
            <option value="">🌍 Toutes les régions</option>
            @foreach($regions as $region)
                <option value="{{ $region->id }}" {{ $regionId == $region->id ? 'selected' : '' }}>
                    📍 {{ $region->nom }}
                </option>
            @endforeach
        </select>
        @if($regionId)
            <a href="{{ route('dashboard') }}" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                <i class="fas fa-times me-1"></i>Réinitialiser
            </a>
        @endif
    </form>
</div>

<!-- Indicateurs climatiques -->
<div class="section-header climate">
    <div class="section-icon">
        <i class="fas fa-cloud-sun-rain"></i>
    </div>
    <h5>Indicateurs Climatiques</h5>
</div>

<div class="stats-grid">
    <div class="indicator-card drought">
        <div class="card-inner">
            <div class="trend-indicator">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="icon-wrapper">
                <i class="fas fa-sun"></i>
            </div>
            <div>
                <div class="card-label">Sécheresse</div>
                <div class="card-value">
                    {{ isset($climateStats['secheresse']) ? number_format($climateStats['secheresse']->moyenne, 1) : '0.0' }}
                </div>
                <div class="card-unit">Indice moyen</div>
            </div>
        </div>
    </div>
    
    <div class="indicator-card flood">
        <div class="card-inner">
            <div class="trend-indicator">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="icon-wrapper">
                <i class="fas fa-water"></i>
            </div>
            <div>
                <div class="card-label">Inondations</div>
                <div class="card-value">
                    {{ isset($climateStats['inondation']) ? number_format($climateStats['inondation']->moyenne, 1) : '0.0' }}
                </div>
                <div class="card-unit">km² affectés</div>
            </div>
        </div>
    </div>
    
    <div class="indicator-card desert">
        <div class="card-inner">
            <div class="trend-indicator">
                <i class="fas fa-arrow-right"></i>
            </div>
            <div class="icon-wrapper">
                <i class="fas fa-mountain-sun"></i>
            </div>
            <div>
                <div class="card-label">Désertification</div>
                <div class="card-value">
                    {{ isset($climateStats['desertification']) ? number_format($climateStats['desertification']->moyenne, 1) : '0.0' }}
                </div>
                <div class="card-unit">Progression annuelle</div>
            </div>
        </div>
    </div>
    
    <div class="indicator-card loss">
        <div class="card-inner">
            <div class="trend-indicator">
                <i class="fas fa-exclamation"></i>
            </div>
            <div class="icon-wrapper">
                <i class="fas fa-seedling"></i>
            </div>
            <div>
                <div class="card-label">Pertes agricoles</div>
                <div class="card-value">
                    {{ number_format($agriculturalLosses->sum('pertes_total') / 1000000, 1) }}M
                </div>
                <div class="card-unit">FCFA (millions)</div>
            </div>
        </div>
    </div>
</div>

<!-- Indicateurs économiques -->
<div class="section-header economic">
    <div class="section-icon">
        <i class="fas fa-coins"></i>
    </div>
    <h5>Indicateurs Économiques</h5>
</div>

<div class="stats-grid">
    <div class="indicator-card agriculture">
        <div class="card-inner">
            <div class="trend-indicator">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="icon-wrapper">
                <i class="fas fa-tractor"></i>
            </div>
            <div>
                <div class="card-label">Agriculture</div>
                <div class="card-value">
                    {{ isset($economicStats['agriculture']) ? number_format($economicStats['agriculture']->total / 1000000, 1) : '0' }}M
                </div>
                <div class="card-unit">FCFA (millions)</div>
            </div>
        </div>
    </div>
    
    <div class="indicator-card fishing">
        <div class="card-inner">
            <div class="trend-indicator">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="icon-wrapper">
                <i class="fas fa-fish"></i>
            </div>
            <div>
                <div class="card-label">Pêche</div>
                <div class="card-value">
                    {{ isset($economicStats['peche']) ? number_format($economicStats['peche']->total / 1000000, 1) : '0' }}M
                </div>
                <div class="card-unit">FCFA (millions)</div>
            </div>
        </div>
    </div>
    
    <div class="indicator-card tourism">
        <div class="card-inner">
            <div class="trend-indicator">
                <i class="fas fa-arrow-right"></i>
            </div>
            <div class="icon-wrapper">
                <i class="fas fa-plane-departure"></i>
            </div>
            <div>
                <div class="card-label">Tourisme</div>
                <div class="card-value">
                    {{ isset($economicStats['tourisme']) ? number_format($economicStats['tourisme']->total / 1000000, 1) : '0' }}M
                </div>
                <div class="card-unit">FCFA (millions)</div>
            </div>
        </div>
    </div>
    
    <div class="indicator-card energy">
        <div class="card-inner">
            <div class="trend-indicator">
                <i class="fas fa-bolt"></i>
            </div>
            <div class="icon-wrapper">
                <i class="fas fa-solar-panel"></i>
            </div>
            <div>
                <div class="card-label">Énergie</div>
                <div class="card-value">
                    {{ isset($economicStats['energie']) ? number_format($economicStats['energie']->total / 1000000, 1) : '0' }}M
                </div>
                <div class="card-unit">FCFA (millions)</div>
            </div>
        </div>
    </div>
</div>

<!-- Carte et données -->
<div class="row mt-4">
    <div class="col-lg-8">
        <div class="card premium-card map-card">
            <div class="card-header premium-header map-header">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="fas fa-globe-africa"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Carte Interactive du Sénégal</h5>
                        <small class="opacity-75">Indicateurs climatiques par région</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="map" style="height: 450px;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card premium-card losses-card mb-4">
            <div class="card-header premium-header losses-header">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Pertes Agricoles</h5>
                        <small class="opacity-75">Par région</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="losses-list">
                    @forelse($agriculturalLosses as $index => $loss)
                        <div class="loss-item" style="animation-delay: {{ $index * 0.1 }}s;">
                            <div class="loss-rank">{{ $index + 1 }}</div>
                            <div class="loss-info">
                                <div class="loss-region">{{ $loss->region }}</div>
                                <div class="loss-bar-container">
                                    <div class="loss-bar" style="width: {{ min(100, ($loss->pertes_total / ($agriculturalLosses->max('pertes_total') ?: 1)) * 100) }}%;"></div>
                                </div>
                            </div>
                            <div class="loss-value">
                                {{ number_format($loss->pertes_total / 1000000, 1) }}M
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-database fa-2x mb-2"></i>
                            <p>Aucune donnée disponible</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        
        <div class="card premium-card users-card">
            <div class="card-header premium-header users-header">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Utilisateurs</h5>
                        <small class="opacity-75">Répartition par rôle</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <canvas id="usersChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Actions rapides -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card premium-card actions-card">
            <div class="card-header premium-header actions-header">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Actions Rapides</h5>
                        <small class="opacity-75">Accédez rapidement aux fonctionnalités principales</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="actions-grid">
                    @if($user->role === 'chercheur' || $user->role === 'collectivite')
                        <a href="{{ route('data.climate.create') }}" class="action-btn action-data">
                            <div class="action-icon">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="action-text">
                                <span class="action-title">Saisir des données</span>
                                <span class="action-desc">Ajouter de nouvelles données</span>
                            </div>
                            <i class="fas fa-arrow-right action-arrow"></i>
                        </a>
                    @endif
                    
                    <a href="{{ route('visualization.climate') }}" class="action-btn action-viz">
                        <div class="action-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="action-text">
                            <span class="action-title">Visualiser</span>
                            <span class="action-desc">Graphiques et analyses</span>
                        </div>
                        <i class="fas fa-arrow-right action-arrow"></i>
                    </a>
                    
                    <a href="{{ route('cartography') }}" class="action-btn action-map">
                        <div class="action-icon">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <div class="action-text">
                            <span class="action-title">Cartographie</span>
                            <span class="action-desc">Explorer la carte interactive</span>
                        </div>
                        <i class="fas fa-arrow-right action-arrow"></i>
                    </a>
                    
                    @if($user->role === 'admin')
                        <a href="{{ route('admin.validation') }}" class="action-btn action-admin">
                            <div class="action-icon">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div class="action-text">
                                <span class="action-title">Administration</span>
                                <span class="action-desc">Gérer la plateforme</span>
                            </div>
                            <i class="fas fa-arrow-right action-arrow"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Données pour la carte
const mapData = @json($mapData);

// Initialiser la carte avec un style plus moderne
const map = L.map("map", {
    zoomControl: false
}).setView([14.4974, -14.4524], 7);

// Ajouter le contrôle de zoom en haut à droite
L.control.zoom({
    position: 'topright'
}).addTo(map);

// Utiliser une couche de tuiles plus moderne (CartoDB)
L.tileLayer("https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png", {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
    subdomains: 'abcd',
    maxZoom: 19
}).addTo(map);

// Icône personnalisée pour les marqueurs
const customIcon = L.divIcon({
    className: 'custom-marker',
    html: '<div style="background: linear-gradient(135deg, #0f766e, #0ea5e9); width: 30px; height: 30px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white; box-shadow: 0 3px 10px rgba(0,0,0,0.3);"></div>',
    iconSize: [30, 30],
    iconAnchor: [15, 30],
    popupAnchor: [0, -30]
});

// Ajouter les marqueurs pour chaque région
mapData.forEach(region => {
    if (region.lat && region.lng) {
        const popupContent = `
            <div class="popup-content">
                <h6><i class="fas fa-map-marker-alt me-2"></i>${region.region}</h6>
                <div class="popup-stat">
                    <span class="popup-stat-label"><i class="fas fa-sun text-warning me-2"></i>Sécheresse</span>
                    <span class="popup-stat-value">${region.secheresse || "N/A"}</span>
                </div>
                <div class="popup-stat">
                    <span class="popup-stat-label"><i class="fas fa-water text-info me-2"></i>Inondation</span>
                    <span class="popup-stat-value">${region.inondation || "N/A"}</span>
                </div>
                <div class="popup-stat">
                    <span class="popup-stat-label"><i class="fas fa-mountain text-secondary me-2"></i>Désertification</span>
                    <span class="popup-stat-value">${region.desertification || "N/A"}</span>
                </div>
                <div class="popup-stat">
                    <span class="popup-stat-label"><i class="fas fa-thermometer-half text-danger me-2"></i>Température</span>
                    <span class="popup-stat-value">${region.temperature || "N/A"}°C</span>
                </div>
            </div>
        `;
        
        L.marker([region.lat, region.lng], { icon: customIcon })
            .addTo(map)
            .bindPopup(popupContent, {
                maxWidth: 300,
                className: 'custom-popup'
            });
    }
});

// Graphique des utilisateurs par rôle avec style amélioré
const userStats = @json($userStats);
const userLabels = userStats.map(stat => {
    const roles = {
        "admin": "Administrateurs",
        "chercheur": "Chercheurs",
        "collectivite": "Collectivités",
        "public": "Public"
    };
    return roles[stat.role] || stat.role;
});
const userData = userStats.map(stat => stat.count);

const usersChart = new Chart(document.getElementById("usersChart"), {
    type: "doughnut",
    data: {
        labels: userLabels,
        datasets: [{
            data: userData,
            backgroundColor: [
                "rgba(239, 68, 68, 0.9)",
                "rgba(14, 165, 233, 0.9)",
                "rgba(245, 158, 11, 0.9)",
                "rgba(16, 185, 129, 0.9)"
            ],
            borderColor: [
                "rgb(239, 68, 68)",
                "rgb(14, 165, 233)",
                "rgb(245, 158, 11)",
                "rgb(16, 185, 129)"
            ],
            borderWidth: 2,
            hoverOffset: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
            legend: {
                position: "bottom",
                labels: {
                    padding: 20,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: {
                        size: 12,
                        weight: '500'
                    }
                }
            }
        },
        animation: {
            animateScale: true,
            animateRotate: true
        }
    }
});
</script>
@endpush
@endsection
