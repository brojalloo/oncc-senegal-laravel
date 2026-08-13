{{--
    Choix du thème, appliqué avant le premier rendu.

    Ce script doit rester en ligne et en tête de <head> : chargé comme un
    module Vite, il s'exécuterait après le premier rendu et la page
    apparaîtrait brièvement en clair avant de basculer.

    L'attribut data-bs-theme accompagne data-theme pour que les composants
    habillés par Bootstrap — menus déroulants, champs, tableaux, fenêtres
    modales — basculent avec le reste.
--}}
<script>
    (function () {
        try {
            var choix = window.localStorage.getItem('oncc-theme');
            var sombre = choix === 'dark' || choix === 'light'
                ? choix === 'dark'
                : window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (sombre) {
                document.documentElement.setAttribute('data-theme', 'dark');
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            }
        } catch (e) {
            // Navigation privée stricte : localStorage lève. Le thème clair
            // reste alors le rendu par défaut, ce qui est acceptable.
        }
    })();
</script>
