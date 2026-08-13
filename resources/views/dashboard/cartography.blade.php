@extends('layouts.app')

@section('title', 'Cartographie')

@push('styles')
<style>
    /* Padding card-body */
    .premium-card .card-body {
        padding: 2rem 2.5rem;
    }
    @media (max-width: 768px) {
        .premium-card .card-body {
            padding: 1.5rem;
        }
    }
    
    /* Styles spécifiques cartographie */
    .legend-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .legend-item.disabled {
        opacity: 0.4;
        text-decoration: line-through;
    }
    
    #map {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    }
    
    /* Custom scrollbar for alerts */
    #alertsList::-webkit-scrollbar {
        width: 6px;
    }
    #alertsList::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }
    #alertsList::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #10b981, #059669);
        border-radius: 3px;
    }
    
    /* Alert cards in list */
    .alert-card {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-left: 4px solid;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        transition: all 0.3s ease;
    }
    .alert-card:hover {
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .alert-card.danger { border-color: #ef4444; background: linear-gradient(135deg, #fff 0%, #fef2f2 100%); }
    .alert-card.warning { border-color: #f59e0b; background: linear-gradient(135deg, #fff 0%, #fffbeb 100%); }
    .alert-card.info { border-color: #3b82f6; background: linear-gradient(135deg, #fff 0%, #eff6ff 100%); }
    .alert-card.success { border-color: #10b981; background: linear-gradient(135deg, #fff 0%, #f0fdf4 100%); }
    
    /* Region data card styling */
    .region-stat {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .region-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    .region-stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
    }
    .region-stat-label {
        font-size: 0.8rem;
        color: #64748b;
    }
</style>
@endpush

@section('content')
<!-- Bloc JS global pour garantir la disponibilité des fonctions -->
<script>
// Flag pour éviter les initialisations multiples
let mapInitialized = false;
let regionCircles = []; // Stocker les cercles pour le filtrage par légende
let currentRegionData = {}; // Cache des données des régions
let activeFilters = { faible: true, modere: true, eleve: true, critique: true }; // Filtres de la légende

// Fonction principale d'initialisation de la carte
function initMappingMap() {
    // Éviter les initialisations multiples
    if (mapInitialized && window.map) {
        console.log('⚠️ Carte déjà initialisée, ignoré');
        return true;
    }
    
    const mapElement = document.getElementById('map');
    if (!mapElement) {
        console.error('❌ ERREUR dans initMappingMap(): #map non trouvé');
        return false;
    }
    
    // Vérifier si Leaflet est chargé
    if (typeof L === 'undefined') {
        console.error('❌ Leaflet (L) non chargé');
        return false;
    }
    
    // Détruire l'ancienne carte si elle existe (avec précaution)
    if (window.map) {
        try {
            window.map.off();
            window.map.remove();
            window.map = null;
            console.log('🧹 Ancienne instance de carte supprimée');
        } catch (e) {
            console.warn('Problème lors de la suppression de la carte:', e);
            window.map = null;
        }
    }
    
    // Vider le contenu de l'élément map au cas où
    mapElement.innerHTML = '';
    
    try {
        // Créer la carte
        window.map = L.map('map').setView([14.4974, -14.4524], 7);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(window.map);
        
        // Marquer comme initialisé
        mapInitialized = true;
        
        // Charger les données dynamiques
        loadMapData();
        
        // Charger les alertes actives
        loadActiveAlerts();
        
        // Redimensionner la carte après chargement
        setTimeout(() => {
            if (window.map) window.map.invalidateSize();
        }, 100);
        
        console.log('✅ Carte initialisée avec succès');
        return true;
    } catch (error) {
        console.error('💥 Erreur création carte:', error);
        mapInitialized = false;
        return false;
    }
}

// Fonction pour charger les données dynamiques de la carte via AJAX
function loadMapData() {
    const year = document.getElementById('yearSelect')?.value || new Date().getFullYear();
    const indicator = document.getElementById('indicatorSelect')?.value || 'secheresse';
    
    console.log(`🔄 Chargement des données AJAX: année=${year}, indicateur=${indicator}`);
    
    // Mettre à jour le titre de la carte
    updateMapTitle(indicator, year);
    
    // Appel AJAX pour récupérer les données filtrées
    fetch(`/api/map-data?year=${year}&indicator=${indicator}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(`✅ Données reçues: ${data.regionsData.length} régions, ${data.alertes.length} alertes`);
                displayRegionsOnMap(data.regionsData);
                displayAlertsFromData(data.alertes, indicator, year);
            } else {
                console.error('❌ Erreur API:', data);
            }
        })
        .catch(error => {
            console.error('❌ Erreur fetch:', error);
            // Fallback: utiliser les données initiales
            const regionsData = @json($regionsData);
            displayRegionsOnMap(regionsData);
        });
}

// Afficher les alertes depuis les données AJAX
function displayAlertsFromData(alertes, indicator, year) {
    const alertsList = document.getElementById('alertsList');
    if (!alertsList) return;
    
    if (!alertes || alertes.length === 0) {
        alertsList.innerHTML = `
            <div class="alert alert-success">
                <h6><i class="fas fa-check-circle me-2"></i> Aucune alerte</h6>
                <p class="mb-0 small">Aucune alerte pour ${getIndicatorLabel(indicator)} en ${year}.</p>
            </div>
        `;
        return;
    }
    
    let alertsHtml = `<p class="small text-muted mb-2"><i class="fas fa-filter me-1"></i> ${alertes.length} alerte(s) - ${getIndicatorLabel(indicator)} (${year})</p>`;
    
    alertes.forEach((alert) => {
        const value = parseFloat(alert.valeur || 50);
        const riskLevel = getRiskLevel(value);
        const riskColor = getColorByRisk(riskLevel);
        const iconClass = riskLevel === 'Critique' ? 'fa-exclamation-circle' : 
                         riskLevel === 'Élevé' ? 'fa-exclamation-triangle' : 'fa-info-circle';
        const regionName = alert.region?.nom || 'Région inconnue';
        const regionId = alert.region?.id || alert.region_id || 0;
        
        alertsHtml += `
            <div class="alert alert-warning border-start border-4 mb-2" style="border-color: ${riskColor} !important; cursor: pointer;" onclick="loadRegionDetails(${regionId}, '${regionName.replace(/'/g, "\\'")}')">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1">
                            <i class="fas ${iconClass} me-2" style="color: ${riskColor};"></i>
                            ${regionName}
                        </h6>
                        <p class="mb-1 small">
                            <strong>${alert.type_alerte || 'Alerte'}:</strong> 
                            ${alert.description || 'Pas de description'}
                            <span class="badge ms-2" style="background-color: ${riskColor};">${riskLevel}</span>
                        </p>
                        <p class="mb-0 small text-muted">
                            <i class="fas fa-calendar me-1"></i> ${new Date(alert.date_debut).toLocaleDateString('fr-FR')}
                        </p>
                    </div>
                </div>
            </div>
        `;
    });
    
    alertsList.innerHTML = alertsHtml;
}

// Afficher les régions sur la carte avec des cercles colorés
function displayRegionsOnMap(data) {
    // Supprimer les anciens cercles
    regionCircles.forEach(circle => {
        if (window.map) window.map.removeLayer(circle);
    });
    regionCircles = [];
    
    if (!data || data.length === 0) {
        console.log('⚠️ Aucune donnée à afficher');
        return;
    }
    
    data.forEach(region => {
        if (region.latitude && region.longitude) {
            const value = parseFloat(region.valeur || region.drought_level || 0);
            const riskLevel = getRiskLevel(value);
            const color = getColorByRisk(riskLevel);
            
            // Stocker les données de la région
            currentRegionData[region.id] = region;
            
            // Créer un cercle coloré selon le niveau de risque
            const circle = L.circle([region.latitude, region.longitude], {
                color: color,
                fillColor: color,
                fillOpacity: 0.6,
                radius: Math.max(15000, value * 400),
                riskLevel: riskLevel // Attribut personnalisé pour le filtrage
            }).addTo(window.map);
            
            // Popup avec informations
            const safeName = (region.nom || region.region_name || '').replace(/'/g, "\\'");
            circle.bindPopup(`
                <div style="min-width: 200px;">
                    <h6 class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>${region.nom || region.region_name}</h6>
                    <hr class="my-2">
                    <p class="mb-1"><strong>Valeur:</strong> ${value.toFixed(1)}%</p>
                    <p class="mb-1"><strong>Niveau de risque:</strong> 
                        <span style="color: ${color}; font-weight: bold;">${riskLevel}</span>
                    </p>
                    <p class="mb-1"><strong>Année:</strong> ${region.annee || region.last_year || new Date().getFullYear()}</p>
                    ${region.data_count ? `<p class="mb-0 small text-muted">${region.data_count} mesure(s)</p>` : ''}
                    <hr class="my-2">
                    <button class="btn btn-sm btn-primary w-100" onclick="loadRegionDetails(${region.id}, '${safeName}')">
                        <i class="fas fa-chart-bar me-1"></i> Voir les détails
                    </button>
                </div>
            `);
            
            // Événement clic
            circle.on('click', function() {
                loadRegionDetails(region.id, region.nom || region.region_name);
            });
            
            regionCircles.push(circle);
        }
    });
    
    console.log(`✅ ${regionCircles.length} régions affichées sur la carte`);
}

// Charger les détails d'une région
function loadRegionDetails(regionId, regionName) {
    console.log(`📊 Chargement des détails pour la région ${regionId}: ${regionName}`);
    
    const regionDataDiv = document.getElementById('regionData');
    if (!regionDataDiv) return;
    
    // Récupérer les filtres actuels
    const year = document.getElementById('yearSelect')?.value || new Date().getFullYear();
    const indicator = document.getElementById('indicatorSelect')?.value || 'secheresse';
    
    // Afficher le chargement
    regionDataDiv.innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-2 mb-0">Chargement de ${regionName}...</p>
            <small class="text-muted">${getIndicatorLabel(indicator)} - ${year}</small>
        </div>
    `;
    
    // Simuler des données de région (remplacer par un vrai appel API Laravel si nécessaire)
    setTimeout(() => {
        const regionData = currentRegionData[regionId];
        if (regionData) {
            const mockData = [
                {
                    type_indicateur: indicator,
                    valeur: regionData.valeur || 50,
                    unite: '%',
                    annee: year,
                    source: 'ONCC Sénégal'
                }
            ];
            displayRegionDetails(mockData, regionName, year, indicator);
        }
    }, 500);
}

// Afficher les détails de la région
function displayRegionDetails(data, regionName, year, indicator) {
    const regionDataDiv = document.getElementById('regionData');
    if (!regionDataDiv) return;
    
    if (!data || data.length === 0) {
        regionDataDiv.innerHTML = `
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle me-2"></i>${regionName}</h6>
                <p class="mb-0 small">Aucune donnée disponible pour ${getIndicatorLabel(indicator)} en ${year}.</p>
            </div>
        `;
        return;
    }
    
    // Construire le HTML
    let html = `
        <h6 class="border-bottom pb-2 mb-3">
            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
            ${regionName}
            <small class="d-block text-muted mt-1">${getIndicatorLabel(indicator)} - ${year}</small>
        </h6>
    `;
    
    // Afficher les indicateurs avec leurs valeurs
    const indicatorIcons = {
        'secheresse': 'fa-sun',
        'inondation': 'fa-water',
        'desertification': 'fa-desert',
        'temperature': 'fa-thermometer-half',
        'pluviometrie': 'fa-cloud-rain',
        'pollution_air': 'fa-smog'
    };
    
    data.forEach(item => {
        const value = parseFloat(item.valeur);
        const riskLevel = getRiskLevel(value);
        const color = getColorByRisk(riskLevel);
        const icon = indicatorIcons[item.type_indicateur] || 'fa-chart-line';
        
        html += `
            <div class="mb-3 p-2 border rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas ${icon} me-2" style="color: ${color};"></i>
                        ${getIndicatorLabel(item.type_indicateur)}
                    </span>
                    <span class="badge" style="background-color: ${color};">${value.toFixed(1)}${item.unite || '%'}</span>
                </div>
                <small class="text-muted d-block mt-1">
                    Année: ${item.annee} | Source: ${item.source || 'N/A'}
                </small>
            </div>
        `;
    });
    
    html += `
        <div class="mt-3 pt-2 border-top">
            <small class="text-muted">
                <i class="fas fa-database me-1"></i> ${data.length} données disponibles
            </small>
        </div>
    `;
    
    regionDataDiv.innerHTML = html;
}

// Variables globales pour stocker les filtres actuels
let currentYear = new Date().getFullYear();
let currentIndicator = 'secheresse';

// Fonction pour mettre à jour la carte avec les filtres
function updateMap() {
    // Récupérer les valeurs des filtres
    currentYear = document.getElementById('yearSelect')?.value || new Date().getFullYear();
    currentIndicator = document.getElementById('indicatorSelect')?.value || 'secheresse';
    
    console.log(`🔄 Application des filtres: année=${currentYear}, indicateur=${currentIndicator}`);
    
    // Mettre à jour la carte
    loadMapData();
    
    // Mettre à jour les alertes avec les filtres
    loadActiveAlerts();
    
    // Réinitialiser les données de région
    const regionDataDiv = document.getElementById('regionData');
    if (regionDataDiv) {
        regionDataDiv.innerHTML = `
            <p class="text-center text-muted">
                <i class="fas fa-mouse-pointer fa-2x mb-3"></i><br>
                Cliquez sur une région pour voir ses données
            </p>
        `;
    }
    
    // Notification de mise à jour
    showNotification(`Filtres appliqués: ${getIndicatorLabel(currentIndicator)} - ${currentYear}`, 'success');
}

// Obtenir le label de l'indicateur
function getIndicatorLabel(indicator) {
    const labels = {
        'secheresse': 'Sécheresse',
        'inondation': 'Inondation',
        'desertification': 'Désertification',
        'temperature': 'Température',
        'pluviometrie': 'Pluviométrie',
        'pollution_air': 'Pollution de l\'air',
        'all': 'Tous les indicateurs'
    };
    return labels[indicator] || indicator;
}

// Mettre à jour le titre de la carte
function updateMapTitle(indicator, year) {
    const titleElement = document.querySelector('.card-header.bg-success h5');
    if (titleElement) {
        const indicatorLabels = {
            'secheresse': 'Sécheresse',
            'inondation': 'Inondation',
            'desertification': 'Désertification',
            'temperature': 'Température',
            'pluviometrie': 'Pluviométrie'
        };
        titleElement.innerHTML = `<i class="fas fa-globe-africa me-2"></i>Carte du Sénégal - ${indicatorLabels[indicator] || indicator} (${year})`;
    }
}

// Filtrer les régions par niveau de risque (légende interactive)
function filterByRiskLevel(level) {
    activeFilters[level] = !activeFilters[level];
    
    // Mettre à jour l'apparence de la légende
    const legendItem = document.querySelector(`[data-risk-level="${level}"]`);
    if (legendItem) {
        legendItem.style.opacity = activeFilters[level] ? '1' : '0.3';
    }
    
    // Filtrer les cercles sur la carte
    regionCircles.forEach(circle => {
        const circleRiskLevel = circle.options.riskLevel?.toLowerCase();
        if (circleRiskLevel) {
            const riskKey = circleRiskLevel === 'élevé' ? 'eleve' : 
                           circleRiskLevel === 'modéré' ? 'modere' : 
                           circleRiskLevel.toLowerCase();
            
            if (activeFilters[riskKey]) {
                circle.setStyle({ opacity: 1, fillOpacity: 0.6 });
            } else {
                circle.setStyle({ opacity: 0.1, fillOpacity: 0.1 });
            }
        }
    });
}

// Déterminer le niveau de risque
function getRiskLevel(value) {
    if (value >= 75) return 'Critique';
    if (value >= 50) return 'Élevé';
    if (value >= 25) return 'Modéré';
    return 'Faible';
}

// Obtenir la couleur par niveau de risque
function getColorByRisk(riskLevel) {
    const level = riskLevel.toLowerCase();
    if (level === 'critique') return '#e76f51';
    if (level === 'élevé' || level === 'eleve') return '#f4a261';
    if (level === 'modéré' || level === 'modere') return '#e6dd3b';
    return '#1e6f5c';
}

// Fonction pour charger les alertes actives
function loadActiveAlerts() {
    console.log('🔄 Chargement des alertes actives...');
    
    const alertsList = document.getElementById('alertsList');
    if (!alertsList) {
        console.error('❌ Element alertsList non trouvé');
        return;
    }
    
    // Récupérer les filtres actuels
    const year = document.getElementById('yearSelect')?.value || new Date().getFullYear();
    const indicator = document.getElementById('indicatorSelect')?.value || 'secheresse';
    const showAlerts = document.getElementById('showAlerts')?.checked;
    
    if (!showAlerts) {
        alertsList.innerHTML = `
            <div class="alert alert-secondary">
                <h6><i class="fas fa-eye-slash me-2"></i> Alertes masquées</h6>
                <p class="mb-0 small">Cochez "Afficher les alertes" pour voir les alertes.</p>
            </div>
        `;
        return;
    }
    
    // Afficher le chargement
    alertsList.innerHTML = `
        <div class="alert alert-info">
            <h6><i class="fas fa-spinner fa-spin me-2"></i> Chargement...</h6>
            <p class="mb-0 small">Récupération des alertes pour ${getIndicatorLabel(indicator)} (${year})</p>
        </div>
    `;
    
    // Appel AJAX pour récupérer les alertes filtrées
    fetch(`/api/map-data?year=${year}&indicator=${indicator}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAlertsFromData(data.alertes, indicator, year);
            }
        })
        .catch(error => {
            console.error('❌ Erreur fetch alertes:', error);
            alertsList.innerHTML = `
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i> Erreur</h6>
                    <p class="mb-0 small">Impossible de charger les alertes.</p>
                </div>
            `;
        });
}

// Contrôles de zoom
function zoomIn() {
    if (window.map) window.map.zoomIn();
}

function zoomOut() {
    if (window.map) window.map.zoomOut();
}

function resetView() {
    if (window.map) window.map.setView([14.4974, -14.4524], 7);
}

// Basculer le mode plein écran
var isFullscreen = false;
function toggleFullscreen() {
    const mapContainer = document.getElementById('map');
    if (!mapContainer) return;
    
    if (!isFullscreen) {
        if (mapContainer.requestFullscreen) {
            mapContainer.requestFullscreen();
        } else if (mapContainer.webkitRequestFullscreen) {
            mapContainer.webkitRequestFullscreen();
        } else if (mapContainer.msRequestFullscreen) {
            mapContainer.msRequestFullscreen();
        }
        isFullscreen = true;
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
        isFullscreen = false;
    }
    
    setTimeout(() => {
        if (window.map) window.map.invalidateSize();
    }, 300);
}

// Afficher une notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    notification.innerHTML = `
        <strong>${type === 'success' ? 'Succès' : 'Information'}:</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}
</script>

<div class="container-fluid">
    <!-- Page Header Premium avec particules -->
    <div class="page-header map-header" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); border-radius: 20px; padding: 2rem; margin-bottom: 2rem; color: white; position: relative; overflow: hidden;">
        <div class="particles-container">
            @for($i = 0; $i < 8; $i++)
            <div class="particle" style="left: {{ rand(0, 100) }}%; animation-delay: {{ rand(0, 10) }}s; animation-duration: {{ rand(10, 20) }}s;"></div>
            @endfor
        </div>
        <div style="position: absolute; top: 0; right: 0; width: 300px; height: 100%; opacity: 0.1;">
            <i class="fas fa-globe-africa" style="font-size: 15rem; position: absolute; right: -50px; top: -30px;"></i>
        </div>
        <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; position: relative; z-index: 1;">
            <i class="fas fa-map-marked-alt me-2"></i>
            Cartographie Interactive
        </h1>
        <p style="opacity: 0.9; margin-bottom: 0.5rem; position: relative; z-index: 1;">
            <span class="pulse-dot"></span>
            Visualisation géographique des indicateurs climatiques par région du Sénégal
        </p>
        <div class="header-badge" style="position: relative; z-index: 1;">
            <i class="fas fa-map me-1"></i>
            14 Régions du Sénégal
        </div>
    </div>

<div class="row">
    <div class="col-md-3">
        <!-- Carte Filtres Premium -->
        <div class="premium-card mb-4">
            <div class="card-header gradient-primary">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="fas fa-filter"></i>
                    </div>
                    <div>
                        <h5>Filtres</h5>
                        <small>Personnalisez l'affichage</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form id="mapFilters" class="premium-form">
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-calendar-alt text-primary"></i> Année
                        </label>
                        <select class="form-select" name="year" id="yearSelect">
                            @for ($year = date('Y'); $year >= 2010; $year--)
                                <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-chart-pie text-success"></i> Indicateur
                        </label>
                        <select class="form-select" name="indicator" id="indicatorSelect">
                            <option value="secheresse">🌞 Sécheresse</option>
                            <option value="inondation">🌊 Inondation</option>
                            <option value="desertification">🏜️ Désertification</option>
                            <option value="temperature">🌡️ Température</option>
                            <option value="pluviometrie">🌧️ Pluviométrie</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-bell text-warning"></i> Alertes
                        </label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="showAlerts" checked style="width: 3em; height: 1.5em;">
                            <label class="form-check-label" for="showAlerts">
                                Afficher les alertes
                            </label>
                        </div>
                    </div>
                    
                    <button type="button" class="btn-premium primary w-100" onclick="updateMap()">
                        <i class="fas fa-sync-alt"></i> Appliquer les filtres
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Légende Premium -->
        <div class="premium-card">
            <div class="card-header gradient-info">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div>
                        <h5>Légende</h5>
                        <small>Cliquez pour filtrer</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="legend-item mb-3 p-2 rounded" data-risk-level="faible" onclick="filterByRiskLevel('faible')" style="cursor: pointer; background: #f0fdf4; transition: all 0.3s;">
                    <div class="d-flex align-items-center">
                        <div style="background: linear-gradient(135deg, #10b981, #059669); width: 24px; height: 24px; margin-right: 12px; border-radius: 6px; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);"></div>
                        <div>
                            <strong style="color: #059669;">Faible</strong>
                            <small class="d-block text-muted">0-25%</small>
                        </div>
                    </div>
                </div>
                <div class="legend-item mb-3 p-2 rounded" data-risk-level="modere" onclick="filterByRiskLevel('modere')" style="cursor: pointer; background: #fefce8; transition: all 0.3s;">
                    <div class="d-flex align-items-center">
                        <div style="background: linear-gradient(135deg, #fbbf24, #f59e0b); width: 24px; height: 24px; margin-right: 12px; border-radius: 6px; box-shadow: 0 2px 8px rgba(251, 191, 36, 0.4);"></div>
                        <div>
                            <strong style="color: #d97706;">Modéré</strong>
                            <small class="d-block text-muted">25-50%</small>
                        </div>
                    </div>
                </div>
                <div class="legend-item mb-3 p-2 rounded" data-risk-level="eleve" onclick="filterByRiskLevel('eleve')" style="cursor: pointer; background: #fff7ed; transition: all 0.3s;">
                    <div class="d-flex align-items-center">
                        <div style="background: linear-gradient(135deg, #fb923c, #f97316); width: 24px; height: 24px; margin-right: 12px; border-radius: 6px; box-shadow: 0 2px 8px rgba(249, 115, 22, 0.4);"></div>
                        <div>
                            <strong style="color: #ea580c;">Élevé</strong>
                            <small class="d-block text-muted">50-75%</small>
                        </div>
                    </div>
                </div>
                <div class="legend-item p-2 rounded" data-risk-level="critique" onclick="filterByRiskLevel('critique')" style="cursor: pointer; background: #fef2f2; transition: all 0.3s;">
                    <div class="d-flex align-items-center">
                        <div style="background: linear-gradient(135deg, #f87171, #ef4444); width: 24px; height: 24px; margin-right: 12px; border-radius: 6px; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);"></div>
                        <div>
                            <strong style="color: #dc2626;">Critique</strong>
                            <small class="d-block text-muted">75-100%</small>
                        </div>
                    </div>
                </div>
                <hr class="my-3">
                <small class="text-muted">
                    <i class="fas fa-hand-pointer me-1"></i> Cliquez sur un niveau pour le masquer/afficher
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <!-- Carte Premium -->
        <div class="premium-card mb-4">
            <div class="card-header gradient-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-globe-africa"></i>
                        </div>
                        <div>
                            <h5 id="mapTitle">Carte du Sénégal - Sécheresse ({{ date('Y') }})</h5>
                            <small>Cliquez sur une région pour voir les détails</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-light rounded-circle" onclick="zoomIn()" title="Zoom avant" style="width: 36px; height: 36px;">
                            <i class="fas fa-search-plus"></i>
                        </button>
                        <button class="btn btn-sm btn-light rounded-circle" onclick="zoomOut()" title="Zoom arrière" style="width: 36px; height: 36px;">
                            <i class="fas fa-search-minus"></i>
                        </button>
                        <button class="btn btn-sm btn-light rounded-circle" onclick="resetView()" title="Vue par défaut" style="width: 36px; height: 36px;">
                            <i class="fas fa-expand"></i>
                        </button>
                        <button class="btn btn-sm btn-light rounded-circle" onclick="toggleFullscreen()" title="Plein écran" style="width: 36px; height: 36px;">
                            <i class="fas fa-expand-arrows-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="map" style="height: 500px; width: 100%; position: relative; z-index: 1; border-radius: 0 0 16px 16px;"></div>
            </div>
            <div class="card-footer" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border-radius: 0 0 16px 16px;">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1 text-success"></i> Cliquez sur un marqueur pour voir les détails
                    </small>
                    <small class="text-muted">
                        <i class="fas fa-sync-alt me-1 text-success"></i> Données mises à jour
                    </small>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Alertes actives -->
            <div class="col-md-6">
                <div class="premium-card h-100">
                    <div class="card-header gradient-warning">
                        <div class="header-content">
                            <div class="header-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div>
                                <h5>Alertes Actives</h5>
                                <small>Zones nécessitant attention</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                        <div id="alertsList">
                            <div class="alert-premium info">
                                <div class="alert-icon"><i class="fas fa-spinner fa-spin"></i></div>
                                <div>Chargement des alertes...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Données région -->
            <div class="col-md-6">
                <div class="premium-card h-100">
                    <div class="card-header gradient-primary">
                        <div class="header-content">
                            <div class="header-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div>
                                <h5>Données Région</h5>
                                <small>Détails de la zone sélectionnée</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="regionData">
                            <div class="text-center py-5">
                                <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                    <i class="fas fa-mouse-pointer text-muted" style="font-size: 1.5rem;"></i>
                                </div>
                                <p class="text-muted mb-0">Cliquez sur une région<br>pour voir ses données</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<style>
#map {
    border-radius: 0 0 8px 8px;
    z-index: 1;
    background: #f8f9fa;
}

.region-marker {
    background: none !important;
    border: none !important;
}

.marker-container {
    transition: all 0.3s ease;
}

.marker-container:hover {
    transform: scale(1.1);
}

.value-display {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 10px;
}

.value-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: #1e6f5c;
}

.value-label {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 5px;
}

.leaflet-container {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    font-size: 14px;
}

.leaflet-popup-content {
    margin: 13px 19px;
}

.leaflet-control-zoom {
    margin-top: 10px !important;
    margin-right: 10px !important;
}

/* Animation pour les notifications */
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.alert.position-fixed {
    animation: slideIn 0.3s ease-out;
}

/* Style pour le mode plein écran */
#map:fullscreen {
    width: 100vw !important;
    height: 100vh !important;
    border-radius: 0 !important;
}
</style>

<script>
// Initialiser la carte au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM chargé - Initialisation de la carte...');

    // Fonction pour attendre que l'élément soit prêt
    function waitForMapElement(callback, maxAttempts = 10) {
        let attempts = 0;
        
        function check() {
            attempts++;
            const mapElement = document.getElementById('map');
            
            if (mapElement && mapElement.offsetWidth > 0 && mapElement.offsetHeight > 0) {
                console.log(`✅ Element map prêt après ${attempts} tentative(s)`);
                callback();
            } else if (attempts < maxAttempts) {
                console.log(`⏳ Element map pas encore prêt, tentative ${attempts}/${maxAttempts}`);
                setTimeout(check, 200);
            } else {
                console.error('❌ Element map non disponible après', maxAttempts, 'tentatives');
            }
        }
        
        check();
    }

    // Attendre que l'élément soit prêt avant d'initialiser
    waitForMapElement(function() {
        // Vérifier que Leaflet est chargé
        if (typeof L === 'undefined') {
            console.error('❌ Leaflet non disponible');
            return;
        }
        // Une seule initialisation
        if (typeof initMappingMap === 'function') {
            initMappingMap();
        }
        
        // Ajouter les écouteurs d'événements pour les filtres
        const yearSelect = document.getElementById('yearSelect');
        const indicatorSelect = document.getElementById('indicatorSelect');
        const showAlerts = document.getElementById('showAlerts');
        
        if (yearSelect) {
            yearSelect.addEventListener('change', function() {
                console.log('📅 Année changée:', this.value);
                updateMap();
            });
        }
        
        if (indicatorSelect) {
            indicatorSelect.addEventListener('change', function() {
                console.log('📊 Indicateur changé:', this.value);
                updateMap();
            });
        }
        
        if (showAlerts) {
            showAlerts.addEventListener('change', function() {
                console.log('⚠️ Affichage alertes:', this.checked);
                loadActiveAlerts();
            });
        }
    });
});
</script>
@endsection
