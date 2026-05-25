<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware : IndustrielAuth
 * Protège les routes de l'espace industriel SIGDRI.
 *
 * Vérifie que :
 *  1. L'utilisateur est authentifié (session active)
 *  2. Son rôle est strictement 'industriel'
 *  3. Son compte est actif (non suspendu)
 *
 * Toute condition non satisfaite redirige vers /industriel/login.
 */
class IndustrielAuth
{
    /**
     * @param  Request  $request  Requête HTTP entrante
     * @param  Closure  $next     Prochain handler de la chaîne
     */
    public function handle(Request $request, Closure $next): Response
    {
        // L'utilisateur n'est pas connecté du tout
        if (! Auth::check()) {
            return redirect()->route('industriel.login')
                ->with('statut', 'Veuillez vous connecter pour accéder à l\'espace industriel.');
        }

        $utilisateur = Auth::user();

        // L'utilisateur est connecté mais n'est pas un industriel
        // (ex : un admin qui tenterait d'accéder à /industriel/dashboard)
        if ($utilisateur->role !== 'industriel') {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('industriel.login')
                ->withErrors(['email' => 'Cet espace est réservé aux comptes industriels.']);
        }

        // Le compte industriel a été suspendu
        if (! $utilisateur->actif) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('industriel.login')
                ->withErrors(['email' => 'Votre compte a été suspendu. Contactez l\'administration.']);
        }

        return $next($request);
    }
}
