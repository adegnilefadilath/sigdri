<?php

namespace App\Http\Controllers\Industriel;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôleur du tableau de bord industriel — SIGDRI
 *
 * Récupère les données propres à l'industriel connecté :
 *   - son unité industrielle
 *   - ses déclarations (total, statuts)
 *   - son agrément en cours
 *   - la période déclarative active
 *   - sa dernière déclaration soumise
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = Auth::user();
        $uniteId     = $utilisateur->unite_industrielle_id;

        // ── Unité industrielle liée au compte ─────────────────────────────────
        $unite = $uniteId
            ? DB::table('unites_industrielles')->where('id', $uniteId)->first()
            : null;

        // ── Statistiques des déclarations de cet industriel ───────────────────
        $totalDeclarations = DB::table('declarations')
            ->where('declarant_id', $utilisateur->id)
            ->count();

        // Décompte par statut pour les badges du dashboard
        $declarationsParStatut = DB::table('declarations')
            ->where('declarant_id', $utilisateur->id)
            ->select('statut', DB::raw('COUNT(*) as total'))
            ->groupBy('statut')
            ->pluck('total', 'statut')   // ['soumise' => 3, 'validee' => 5, …]
            ->toArray();

        // ── Agrément de l'unité industrielle ─────────────────────────────────
        // On prend l'agrément le plus récent (date de délivrance la plus haute)
        $agrement = $uniteId
            ? DB::table('agrements')
                ->where('unite_industrielle_id', $uniteId)
                ->orderByDesc('date_delivrance')
                ->first()
            : null;

        // ── Période déclarative en cours ──────────────────────────────────────
        $periodeCourante = DB::table('periodes')
            ->where('statut', 'ouverte')
            ->orderByDesc('date_debut')
            ->first();

        // ── Dernière déclaration soumise par cet industriel ───────────────────
        $derniereDeclaration = DB::table('declarations')
            ->where('declarant_id', $utilisateur->id)
            ->orderByDesc('created_at')
            ->first();

        // ── Nombre de produits déclarés sur l'ensemble des déclarations ───────
        $totalProduits = $uniteId
            ? DB::table('produits')
                ->where('unite_industrielle_id', $uniteId)
                ->where('actif', true)
                ->count()
            : 0;

        return view('industriel.dashboard', compact(
            'utilisateur',
            'unite',
            'totalDeclarations',
            'declarationsParStatut',
            'agrement',
            'periodeCourante',
            'derniereDeclaration',
            'totalProduits',
        ));
    }
}
