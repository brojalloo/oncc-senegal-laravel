import './bootstrap';

// Global UI enhancements for the admin/analytics frontend.
// Keep JavaScript routed through Vite so the modernized layout can run cleanly.

// Bascule de thème.
//
// Le thème lui-même est posé avant le premier rendu par le script en ligne
// de resources/views/layouts/theme.blade.php ; ce bloc ne gère que le
// changement d'état, une fois la page affichée.
document.addEventListener('DOMContentLoaded', function() {
    const racine = document.documentElement;
    const bascule = document.getElementById('basculeTheme');

    const appliquerTheme = function(sombre) {
        if (sombre) {
            racine.setAttribute('data-theme', 'dark');
            racine.setAttribute('data-bs-theme', 'dark');
        } else {
            racine.removeAttribute('data-theme');
            racine.removeAttribute('data-bs-theme');
        }

        if (bascule) {
            bascule.setAttribute('aria-pressed', sombre ? 'true' : 'false');
            bascule.setAttribute(
                'aria-label',
                sombre ? 'Passer au thème clair' : 'Passer au thème sombre'
            );
        }
    };

    appliquerTheme(racine.getAttribute('data-theme') === 'dark');

    if (bascule) {
        bascule.addEventListener('click', function() {
            const sombre = racine.getAttribute('data-theme') !== 'dark';
            appliquerTheme(sombre);

            try {
                window.localStorage.setItem('oncc-theme', sombre ? 'dark' : 'light');
            } catch (e) {
                // Sans stockage, le choix ne vaut que pour cette page.
            }
        });
    }

    // Tant que rien n'a été choisi explicitement, l'interface suit le
    // réglage du système et le suit encore s'il change en cours de session.
    const preference = window.matchMedia('(prefers-color-scheme: dark)');
    preference.addEventListener('change', function(e) {
        let choisi = null;
        try {
            choisi = window.localStorage.getItem('oncc-theme');
        } catch (err) {
            choisi = null;
        }

        if (choisi !== 'dark' && choisi !== 'light') {
            appliquerTheme(e.matches);
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.querySelector('[data-bs-target="#sidebar"]');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);

        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        });

        overlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });

        const closeBtn = sidebar.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }
    }

    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert && alert.parentNode) {
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 300);
            }
        }, 5000);
    });

    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
            }
        });
    });
});
