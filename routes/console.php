<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Tâche planifiée : vérification quotidienne des agréments expirés ────────
// Passe automatiquement au statut "expiré" tout agrément dont la date est dépassée.
// Exécution : chaque jour à 00h05 (heure serveur).
Schedule::command('agrement:verifier-expires')->dailyAt('00:05');
