<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôleur admin — Module 5 : Alertes et surveillance
 *
 * Surveille trois types d'événements nécessitant une action de l'administration :
 *   1. Agréments valides dont l'expiration approche (≤ 30 jours)
 *   2. Agréments déjà expirés (statut = 'expire')
 *   3. Déclarations soumises en attente depuis plus de 7 jours sans traitement
 *
 * Les alertes traitées sont archivées dans la table alertes_traitees pour
 * ne plus apparaître dans la liste active. Elles restent consultables en base.
 */
class AlertesController extends Controller
{
    // ── Page principale — affiche les trois catégories d'alertes actives ───────
    public function index(): View
    {
        // Identifiants déjà traités — chargés une fois, regroupés par type
        $traitees = DB::table('alertes_traitees')
            ->get(['type', 'reference_id'])
            ->groupBy('type')
            ->map(fn ($items) => $items->pluck('reference_id')->all());

        $idsFiltres = fn (string $type): array => $traitees->get($type, []);

        // ── 1. Agréments valides expirant dans les 30 prochains jours ────────
        $expirantBientot = $this->agrementsExpirant($idsFiltres('agrement_expirant'));

        // ── 2. Agréments expirés (statut = 'expire') non encore traités ──────
        $expires = DB::table('agrements as a')
            ->join('unites_industrielles as u', 'a.unite_industrielle_id', '=', 'u.id')
            ->select(
                'a.id', 'a.numero_agrement', 'a.type_agrement',
                'a.date_expiration', 'a.statut',
                'u.denomination as denomination_unite',
                'u.departement  as departement_unite'
            )
            ->where('a.statut', 'expire')
            ->whereNotIn('a.id', $idsFiltres('agrement_expire'))
            ->orderBy('a.date_expiration')
            ->get()
            ->map(function ($a) {
                // Nombre de jours écoulés depuis l'expiration (valeur positive)
                $a->jours_ecoules = max(0, (int) floor(
                    (time() - strtotime($a->date_expiration)) / 86400
                ));
                return $a;
            });

        // ── 3. Déclarations soumises sans réponse depuis plus de 7 jours ─────
        $declarationsEnAttente = DB::table('declarations as d')
            ->join('unites_industrielles as u', 'd.unite_industrielle_id', '=', 'u.id')
            ->select(
                'd.id', 'd.numero_declaration', 'd.mois', 'd.annee',
                'd.date_soumission', 'd.statut',
                'u.denomination as denomination_unite',
                'u.departement  as departement_unite'
            )
            ->where('d.statut', 'soumise')
            ->where('d.date_soumission', '<', now()->subDays(7))
            ->whereNotIn('d.id', $idsFiltres('declaration_en_attente'))
            ->orderBy('d.date_soumission')
            ->get()
            ->map(function ($d) {
                // Nombre de jours d'attente depuis la soumission
                $d->jours_attente = max(0, (int) floor(
                    (time() - strtotime($d->date_soumission)) / 86400
                ));
                return $d;
            });

        // ── Compteurs globaux pour les cartes du haut de page ─────────────────
        $compteurs = [
            'expirant_bientot'     => $expirantBientot->count(),
            'expires'              => $expires->count(),
            'declarations_attente' => $declarationsEnAttente->count(),
            'total'                => $expirantBientot->count()
                                    + $expires->count()
                                    + $declarationsEnAttente->count(),
        ];

        return view('admin.alertes.index', compact(
            'expirantBientot', 'expires', 'declarationsEnAttente', 'compteurs'
        ));
    }

    // ── Marquer une alerte comme traitée ──────────────────────────────────────
    // Le champ caché "type" dans le formulaire identifie la catégorie d'alerte.
    // L'unicité (type, reference_id) est garantie en base — double-clic sans risque.
    public function marquerTraitee(Request $request, int $id): RedirectResponse
    {
        $donnees = $request->validate([
            'type' => ['required', 'in:agrement_expirant,agrement_expire,declaration_en_attente'],
        ], [
            'type.required' => 'Le type d\'alerte est manquant.',
            'type.in'       => 'Type d\'alerte invalide.',
        ]);

        // Récupère le nom de l'agent connecté pour la traçabilité
        $agent = Auth::user()
            ? trim((Auth::user()->prenom ?? '') . ' ' . (Auth::user()->nom ?? ''))
            : 'Inconnu';

        // updateOrInsert : idempotent si l'alerte a déjà été traitée
        DB::table('alertes_traitees')->updateOrInsert(
            ['type' => $donnees['type'], 'reference_id' => $id],
            ['traitee_par' => $agent, 'created_at' => now()]
        );

        return redirect()->route('admin.alertes.index')
            ->with('statut', 'Alerte marquée comme traitée.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Méthodes privées
    // ══════════════════════════════════════════════════════════════════════════

    // ── Agréments valides dont la date d'expiration est dans les 30 prochains jours
    // Exclut les identifiants déjà traités passés en paramètre.
    private function agrementsExpirant(array $exclusions): \Illuminate\Support\Collection
    {
        return DB::table('agrements as a')
            ->join('unites_industrielles as u', 'a.unite_industrielle_id', '=', 'u.id')
            ->select(
                'a.id', 'a.numero_agrement', 'a.type_agrement',
                'a.date_expiration', 'a.statut',
                'u.denomination as denomination_unite',
                'u.departement  as departement_unite'
            )
            ->where('a.statut', 'valide')
            ->whereNotNull('a.date_expiration')
            ->whereBetween('a.date_expiration', [
                now()->toDateString(),
                now()->addDays(30)->toDateString(),
            ])
            ->whereNotIn('a.id', $exclusions)
            ->orderBy('a.date_expiration')
            ->get()
            ->map(function ($a) {
                // Nombre de jours restants avant expiration (0 = expire aujourd'hui)
                $a->jours_restants = max(0, (int) floor(
                    (strtotime($a->date_expiration) - time()) / 86400
                ));
                return $a;
            });
    }
}
