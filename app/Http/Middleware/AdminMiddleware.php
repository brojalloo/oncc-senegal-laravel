<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier l'authentification
        if (!auth()->check()) {
            Log::warning('Tentative d\'accès admin sans authentification', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'timestamp' => now()
            ]);
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        // Vérifier le rôle admin
        if (auth()->user()->role !== 'admin') {
            Log::warning('Tentative d\'accès admin non autorisée', [
                'user_id' => auth()->id(),
                'user_role' => auth()->user()->role,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'timestamp' => now()
            ]);
            abort(403, 'Accès non autorisé. Seuls les administrateurs peuvent accéder à cette section.');
        }

        // Vérifier si le compte est actif
        if (!auth()->user()->is_active) {
            Log::warning('Tentative d\'accès avec compte désactivé', [
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'timestamp' => now()
            ]);
            auth()->logout();
            return redirect()->route('login')->with('error', 'Votre compte a été désactivé. Contactez l\'administrateur.');
        }

        return $next($request);
    }
}
