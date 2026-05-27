<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AlertesController;
use App\Http\Controllers\Admin\CartographieController;
use App\Http\Controllers\Admin\JournauxController;
use App\Http\Controllers\Admin\NotificationsController     as AdminNotificationsController;
use App\Http\Controllers\Admin\ParametresController;
use App\Http\Controllers\Admin\ProfilController            as AdminProfilController;
use App\Http\Controllers\Admin\ReportingController;
use App\Http\Controllers\Admin\UnitesIndustriellesController;
use App\Http\Controllers\Admin\UtilisateursController;
use App\Http\Controllers\Admin\AgrementController          as AdminAgrementController;
use App\Http\Controllers\Admin\DeclarationsController      as AdminDeclarationsController;
use App\Http\Controllers\Industriel\AuthController            as IndustrielAuthController;
use App\Http\Controllers\Industriel\DashboardController       as IndustrielDashboardController;
use App\Http\Controllers\Industriel\AgrementController        as IndustrielAgrementController;
use App\Http\Controllers\Industriel\DeclarationsController    as IndustrielDeclarationsController;
use App\Http\Controllers\Industriel\NotificationsController   as IndustrielNotificationsController;
use App\Http\Controllers\Industriel\ProfilController          as IndustrielProfilController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Web — SIGDRI
|--------------------------------------------------------------------------
| Les routes publiques (login) ne passent pas par AdminAuth.
| Les routes protégées utilisent le middleware 'admin.auth' ou 'industriel.auth'
| (alias déclarés dans bootstrap/app.php).
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
        Route::resource('agrements', AdminAgrementController::class)->except(['destroy']);
        Route::post('agrements/{agrement}/suspendre',
            [AdminAgrementController::class, 'suspendre'])->name('agrements.suspendre');
        Route::post('agrements/{agrement}/reactiver',
            [AdminAgrementController::class, 'reactiver'])->name('agrements.reactiver');

        // ════════════════════════════════════════════════════════════════════
        // MODULE 3 — Déclarations industrielles
        // ════════════════════════════════════════════════════════════════════

        // ── Déclarations : lecture + actions métier (pas de create/edit côté admin)

        // Export CSV — doit être déclaré AVANT le resource pour éviter que
        // "exporter" soit capturé comme {declaration} par la route show.
        Route::get('declarations/exporter',
            [AdminDeclarationsController::class, 'exporter'])->name('declarations.exporter');

        Route::resource('declarations', AdminDeclarationsController::class)
             ->only(['index', 'show']);

        // Validation d'une déclaration soumise
        Route::post('declarations/{declaration}/valider',
            [AdminDeclarationsController::class, 'valider'])->name('declarations.valider');

        // Rejet avec motif obligatoire
        Route::post('declarations/{declaration}/rejeter',
            [AdminDeclarationsController::class, 'rejeter'])->name('declarations.rejeter');

        // ════════════════════════════════════════════════════════════════════
        // MODULE 4 — Reporting et statistiques
        // ════════════════════════════════════════════════════════════════════

        // Page principale avec filtres et graphiques Chart.js
        Route::get('reporting', [ReportingController::class, 'index'])->name('reporting.index');

        // Endpoint JSON pour rechargement dynamique des graphiques
        Route::get('reporting/statistiques', [ReportingController::class, 'statistiques'])->name('reporting.statistiques');

        // Exports — déclarés avant tout éventuel sous-ressource pour éviter les conflits
        Route::get('reporting/export-pdf',   [ReportingController::class, 'exportPDF'])->name('reporting.export-pdf');
        Route::get('reporting/export-excel', [ReportingController::class, 'exportExcel'])->name('reporting.export-excel');

        // ════════════════════════════════════════════════════════════════════
        // MODULE 5 — Cartographie et alertes
        // ════════════════════════════════════════════════════════════════════

        // Carte Leaflet des unités industrielles géolocalisées
        Route::get('cartographie',         [CartographieController::class, 'index'])->name('cartographie.index');

        // Endpoint JSON consommé par Leaflet.js (GET pour caching navigateur)
        Route::get('cartographie/donnees', [CartographieController::class, 'donnees'])->name('cartographie.donnees');

        // Alertes actives : agréments expirant, expirés, déclarations en attente
        Route::get('alertes', [AlertesController::class, 'index'])->name('alertes.index');

        // Marquage d'une alerte comme traitée (POST pour éviter le double-clic via refresh)
        Route::post('alertes/{id}/traiter', [AlertesController::class, 'marquerTraitee'])->name('alertes.traiter');

        // ════════════════════════════════════════════════════════════════════
        // MODULE 6 — Gestion des utilisateurs et administration
        // ════════════════════════════════════════════════════════════════════

        // ── Utilisateurs (CRUD sans destroy — suppression physique interdite) ──
        Route::resource('utilisateurs', UtilisateursController::class)->except(['destroy']);

        // Actions métier sur un compte (POST pour éviter les requêtes GET indésirables)
        Route::post('utilisateurs/{utilisateur}/toggle-statut',
            [UtilisateursController::class, 'toggleStatut'])->name('utilisateurs.toggle-statut');
        Route::post('utilisateurs/{utilisateur}/reset-password',
            [UtilisateursController::class, 'resetPassword'])->name('utilisateurs.reset-password');

        // ── Profil de l'admin connecté ───────────────────────────────────────
        // La route password est déclarée avant profil/update pour éviter tout conflit
        Route::get('profil',           [AdminProfilController::class, 'index'])->name('profil.index');
        Route::put('profil',           [AdminProfilController::class, 'update'])->name('profil.update');
        Route::put('profil/password',  [AdminProfilController::class, 'updatePassword'])->name('profil.password');

        // ── Paramètres système (lecture + mise à jour) ───────────────────────
        Route::get('parametres',  [ParametresController::class, 'index'])->name('parametres.index');
        Route::post('parametres', [ParametresController::class, 'update'])->name('parametres.update');

        // ════════════════════════════════════════════════════════════════════
        // MODULE 8 — Notifications in-app (espace admin)
        // ════════════════════════════════════════════════════════════════════

        // Déclaré avant la route avec {id} pour éviter que "toutes-lues"
        // soit capturé comme paramètre de marquerLue.
        Route::post('notifications/toutes-lues',
            [AdminNotificationsController::class, 'marquerToutesLues'])
            ->name('notifications.toutes-lues');

        Route::get('notifications',
            [AdminNotificationsController::class, 'index'])
            ->name('notifications.index');

        Route::post('notifications/{id}/lue',
            [AdminNotificationsController::class, 'marquerLue'])
            ->name('notifications.lue');

        // ════════════════════════════════════════════════════════════════════
        // MODULE 7 — Journal d'audit
        // ════════════════════════════════════════════════════════════════════

        // Liste paginée des actions tracées (lecture seule)
        Route::get('journaux', [JournauxController::class, 'index'])->name('journaux.index');

        // Export PDF du journal filtré (500 lignes max)
        Route::get('journaux/exporter', [JournauxController::class, 'exporter'])->name('journaux.exporter');

    });
});

// ════════════════════════════════════════════════════════════════════════════
// ESPACE INDUSTRIEL — préfixe /industriel
// ════════════════════════════════════════════════════════════════════════════
Route::prefix('industriel')->name('industriel.')->group(function () {

    // ── Authentification publique ────────────────────────────────────────────
    Route::get('/login',  [IndustrielAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [IndustrielAuthController::class, 'login'])->name('login.submit');

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

        // ── MODULE 3 — Déclarations (vue industriel) ─────────────────────────
        // CRUD standard + correction des déclarations rejetées
        // La route store est protégée par le rate limiter (10 soumissions/heure)
        Route::resource('declarations', IndustrielDeclarationsController::class)
             ->only(['index', 'create', 'show', 'edit', 'update']);
        Route::post('declarations', [IndustrielDeclarationsController::class, 'store'])
             ->middleware('rate.declarations')
             ->name('declarations.store');

        // ── MODULE 8 — Notifications in-app ──────────────────────────────────
        // Déclaré avant toutes-lues pour éviter que "toutes-lues" soit capturé
        // comme {id} par la route marquerLue.
        // ── Profil de l'industriel connecté ─────────────────────────────────
        Route::get('profil', [IndustrielProfilController::class, 'index'])->name('profil.index');
        Route::put('profil', [IndustrielProfilController::class, 'update'])->name('profil.update');

        Route::post('notifications/toutes-lues',
            [IndustrielNotificationsController::class, 'marquerToutesLues'])
            ->name('notifications.toutes-lues');

        Route::get('notifications',
            [IndustrielNotificationsController::class, 'index'])
            ->name('notifications.index');

        Route::post('notifications/{id}/lue',
            [IndustrielNotificationsController::class, 'marquerLue'])
            ->name('notifications.lue');
    });
});
