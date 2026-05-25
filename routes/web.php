<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Industriel\AuthController as IndustrielAuthController;
use App\Http\Controllers\Industriel\DashboardController as IndustrielDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Web — SIGDRI
|--------------------------------------------------------------------------
| Les routes publiques (login) ne passent pas par AdminAuth.
| Les routes protégées utilisent le middleware 'admin.auth' (alias déclaré
| dans bootstrap/app.php).
*/

// ── Racine : redirige vers /login ou /dashboard selon l'état de connexion ──
Route::get('/', function () {
    return redirect()->route('login');
});

// ── Routes publiques d'authentification ─────────────────────────────────────
Route::middleware('guest')->group(function () {
    // Affichage du formulaire de connexion
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

    // Traitement du formulaire de connexion
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// Déconnexion (protégée pour éviter les appels non authentifiés)
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('admin.auth')
    ->name('logout');

// ── Routes protégées du back-office ─────────────────────────────────────────
Route::middleware('admin.auth')->group(function () {
    // Tableau de bord principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
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
        // Tableau de bord de l'industriel
        Route::get('/dashboard', [IndustrielDashboardController::class, 'index'])
             ->name('dashboard');
    });
});
