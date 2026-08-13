<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Origines externes dont dépendent les vues : Bootstrap et Chart.js sur
     * jsDelivr, Leaflet sur unpkg, Font Awesome sur cdnjs, les polices sur
     * Bunny, et les tuiles cartographiques d'OpenStreetMap et CARTO.
     */
    private const SCRIPT_SOURCES = [
        'https://cdn.jsdelivr.net',
        'https://unpkg.com',
        'https://cdnjs.cloudflare.com',
    ];

    private const STYLE_SOURCES = [
        'https://cdn.jsdelivr.net',
        'https://unpkg.com',
        'https://cdnjs.cloudflare.com',
        'https://fonts.bunny.net',
        // style.css et optimized.css importent des polices depuis Google.
        'https://fonts.googleapis.com',
    ];

    private const FONT_SOURCES = [
        'https://fonts.bunny.net',
        'https://cdnjs.cloudflare.com',
        'https://cdn.jsdelivr.net',
        // La feuille servie par googleapis pointe vers gstatic pour les
        // fichiers de police eux-mêmes : autoriser l'un sans l'autre ne
        // charge rien.
        'https://fonts.gstatic.com',
    ];

    private const IMAGE_SOURCES = [
        'data:',
        'https://*.tile.openstreetmap.org',
        'https://*.basemaps.cartocdn.com',
        'https://unpkg.com',
        // custom.css utilise une photographie hébergée par Unsplash.
        'https://images.unsplash.com',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());

        // Annoncer HSTS sur une réponse en clair n'a aucun effet et complique
        // le développement local : on ne l'envoie que sur une connexion sûre.
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        // X-Powered-By est ajouté par PHP lui-même, au niveau du SAPI : le
        // retirer de l'objet réponse Symfony ne suffit pas, il faut passer par
        // header_remove(). L'image de production coupe déjà la source avec
        // expose_php=Off ; cet appel couvre les autres façons de servir l'app.
        if (! headers_sent()) {
            header_remove('X-Powered-By');
        }

        return $response;
    }

    /**
     * Politique de sécurité du contenu.
     *
     * Limite connue : 'unsafe-inline' reste nécessaire tant que les vues
     * portent des balises <script> et des attributs style en ligne (neuf vues
     * concernées). La politique bloque donc l'injection de scripts distants,
     * l'encadrement dans une iframe tierce, le détournement de formulaire et
     * l'injection de balise <base>, mais pas un script injecté en ligne.
     * Passer à des nonces suppose de retoucher ces vues — travail distinct.
     */
    private function contentSecurityPolicy(): string
    {
        $script = implode(' ', self::SCRIPT_SOURCES);
        $style = implode(' ', self::STYLE_SOURCES);
        $font = implode(' ', self::FONT_SOURCES);
        $image = implode(' ', self::IMAGE_SOURCES);

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' {$script}",
            "style-src 'self' 'unsafe-inline' {$style}",
            "font-src 'self' {$font}",
            "img-src 'self' {$image}",
            "connect-src 'self'",
            "form-action 'self'",
            "base-uri 'self'",
            "frame-ancestors 'none'",
            "object-src 'none'",
        ]);
    }
}
