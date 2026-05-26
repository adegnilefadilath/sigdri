<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de limitation des soumissions de déclarations
 *
 * Protège l'endpoint POST industriel/declarations contre les soumissions
 * abusives ou les doubles envois. Chaque industriel connecté est limité à
 * 10 soumissions par heure, glissantes (TTL 3600 secondes via le cache).
 *
 * Retourne un statut HTTP 429 avec un message clair si la limite est atteinte.
 */
class RateLimitDeclarations
{
    /**
     * Génère une clé de rate limit unique par utilisateur.
     * Format : declarations:{user_id}
     */
    private function cleLimite(int $userId): string
    {
        return 'declarations:' . $userId;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $userId = Auth::id();

        // Sécurité défensive : si l'utilisateur n'est pas authentifié, on laisse passer
        // (les middlewares d'authentification gèrent ce cas en amont)
        if (! $userId) {
            return $next($request);
        }

        $cle        = $this->cleLimite($userId);
        $maxTentatives = 10;      // Nombre maximum de soumissions autorisées
        $fenetreSecondes = 3600; // Fenêtre glissante d'une heure

        if (RateLimiter::tooManyAttempts($cle, $maxTentatives)) {
            // Calcule le nombre de secondes avant de pouvoir retenter
            $reessaiDans = RateLimiter::availableIn($cle);

            // Réponse JSON si la requête attend du JSON (API / AJAX)
            if ($request->expectsJson()) {
                return response()->json([
                    'erreur'       => 'Limite de soumissions atteinte.',
                    'message'      => "Vous avez atteint la limite de {$maxTentatives} soumissions par heure.",
                    'reessai_dans' => $reessaiDans . ' secondes',
                ], 429);
            }

            // Réponse HTML pour une soumission de formulaire classique
            return back()->with('erreur',
                "Vous avez atteint la limite de {$maxTentatives} soumissions par heure. "
                . "Veuillez patienter " . ceil($reessaiDans / 60) . " minute(s) avant de réessayer."
            );
        }

        // Incrémente le compteur et fixe son expiration à 1 heure
        RateLimiter::hit($cle, $fenetreSecondes);

        return $next($request);
    }
}
