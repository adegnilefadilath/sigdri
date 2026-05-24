<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware : ForceJsonResponse
 * Force l'en-tête Accept: application/json sur toutes les requêtes API entrantes
 * afin que Laravel retourne systématiquement du JSON (même pour les erreurs 404/422…).
 */
class ForceJsonResponse
{
    /**
     * Traite la requête entrante.
     *
     * @param  Request  $request   Requête HTTP courante
     * @param  Closure  $next      Prochain handler de la chaîne middleware
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Injecte l'en-tête Accept pour garantir une réponse JSON
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
