@extends('layouts.app')

@section('title', 'Visualisation Climatique')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/premium-design.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header avec particules -->
    <div class="page-header climate-header" style="position: relative;">
        <div class="particles-container">
            @for($i = 0; $i < 8; $i++)
            <div class="particle" style="left: {{ rand(0, 100) }}%; animation-delay: {{ rand(0, 10) }}s; animation-duration: {{ rand(10, 20) }}s;"></div>
            @endfor
        </div>
        <h1>
            <i class="fas fa-cloud-sun-rain me-2"></i>
            Visualisation des Données Climatiques
        </h1>
        <p>
            <span class="pulse-dot"></span>
            Analyse et évolution des indicateurs climatiques au Sénégal
        </p>
        <div class="header-badge">
            <i class="fas fa-database me-1"></i>
            Données climatiques
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-filter text-info"></i>
                <label for="region_id" class="fw-semibold mb-0">Filtrer par région :</label>
            </div>
            <select name="region_id" class="form-select w-auto" onchange="this.form.submit()">
                <option value="">🌍 Toutes les régions</option>
                @foreach($regions as $region)
                    <option value="{{ $region->id }}" {{ $regionId == $region->id ? 'selected' : '' }}>
                        📍 {{ $region->nom }}
                    </option>
                @endforeach
            </select>
            @if($regionId)
                <a href="{{ route('visualization.climate') }}" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                    <i class="fas fa-times me-1"></i>Réinitialiser
                </a>
            @endif
        </form>
    </div>

    <div class="row">
        <!-- Évolution temporelle -->
        <div class="col-lg-8 mb-4">
            <div class="premium-card">
                <div class="card-header gradient-info">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                            <h5>Évolution des Indicateurs Climatiques</h5>
                            <small>Tendances annuelles par type d'indicateur</small>
                        </div>
                    </div>
                </div>
                <div class="card-body chart-container">
                    <canvas id="climateEvolutionChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Statistiques par indicateur -->
        <div class="col-lg-4 mb-4">
            <div class="premium-card">
                <div class="card-header gradient-purple">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div>
                            <h5>Répartition</h5>
                            <small>Par type d'indicateur</small>
                        </div>
                    </div>
                </div>
                <div class="card-body chart-container">
                    <canvas id="climateStatsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Tableau statistiques détaillées -->
        <div class="col-12">
            <div class="premium-card">
                <div class="card-header gradient-dark">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-table"></i>
                        </div>
                        <div>
                            <h5>Statistiques Détaillées</h5>
                            <small>Résumé des données climatiques</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table premium-table">
                            <thead>
                                <tr>
                                    <th>Indicateur</th>
                                    <th>Nombre de données</th>
                                    <th>Moyenne</th>
                                    <th>Maximum</th>
                                    <th>Minimum</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($climateStats as $stat)
                                    <tr>
                                        <td>
                                            <span class="badge-premium info">
                                                <i class="fas fa-chart-area"></i>
                                                {{ ucfirst(str_replace('_', ' ', $stat->type_indicateur)) }}
                                            </span>
                                        </td>
                                        <td><strong>{{ $stat->count }}</strong></td>
                                        <td>
                                            <span class="badge-premium primary">{{ number_format($stat->moyenne, 2) }}</span>
                                        </td>
                                        <td>
                                            <span class="text-danger fw-bold">
                                                <i class="fas fa-arrow-up me-1"></i>{{ number_format($stat->max, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-success fw-bold">
                                                <i class="fas fa-arrow-down me-1"></i>{{ number_format($stat->min, 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state">
                                                <i class="fas fa-database"></i>
                                                <h5>Aucune donnée disponible</h5>
                                                <p>Les données climatiques apparaîtront ici une fois ajoutées.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Évolution temporelle
const evolutionData = @json($climateEvolution);
const years = [...new Set(evolutionData.map(d => d.annee))].sort();
const indicators = [...new Set(evolutionData.map(d => d.type_indicateur))];

const colors = {
    'secheresse': { border: '#f97316', bg: 'rgba(249, 115, 22, 0.1)' },
    'inondation': { border: '#0ea5e9', bg: 'rgba(14, 165, 233, 0.1)' },
    'desertification': { border: '#eab308', bg: 'rgba(234, 179, 8, 0.1)' },
    'temperature': { border: '#ef4444', bg: 'rgba(239, 68, 68, 0.1)' },
    'pluviometrie': { border: '#3b82f6', bg: 'rgba(59, 130, 246, 0.1)' },
    'pollution_air': { border: '#6b7280', bg: 'rgba(107, 114, 128, 0.1)' }
};

const datasets = indicators.map(indicator => {
    const color = colors[indicator] || { border: '#6b7280', bg: 'rgba(107, 114, 128, 0.1)' };
    return {
        label: indicator.replace('_', ' ').toUpperCase(),
        data: years.map(year => {
            const item = evolutionData.find(d => d.annee === year && d.type_indicateur === indicator);
            return item ? parseFloat(item.moyenne) : null;
        }),
        borderColor: color.border,
        backgroundColor: color.bg,
        tension: 0.4,
        fill: true,
        borderWidth: 3,
        pointRadius: 5,
        pointHoverRadius: 8
    };
});

new Chart(document.getElementById('climateEvolutionChart'), {
    type: 'line',
    data: {
        labels: years,
        datasets: datasets
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true,
                    font: { size: 12, weight: '500' }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' },
                title: {
                    display: true,
                    text: 'Valeur moyenne',
                    font: { weight: '600' }
                }
            },
            x: {
                grid: { display: false },
                title: {
                    display: true,
                    text: 'Année',
                    font: { weight: '600' }
                }
            }
        }
    }
});

// Répartition par indicateur
const statsData = @json($climateStats);
new Chart(document.getElementById('climateStatsChart'), {
    type: 'doughnut',
    data: {
        labels: statsData.map(d => d.type_indicateur.replace('_', ' ').toUpperCase()),
        datasets: [{
            data: statsData.map(d => d.count),
            backgroundColor: statsData.map(d => {
                const c = colors[d.type_indicateur];
                return c ? c.border : '#6b7280';
            }),
            borderWidth: 0,
            hoverOffset: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        cutout: '65%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    usePointStyle: true,
                    font: { size: 11, weight: '500' }
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