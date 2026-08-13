<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Coupe l'accès d'un compte désactivé, y compris en cours de session.
 *
 * Le contrôleur de connexion refuse déjà un compte inactif, mais il ne
 * s'exécute qu'une fois. Sans cette vérification à chaque requête, désactiver
 * un compte ne produisait aucun effet tant que sa session vivait — jusqu'à
 * deux heures avec la durée configurée. Le geste par lequel un administrateur
 * retire l'accès à quelqu'un ne retirait donc rien dans l'immédiat.
 */
class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $utilisateur = Auth::user();

        if ($utilisateur && $utilisateur->statut !== 'actif') {
            Log::warning('Session poursuivie par un compte désactivé, interrompue', [
                'user_id' => $utilisateur->id,
                'statut' => $utilisateur->statut,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Votre compte a été désactivé. Veuillez contacter l\'administrateur.',
            ]);
        }

        return $next($request);
    }
}
