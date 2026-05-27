<?php

namespace App\Http\Controllers\Industriel;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôleur — Notifications de l'espace industriel
 *
 * Permet à l'industriel de consulter ses notifications in-app,
 * de les marquer comme lues individuellement ou en totalité.
 */
class NotificationsController extends Controller
{
    // ── Liste paginée des notifications ──────────────────────────────────────

    public function index(): View
    {
        $notifications = DB::table('notifications')
            ->where('utilisateur_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(20);

        // Nombre de non-lues (pour afficher dans le titre de page)
        $nonLues = DB::table('notifications')
            ->where('utilisateur_id', Auth::id())
            ->where('lu', false)
            ->count();

        return view('industriel.notifications.index', compact('notifications', 'nonLues'));
    }

    // ── Marquer une notification comme lue ───────────────────────────────────

    public function marquerLue(int $id): RedirectResponse
    {
        DB::table('notifications')
            ->where('id', $id)
            ->where('utilisateur_id', Auth::id())   // sécurité : seul le destinataire peut marquer
            ->update(['lu' => true]);

        return back()->with('statut', 'Notification marquée comme lue.');
    }

    // ── Marquer toutes les notifications comme lues ───────────────────────────

    public function marquerToutesLues(): RedirectResponse
    {
        DB::table('notifications')
            ->where('utilisateur_id', Auth::id())
            ->where('lu', false)
            ->update(['lu' => true]);

        return back()->with('statut', 'Toutes les notifications ont été marquées comme lues.');
    }
}
