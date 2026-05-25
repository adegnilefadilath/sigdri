<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware : AdminAuth
 * Protège les routes du back-office SIGDRI.
 *
 * Vérifie que :
 *  1. L'utilisateur est authentifié (session active)
 *  2. Son compte est toujours actif (non suspendu)
 *
 * Si l'une des conditions échoue, redirige vers /login.
 */
class AdminAuth
{
    /**
     * @param  Request  $request  Requête HTTP entrante
     * @param  Closure  $next     Prochain handler de la chaîne
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérification de l'authentification via le guard web (session)
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('statut', 'Veuillez vous connecter pour accéder à cette page.');
        }

        // Vérification que le compte n'a pas été suspendu entre deux requêtes
        if (! Auth::user()->actif) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('login')
                ->withErrors(['email' => 'Votre compte a été suspendu. Contactez un administrateur.']);
        }

        return $next($request);
    }
}
