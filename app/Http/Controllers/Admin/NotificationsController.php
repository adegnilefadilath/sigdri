<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôleur admin — Notifications in-app
 *
 * Permet aux agents admin de consulter leurs notifications,
 * de les marquer comme lues individuellement ou en totalité.
 */
class NotificationsController extends Controller
{
    // ── Liste paginée des notifications de l'admin connecté ──────────────────

    public function index(): View
    {
        $notifications = DB::table('notifications')
            ->where('utilisateur_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(25);

        $nonLues = DB::table('notifications')
            ->where('utilisateur_id', Auth::id())
            ->where('lu', false)
            ->count();

        return view('admin.notifications.index', compact('notifications', 'nonLues'));
    }

    // ── Marquer une notification comme lue ───────────────────────────────────

    public function marquerLue(int $id): RedirectResponse
    {
        DB::table('notifications')
            ->where('id', $id)
            ->where('utilisateur_id', Auth::id())   // seul le destinataire peut marquer
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
