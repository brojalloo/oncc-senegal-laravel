<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restreint une route à certains rôles.
 *
 * Le formulaire d'inscription annonce ce que chaque rôle peut faire : le
 * public consulte, la collectivité saisit des données économiques, le
 * chercheur saisit tout. Le tableau de bord respectait cette répartition en
 * masquant les boutons, mais les routes de saisie acceptaient n'importe quel
 * compte authentifié. L'autorisation n'existait que dans la vue.
 *
 * Usage : ->middleware('role:chercheur,collectivite')
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $utilisateur = Auth::user();

        if (! $utilisateur) {
            return redirect()->route('login');
        }

        if (! in_array($utilisateur->role, $roles, true)) {
            Log::warning('Accès refusé : rôle insuffisant', [
                'user_id' => $utilisateur->id,
                'user_role' => $utilisateur->role,
                'roles_requis' => $roles,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            abort(403, 'Votre rôle ne permet pas cette action.');
        }

        return $next($request);
    }
}
