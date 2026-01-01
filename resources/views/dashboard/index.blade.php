@extends('layouts.app')

@section('title', 'Dashboard - ONCC Sénégal')

@push('styles')
<style>
/* ===== Dashboard Premium Styles ===== */
.dashboard-header {
    background: linear-gradient(135deg, rgba(15, 118, 110, 0.95) 0%, rgba(14, 165, 233, 0.9) 100%);
    border-radius: 20px;
    padding: 2rem 2.5rem;
    margin-top: 1.5rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(15, 118, 110, 0.3);
}

.dashboard-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    border-radius: 50%;
}

.dashboard-header::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
}

.dashboard-header h1 {
    color: white;
    font-weight: 700;
    font-size: 2.2rem;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 1;
}

.dashboard-header p {
    color: rgba(255,255,255,0.9);
    font-size: 1.1rem;
    margin-bottom: 1rem;
    position: relative;
    z-index: 1;
}

.user-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    padding: 8px 16px;
    border-radius: 50px;
    color: white;
    font-weight: 500;
    position: relative;
    z-index: 1;
}

.user-badge .role-badge {
    background: rgba(255,255,255,0.3);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

/* Filter Bar */
.filter-bar {
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(20px);
    border-radius: 16px;
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid rgba(255,255,255,0.8);
}

[data-theme="dark"] .filter-bar {
    background: rgba(31, 41, 55, 0.95);
    border-color: rgba(75, 85, 99, 0.5);
}

.filter-bar .form-select {
    border-radius: 10px;
    border: 2px solid var(--gray-200);
    padding: 0.6rem 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.filter-bar .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.15);
}

/* Section Headers */
.section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid transparent;
    background: linear-gradient(90deg, var(--primary-color), transparent) border-box;
    -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
    mask-composite: exclude;
}

.section-header .section-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: white;
}

.section-header.climate .section-icon {
    background: linear-gradient(135deg, #0ea5e9, #06b6d4);
}

.section-header.economic .section-icon {
    background: linear-gradient(135deg, #10b981, #059669);
}

.section-header h5 {
    font-weight: 600;
    color: var(--text-color);
    margin: 0;
    font-size: 1.15rem;
}

/* Premium Indicator Cards */
.indicator-card {
    border-radius: 20px;
    padding: 0;
    overflow: hidden;
    position: relative;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: none;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    height: 100%;
}

.indicator-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.indicator-card .card-inner {
    padding: 1.5rem;
    position: relative;
    z-index: 2;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.indicator-card .card-bg-pattern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    opacity: 0.1;
    background-size: 100px 100px;
    z-index: 1;
}

.indicator-card .icon-wrapper {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1rem;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.indicator-card:hover .icon-wrapper {
    transform: rotate(-10deg) scale(1.1);
}

.indicator-card .card-label {
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.9;
    margin-bottom: 0.5rem;
}

.indicator-card .card-value {
    font-size: 2.2rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 0.25rem;
}

.indicator-card .card-unit {
    font-size: 0.8rem;
    opacity: 0.8;
    font-weight: 500;
}

.indicator-card .trend-indicator {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.2);
    font-size: 0.9rem;
}

/* Card Color Variants */
.indicator-card.drought {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
}

.indicator-card.flood {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    color: white;
}

.indicator-card.desert {
    background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%);
    color: white;
}

.indicator-card.loss {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}

.indicator-card.agriculture {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.indicator-card.fishing {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    color: white;
}

.indicator-card.tourism {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
}

.indicator-card.energy {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

/* Pulse Animation for Live Data */
.pulse-dot {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
}

/* Card Entry Animations */
.indicator-card {
    animation: cardEntry 0.6s ease forwards;
    opacity: 0;
}

.indicator-card:nth-child(1) { animation-delay: 0.1s; }
.indicator-card:nth-child(2) { animation-delay: 0.2s; }
.indicator-card:nth-child(3) { animation-delay: 0.3s; }
.indicator-card:nth-child(4) { animation-delay: 0.4s; }

@keyframes cardEntry {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}

/* Floating Particles Effect */
.particles-container {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: hidden;
    pointer-events: none;
    z-index: 0;
}

.particle {
    position: absolute;
    width: 4px;
    height: 4px;
    background: rgba(255,255,255,0.3);
    border-radius: 50%;
    animation: float 15s infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { transform: translateY(-100vh) rotate(720deg); opacity: 0; }
}
</style>
@endpush

@section('content')
<!-- Dashboard Header Premium -->
<div class="dashboard-header">
    <div class="particles-container">
        @for($i = 0; $i < 10; $i++)
        <div class="particle" style="left: {{ rand(0, 100) }}%; animation-delay: {{ rand(0, 10) }}s; animation-duration: {{ rand(10, 20) }}s;"></div>
        @endfor
    </div>
    <h1>
        <i class="fas fa-chart-network me-2"></i>
        Tableau de bord ONCC-SN
    </h1>
    <p>
        <span class="pulse-dot"></span>
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
        <div class="card-bg-pattern" style="background-image: url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 80 80\"><circle cx=\"40\" cy=\"40\" r=\"30\" fill=\"white\" fill-opacity=\"0.05\"/></svg>');"></div>
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
        <div class="card-bg-pattern" style="background-image: url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 80 80\"><path d=\"M0 40 Q20 20 40 40 T80 40\" stroke=\"white\" stroke-opacity=\"0.1\" fill=\"none\" stroke-width=\"2\"/></svg>');"></div>
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
        <div class="card-bg-pattern"></div>
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
        <div class="card-bg-pattern"></div>
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
        <div class="card-bg-pattern"></div>
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
        <div class="card-bg-pattern"></div>
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
        <div class="card-bg-pattern"></div>
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
        <div class="card-bg-pattern"></div>
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
                <div id="map" style="height: 450px; border-radius: 0 0 20px 20px;"></div>
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
                    <div class="header-icon pulse-icon">
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

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
/* Premium Cards */
.premium-card {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    transition: all 0.4s ease;
    background: var(--card-bg);
}

.premium-card:hover {
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.premium-header {
    padding: 1.25rem 1.5rem;
    border: none;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    color: white;
}

.premium-header h5 {
    color: white;
    font-weight: 600;
}

.premium-header small {
    color: rgba(255,255,255,0.8);
}

/* Map Card */
.map-header {
    background: linear-gradient(135deg, #0f766e 0%, #0ea5e9 100%);
}
.map-header .header-icon {
    background: rgba(255,255,255,0.2);
}

/* Losses Card */
.losses-header {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}
.losses-header .header-icon {
    background: rgba(255,255,255,0.2);
}

.losses-list {
    max-height: 250px;
    overflow-y: auto;
}

.loss-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    margin-bottom: 8px;
    border-radius: 12px;
    background: var(--gray-50);
    transition: all 0.3s ease;
    animation: slideIn 0.5s ease forwards;
    opacity: 0;
}

[data-theme="dark"] .loss-item {
    background: var(--gray-700);
}

.loss-item:hover {
    background: var(--gray-100);
    transform: translateX(5px);
}

[data-theme="dark"] .loss-item:hover {
    background: var(--gray-600);
}

@keyframes slideIn {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}

.loss-rank {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
}

.loss-info {
    flex: 1;
}

.loss-region {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-color);
    margin-bottom: 4px;
}

.loss-bar-container {
    height: 4px;
    background: var(--gray-200);
    border-radius: 2px;
    overflow: hidden;
}

[data-theme="dark"] .loss-bar-container {
    background: var(--gray-600);
}

.loss-bar {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #059669);
    border-radius: 2px;
    transition: width 1s ease;
}

.loss-value {
    font-weight: 700;
    color: #059669;
    font-size: 0.95rem;
}

/* Users Card */
.users-header {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}
.users-header .header-icon {
    background: rgba(255,255,255,0.2);
}

/* Actions Card */
.actions-header {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}
.actions-header .header-icon {
    background: rgba(255,255,255,0.2);
}

.pulse-icon {
    animation: pulseIcon 2s infinite;
}

@keyframes pulseIcon {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    border-radius: 16px;
    text-decoration: none;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
}

.action-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.action-btn:hover::before {
    opacity: 1;
}

.action-btn:hover {
    transform: translateY(-5px) scale(1.02);
    text-decoration: none;
}

.action-btn .action-icon {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    background: rgba(255,255,255,0.2);
    transition: all 0.3s ease;
}

.action-btn:hover .action-icon {
    transform: rotate(-10deg) scale(1.1);
}

.action-text {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.action-title {
    font-weight: 600;
    font-size: 1rem;
}

.action-desc {
    font-size: 0.85rem;
    opacity: 0.8;
}

.action-arrow {
    opacity: 0;
    transform: translateX(-10px);
    transition: all 0.3s ease;
}

.action-btn:hover .action-arrow {
    opacity: 1;
    transform: translateX(0);
}

/* Action Button Colors */
.action-data {
    background: linear-gradient(135deg, #0f766e, #14b8a6);
    color: white;
    box-shadow: 0 8px 25px rgba(15, 118, 110, 0.3);
}
.action-data:hover {
    box-shadow: 0 15px 35px rgba(15, 118, 110, 0.4);
    color: white;
}

.action-viz {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}
.action-viz:hover {
    box-shadow: 0 15px 35px rgba(16, 185, 129, 0.4);
    color: white;
}

.action-map {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: white;
    box-shadow: 0 8px 25px rgba(14, 165, 233, 0.3);
}
.action-map:hover {
    box-shadow: 0 15px 35px rgba(14, 165, 233, 0.4);
    color: white;
}

.action-admin {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
}
.action-admin:hover {
    box-shadow: 0 15px 35px rgba(245, 158, 11, 0.4);
    color: white;
}

/* Leaflet Popup Styling */
.leaflet-popup-content-wrapper {
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.leaflet-popup-content {
    margin: 1rem;
}

.popup-content h6 {
    color: var(--primary-color);
    font-weight: 700;
    margin-bottom: 0.75rem;
}

.popup-content .popup-stat {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--gray-200);
}

.popup-content .popup-stat:last-child {
    border-bottom: none;
}

.popup-stat-label {
    color: var(--gray-600);
    font-size: 0.85rem;
}

.popup-stat-value {
    font-weight: 600;
    color: var(--text-color);
}
</style>
@endpush

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
