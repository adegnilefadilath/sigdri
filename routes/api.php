<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes API — SIGDRI
|--------------------------------------------------------------------------
| Toutes ces routes sont préfixées par /api et passent par le middleware
| ForceJsonResponse (enregistré dans bootstrap/app.php).
*/

/**
 * Route de santé — Module 0
 * Permet de vérifier que l'application et la base de données sont opérationnelles.
 * GET /api/health
 */
Route::get('/health', function () {
    // Vérification de la connexion à la base de données
    try {
        DB::connection()->getPdo();
        $etatBase = 'connectee';
    } catch (\Throwable $e) {
        // La connexion a échoué : on renvoie quand même une réponse JSON (pas une erreur 500)
        $etatBase = 'deconnectee';
    }

    return response()->json([
        'status'    => 'ok',
        'db'        => $etatBase,
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('api.health');
