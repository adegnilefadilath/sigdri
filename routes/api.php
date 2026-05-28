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

/**
 * Recherche d'un produit par code-barres ou désignation — Scanner caméra
 * GET /api/produits/scan/{code}
 *
 * Cherche par code_produit (exact) puis par désignation (partielle).
 * Retourne toujours 200 avec : { trouve: bool, nom: string|null, unite: string|null }
 * Le flag "trouve" permet au JS de distinguer sans inspecter le status HTTP.
 */
Route::get('/produits/scan/{code}', function (string $code) {
    $produit = DB::table('produits')
        ->where('actif', true)
        ->where(function ($q) use ($code) {
            // Priorité : code produit exact, puis partiel, puis nom contient le code
            $q->where('code_produit', $code)
              ->orWhere('code_produit', 'like', '%' . $code . '%')
              ->orWhere('designation',  'like', '%' . $code . '%');
        })
        ->first(['id', 'designation', 'code_produit', 'unite_mesure']);

    if (! $produit) {
        return response()->json(['trouve' => false, 'nom' => null, 'unite' => null]);
    }

    return response()->json([
        'trouve' => true,
        'nom'    => $produit->designation,
        'unite'  => $produit->unite_mesure,
    ]);
})->name('api.produits.scan');
