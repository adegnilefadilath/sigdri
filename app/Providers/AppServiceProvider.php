<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // ── View Composers — Cloche de notifications ──────────────────────────
        //
        // Injecte automatiquement $notifNonLues (int) et $notifRecentes (Collection)
        // dans les deux layouts qui affichent la cloche.
        //
        // Avantage : les contrôleurs et les vues enfants n'ont rien à faire ;
        // les variables sont disponibles dès que le layout est rendu.
        //
        // Guard : si l'utilisateur n'est pas connecté (page de login, erreur 500…),
        // on renvoie des valeurs neutres pour éviter toute exception.
        View::composer(['layouts.app', 'layouts.industriel'], function ($view): void {
            $nonLues  = 0;
            $recentes = collect();

            if (Auth::check()) {
                $userId = Auth::id();

                $nonLues = DB::table('notifications')
                    ->where('utilisateur_id', $userId)
                    ->where('lu', false)
                    ->count();

                $recentes = DB::table('notifications')
                    ->where('utilisateur_id', $userId)
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get();
            }

            $view->with([
                'notifNonLues'  => $nonLues,
                'notifRecentes' => $recentes,
            ]);
        });
    }
}
