<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôleur admin — Module 7 : Journal d'audit
 *
 * Affiche toutes les actions traçées par le JournalService avec filtres,
 * pagination (50 entrées par page) et export PDF.
 * Le journal est en lecture seule : aucune modification n'est possible.
 */
class JournauxController extends Controller
{
    // ── Liste paginée des entrées de journal avec filtres ────────────────────
    public function index(Request $request): View
    {
        $query = DB::table('journaux as j')
            ->leftJoin('utilisateurs as u', 'j.utilisateur_id', '=', 'u.id')
            ->select(
                'j.id',
                'j.action',
                'j.description',
                'j.table_concernee',
                'j.enregistrement_id',
                'j.ip_address',
                'j.created_at',
                DB::raw("TRIM(CONCAT(COALESCE(u.prenom,''), ' ', COALESCE(u.nom,''))) as auteur"),
                'u.role as auteur_role',
                'u.email as auteur_email'
            )
            ->orderByDesc('j.created_at');

        // ── Filtre par type d'action ──────────────────────────────────────────
        if ($request->filled('action')) {
            $query->where('j.action', $request->action);
        }

        // ── Filtre par utilisateur (nom, prénom ou e-mail) ────────────────────
        if ($request->filled('utilisateur')) {
            $terme = '%' . $request->utilisateur . '%';
            $query->where(function ($q) use ($terme) {
                $q->where('u.nom',    'like', $terme)
                  ->orWhere('u.prenom', 'like', $terme)
                  ->orWhere('u.email',  'like', $terme);
            });
        }

        // ── Filtre par date de début (inclusif) ───────────────────────────────
        if ($request->filled('date_debut')) {
            $query->where('j.created_at', '>=', $request->date_debut . ' 00:00:00');
        }

        // ── Filtre par date de fin (inclusif) ─────────────────────────────────
        if ($request->filled('date_fin')) {
            $query->where('j.created_at', '<=', $request->date_fin . ' 23:59:59');
        }

        // 50 entrées par page — le journal peut être volumineux
        $journaux = $query->paginate(50)->withQueryString();

        // Valeurs distinctes pour le sélecteur "Type d'action"
        $actionsDisponibles = DB::table('journaux')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        // Compteur global (sans filtre) pour l'en-tête de page
        $total = DB::table('journaux')->count();

        return view('admin.journaux.index', compact('journaux', 'actionsDisponibles', 'total'));
    }

    // ── Export PDF du journal filtré ─────────────────────────────────────────
    // Exporte les 500 dernières entrées correspondant aux filtres actifs.
    // Limité à 500 lignes pour éviter les PDFs trop lourds.
    public function exporter(Request $request): Response
    {
        $query = DB::table('journaux as j')
            ->leftJoin('utilisateurs as u', 'j.utilisateur_id', '=', 'u.id')
            ->select(
                'j.action', 'j.description', 'j.ip_address', 'j.created_at',
                DB::raw("TRIM(CONCAT(COALESCE(u.prenom,''), ' ', COALESCE(u.nom,''))) as auteur")
            )
            ->orderByDesc('j.created_at');

        if ($request->filled('action')) {
            $query->where('j.action', $request->action);
        }
        if ($request->filled('utilisateur')) {
            $terme = '%' . $request->utilisateur . '%';
            $query->where(function ($q) use ($terme) {
                $q->where('u.nom', 'like', $terme)->orWhere('u.prenom', 'like', $terme);
            });
        }
        if ($request->filled('date_debut')) {
            $query->where('j.created_at', '>=', $request->date_debut . ' 00:00:00');
        }
        if ($request->filled('date_fin')) {
            $query->where('j.created_at', '<=', $request->date_fin . ' 23:59:59');
        }

        $journaux = $query->limit(500)->get();

        $pdf = Pdf::loadView('pdf.journaux', compact('journaux'))
            ->setPaper('A4', 'landscape');

        return $pdf->download('journal-audit-sigdri-' . now()->format('Y-m-d') . '.pdf');
    }
}
