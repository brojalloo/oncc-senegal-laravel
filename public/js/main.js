// Scripts JavaScript pour ONCC-SN

document.addEventListener('DOMContentLoaded', function() {
    // Gestion des messages d'alerte
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Validation des formulaires
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Tooltips Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Gestion des onglets
    const tabTriggers = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabTriggers.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (e) {
            // Mettre à jour l'URL avec l'onglet actif
            const tabId = e.target.getAttribute('href').substring(1);
            history.replaceState(null, null, `#${tabId}`);
        });
    });
    
    // Restaurer l'onglet actif depuis l'URL
    const hash = window.location.hash;
    if (hash) {
        const triggerEl = document.querySelector(`[href="${hash}"]`);
        if (triggerEl) {
            bootstrap.Tab.getOrCreateInstance(triggerEl).show();
        }
    }
    
    // Auto-hide des messages après soumission
    if (window.location.search.includes('success=true')) {
        setTimeout(() => {
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                const bsAlert = new bootstrap.Alert(successAlert);
                bsAlert.close();
            }
        }, 3000);
    }
    
    // Confirmation avant suppression
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                e.preventDefault();
            }
        });
    });
    
    // Formatage des nombres
    const numberElements = document.querySelectorAll('.format-number');
    numberElements.forEach(element => {
        const number = parseFloat(element.textContent);
        if (!isNaN(number)) {
            element.textContent = new Intl.NumberFormat('fr-FR').format(number);
        }
    });
    
    // Mise à jour dynamique de l'année dans le footer
});

// ==================================
// Fonctions de gestion du thème
// ==================================

function initThemeToggle() {
    const themeCheckbox = document.getElementById('theme-checkbox');
    if (!themeCheckbox) return;
    
    // Appliquer le thème sauvegardé
    const savedTheme = localStorage.getItem('theme') || 'light';
    applyTheme(savedTheme);
    
    // Mettre à jour l'état du checkbox
    themeCheckbox.checked = savedTheme === 'dark';
    
    // Gestionnaire de changement
    themeCheckbox.addEventListener('change', function() {
        const newTheme = this.checked ? 'dark' : 'light';
        
        // Animation du wrapper
        const wrapper = this.closest('.theme-switch-wrapper');
        if (wrapper) {
            wrapper.classList.add('switching');
            setTimeout(() => wrapper.classList.remove('switching'), 300);
        }
        
        applyTheme(newTheme);
        localStorage.setItem('theme', newTheme);
        
        // Notification visuelle
        showThemeNotification(newTheme);
    });
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    
    // Mettre à jour le checkbox
    const themeCheckbox = document.getElementById('theme-checkbox');
    if (themeCheckbox) {
        themeCheckbox.checked = theme === 'dark';
    }
    
    // Mettre à jour les graphiques Chart.js si présents
    updateChartsTheme(theme);
}

function showThemeNotification(theme) {
    // Créer une notification toast
    const message = theme === 'dark' ? 'Mode sombre activé' : 'Mode clair activé';
    const icon = theme === 'dark' ? 'fa-moon' : 'fa-sun';
    
    // Supprimer toute notification existante
    const existingToast = document.querySelector('.theme-toast');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.className = 'theme-toast';
    toast.innerHTML = `<i class="fas ${icon} me-2"></i>${message}`;
    document.body.appendChild(toast);
    
    // Animation d'entrée
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Supprimer après 2 secondes
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}

function updateChartsTheme(theme) {
    // Mise à jour des graphiques Chart.js pour le thème
    if (typeof Chart !== 'undefined' && Chart.instances) {
        const textColor = theme === 'dark' ? '#e5e7eb' : '#374151';
        const gridColor = theme === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
        
        Object.values(Chart.instances).forEach(chart => {
            if (chart.options.scales) {
                if (chart.options.scales.x) {
                    chart.options.scales.x.ticks = chart.options.scales.x.ticks || {};
                    chart.options.scales.x.ticks.color = textColor;
                    chart.options.scales.x.grid = chart.options.scales.x.grid || {};
                    chart.options.scales.x.grid.color = gridColor;
                }
                if (chart.options.scales.y) {
                    chart.options.scales.y.ticks = chart.options.scales.y.ticks || {};
                    chart.options.scales.y.ticks.color = textColor;
                    chart.options.scales.y.grid = chart.options.scales.y.grid || {};
                    chart.options.scales.y.grid.color = gridColor;
                }
            }
            if (chart.options.plugins && chart.options.plugins.legend) {
                chart.options.plugins.legend.labels = chart.options.plugins.legend.labels || {};
                chart.options.plugins.legend.labels.color = textColor;
            }
            chart.update('none');
        });
    }
}

// Écouter les changements de préférence système
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    // Uniquement si l'utilisateur n'a pas de préférence sauvegardée
    if (!localStorage.getItem('theme')) {
        applyTheme(e.matches ? 'dark' : 'light');
    }

    const yearSpan = document.getElementById('current-year');
    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }
});

// Fonction pour générer des couleurs aléatoires
function getRandomColor() {
    const colors = [
        '#1e6f5c', '#289672', '#e6dd3b', '#0a2e38',
        '#2a9d8f', '#e9c46a', '#f4a261', '#e76f51'
    ];
    return colors[Math.floor(Math.random() * colors.length)];
}

// Fonction pour formater les dates
function formatDate(dateString) {
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(dateString).toLocaleDateString('fr-FR', options);
}

// Fonction pour télécharger des données au format CSV
function downloadCSV(data, filename) {
    const csvContent = "data:text/csv;charset=utf-8," 
        + data.map(row => Object.values(row).join(",")).join("\n");
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Initialisation des cartes
function initMap(elementId, center, zoom) {
    const map = L.map(elementId).setView(center, zoom);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    return map;
}

// Fonction pour créer un graphique
function createChart(ctx, type, data, options = {}) {
    return new Chart(ctx, {
        type: type,
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            ...options
        }
    });
}