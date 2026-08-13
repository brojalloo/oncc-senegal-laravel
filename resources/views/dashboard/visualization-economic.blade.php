@extends('layouts.app')

@section('title', 'Visualisation Économique')

@push('styles')
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header avec particules -->
    <div class="page-header">
        <h1>
            <i class="fas fa-coins me-2"></i>
            Visualisation des Données Économiques
        </h1>
        <p>
            <span class="pulse-dot"></span>
            Analyse des impacts économiques par secteur d'activité
        </p>
        <div class="header-badge">
            <i class="fas fa-chart-bar me-1"></i>
            Données économiques
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-filter"></i>
                <label for="region_id" class="fw-semibold mb-0">Filtrer par région :</label>
            </div>
            <select name="region_id" class="form-select w-auto" onchange="this.form.submit()">
                <option value="">Toutes les régions</option>
                @foreach($regions as $region)
                    <option value="{{ $region->id }}" {{ $regionId == $region->id ? 'selected' : '' }}>
                        {{ $region->nom }}
                    </option>
                @endforeach
            </select>
            @if($regionId)
                <a href="{{ route('visualization.economic') }}" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                    <i class="fas fa-times me-1"></i>Réinitialiser
                </a>
            @endif
        </form>
    </div>

    <div class="row">
        <!-- Évolution par secteur -->
        <div class="col-lg-8 mb-4">
            <div class="premium-card">
                <div class="card-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-chart-area"></i>
                        </div>
                        <div>
                            <h5>Évolution Économique par Secteur</h5>
                            <small>Tendances annuelles des valeurs économiques</small>
                        </div>
                    </div>
                </div>
                <div class="card-body chart-container">
                    <canvas id="economicEvolutionChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Impact par secteur -->
        <div class="col-lg-4 mb-4">
            <div class="premium-card">
                <div class="card-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div>
                            <h5>Impact par Secteur</h5>
                            <small>Comparaison des secteurs</small>
                        </div>
                    </div>
                </div>
                <div class="card-body chart-container">
                    <canvas id="economicImpactChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Tableau impacts détaillés -->
        <div class="col-12">
            <div class="premium-card">
                <div class="card-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-table"></i>
                        </div>
                        <div>
                            <h5>Impacts Détaillés par Secteur</h5>
                            <small>Résumé des données économiques</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table premium-table">
                            <thead>
                                <tr>
                                    <th>Secteur</th>
                                    <th>Impact</th>
                                    <th>Nombre de données</th>
                                    <th>Total (FCFA)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($economicImpacts as $impact)
                                    <tr>
                                        <td>
                                            <span class="badge-premium primary">
                                                <i class="fas fa-industry"></i>
                                                {{ ucfirst($impact->secteur) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($impact->impact === 'positif')
                                                <span class="badge-premium success">
                                                    <i class="fas fa-arrow-up"></i> Positif
                                                </span>
                                            @elseif($impact->impact === 'negatif')
                                                <span class="badge-premium danger">
                                                    <i class="fas fa-arrow-down"></i> Négatif
                                                </span>
                                            @else
                                                <span class="badge-premium warning">
                                                    <i class="fas fa-minus"></i> Neutre
                                                </span>
                                            @endif
                                        </td>
                                        <td><strong>{{ $impact->count }}</strong></td>
                                        <td>
                                            <span class="fw-bold text-success">
                                                {{ number_format($impact->total, 0, ',', ' ') }} FCFA
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">
                                            <div class="empty-state">
                                                <i class="fas fa-database"></i>
                                                <h5>Aucune donnée disponible</h5>
                                                <p>Les données économiques apparaîtront ici une fois ajoutées.</p>
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
// Évolution par secteur
const evolutionData = @json($economicEvolution);
const years = [...new Set(evolutionData.map(d => d.annee))].sort();
const sectors = [...new Set(evolutionData.map(d => d.secteur))];

const sectorColors = {
    'agriculture': { border: '#10b981', bg: 'rgba(16, 185, 129, 0.1)' },
    'peche': { border: '#06b6d4', bg: 'rgba(6, 182, 212, 0.1)' },
    'tourisme': { border: '#8b5cf6', bg: 'rgba(139, 92, 246, 0.1)' },
    'energie': { border: '#f59e0b', bg: 'rgba(245, 158, 11, 0.1)' },
    'elevage': { border: '#ec4899', bg: 'rgba(236, 72, 153, 0.1)' },
    'foret': { border: '#22c55e', bg: 'rgba(34, 197, 94, 0.1)' }
};

const evolutionDatasets = sectors.map(sector => {
    const color = sectorColors[sector] || { border: '#6b7280', bg: 'rgba(107, 114, 128, 0.1)' };
    return {
        label: sector.charAt(0).toUpperCase() + sector.slice(1),
        data: years.map(year => {
            const item = evolutionData.find(d => d.annee === year && d.secteur === sector);
            return item ? parseFloat(item.total) : null;
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

new Chart(document.getElementById('economicEvolutionChart'), {
    type: 'line',
    data: {
        labels: years,
        datasets: evolutionDatasets
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
                    text: 'Valeur totale (FCFA)',
                    font: { weight: '600' }
                },
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString();
                    }
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

// Impact par secteur
const impactData = @json($economicImpacts);
const impactBySector = {};

impactData.forEach(item => {
    if (!impactBySector[item.secteur]) {
        impactBySector[item.secteur] = 0;
    }
    impactBySector[item.secteur] += parseFloat(item.total);
});

new Chart(document.getElementById('economicImpactChart'), {
    type: 'bar',
    data: {
        labels: Object.keys(impactBySector).map(s => s.charAt(0).toUpperCase() + s.slice(1)),
        datasets: [{
            label: 'Impact Total (FCFA)',
            data: Object.values(impactBySector),
            backgroundColor: Object.keys(impactBySector).map(s => {
                const c = sectorColors[s];
                return c ? c.border : '#6b7280';
            }),
            borderRadius: 8,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString();
                    }
                }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});
</script>
@endpush
@endsection