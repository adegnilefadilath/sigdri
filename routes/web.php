<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UnitesIndustriellesController;
use App\Http\Controllers\Admin\AgrementController as AdminAgrementController;
use App\Http\Controllers\Industriel\AuthController       as IndustrielAuthController;
use App\Http\Controllers\Industriel\DashboardController  as IndustrielDashboardController;
use App\Http\Controllers\Industriel\AgrementController   as IndustrielAgrementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Web — SIGDRI
|--------------------------------------------------------------------------
| Les routes publiques (login) ne passent pas par AdminAuth.
| Les routes protégées utilisent le middleware 'admin.auth' (alias déclaré
| dans bootstrap/app.php).
*/

// ── Racine : redirige vers /login ───────────────────────────────────────────
Route::get('/', fn () => redirect()->route('login'));

// ── Routes publiques d'authentification ─────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('admin.auth')
    ->name('logout');

// ── Routes protégées du back-office ─────────────────────────────────────────
Route::middleware('admin.auth')->group(function () {

    // Tableau de bord principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ════════════════════════════════════════════════════════════════════════
    // MODULE 2 — Gestion des unités industrielles et des agréments
    // Préfixe URL : /admin/   — Préfixe nom : admin.
    // ════════════════════════════════════════════════════════════════════════
    Route::prefix('admin')->name('admin.')->group(function () {

        // ── Unités industrielles (CRUD complet + désactivation) ──────────────
        Route::resource('unites', UnitesIndustriellesController::class);

        // ── Agréments (CRUD + actions métier) ───────────────────────────────
        // destroy() exclu : les agréments ne sont jamais supprimés, seulement suspendus/révoqués
        Route::resource('agrements', AdminAgrementController::class)->except(['destroy']);

        // Suspension avec motif obligatoire
        Route::post('agrements/{agrement}/suspendre', [AdminAgrementController::class, 'suspendre'])
             ->name('agrements.suspendre');

        // Réactivation d'un agrément suspendu
        Route::post('agrements/{agrement}/reactiver', [AdminAgrementController::class, 'reactiver'])
             ->name('agrements.reactiver');
    });
});

// ════════════════════════════════════════════════════════════════════════════
// ESPACE INDUSTRIEL — préfixe /industriel
// Séparé de l'espace admin pour cloisonner les accès et les vues
// ════════════════════════════════════════════════════════════════════════════
Route::prefix('industriel')->name('industriel.')->group(function () {

    // ── Authentification publique ────────────────────────────────────────────
    Route::get('/login',  [IndustrielAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [IndustrielAuthController::class, 'login'])->name('login.submit');

    // ── Déconnexion (nécessite d'être authentifié) ───────────────────────────
    Route::post('/logout', [IndustrielAuthController::class, 'logout'])
         ->middleware('industriel.auth')
         ->name('logout');

    // ── Routes protégées de l'espace industriel ──────────────────────────────
    Route::middleware('industriel.auth')->group(function () {

        // Tableau de bord
        Route::get('/dashboard', [IndustrielDashboardController::class, 'index'])
             ->name('dashboard');

        // Mon agrément (lecture seule)
        Route::get('/agrement', [IndustrielAgrementController::class, 'show'])
             ->name('agrement.show');
    });
});
