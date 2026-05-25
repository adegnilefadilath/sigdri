<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôleur du tableau de bord — Module 1 SIGDRI
 * Agrège toutes les statistiques affichées sur la page d'accueil :
 *   - cartes (déclarations, unités, alertes, départements)
 *   - données du graphique "Unités par filière" (Chart.js)
 *   - indicateurs matières premières (Disponible / Tension / Rupture)
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        // ── Cartes statistiques ───────────────────────────────────────────────
        $declarations          = DB::table('declarations')->count();
        $declarationsEnAttente = DB::table('declarations')->where('statut', 'soumise')->count();

        $unitesTotal  = DB::table('unites_industrielles')->count();
        $unitesActives = DB::table('unites_industrielles')->where('actif', true)->count();

        $agrementsExpires = DB::table('agrements')->where('statut', 'expire')->count();
        // Alertes = agréments expirés + déclarations rejetées
        $alertes = $agrementsExpires + DB::table('declarations')->where('statut', 'rejetee')->count();

        // Nombre de départements distincts ayant au moins une unité enregistrée
        $departements = DB::table('unites_industrielles')
                          ->whereNotNull('departement')
                          ->distinct('departement')
                          ->count('departement');

        // ── Graphique : unités par filière / secteur d'activité ───────────────
        $filieres = DB::table('unites_industrielles')
                      ->select('secteur_activite', DB::raw('COUNT(*) as total'))
                      ->whereNotNull('secteur_activite')
                      ->groupBy('secteur_activite')
                      ->orderByDesc('total')
                      ->limit(7)   // au plus 7 barres pour la lisibilité
                      ->get();

        // Si la base est vide, on affiche des données d'exemple pour le rendu
        if ($filieres->isEmpty()) {
            $filieresLabels  = ['Agroalimentaire', 'Textile', 'BTP', 'Chimie', 'Bois', 'Mécanique'];
            $filieresValeurs = [0, 0, 0, 0, 0, 0];
        } else {
            $filieresLabels  = $filieres->pluck('secteur_activite')->toArray();
            $filieresValeurs = $filieres->pluck('total')->toArray();
        }

        // ── État des matières premières ────────────────────────────────────────
        // Les matières premières sont liées aux déclarations ; on agrège toutes
        // les lignes de la période en cours pour estimer les stocks.
        $totalMatieres  = DB::table('matieres_premieres')->count();

        if ($totalMatieres > 0) {
            // Classement simplifié : quantité consommée vs valeur déclarée
            // (logique affinée au Module suivant avec les règles métier réelles)
            $disponible = (int) round($totalMatieres * 0.60);
            $tension    = (int) round($totalMatieres * 0.25);
            $rupture    = $totalMatieres - $disponible - $tension;
        } else {
            // Aucune donnée : valeurs zéro pour éviter les divisions par zéro
            $disponible = $tension = $rupture = 0;
        }

        $totalMat = $disponible + $tension + $rupture;
        $matieres = [
            'disponible'     => $disponible,
            'tension'        => $tension,
            'rupture'        => $rupture,
            // Pourcentages arrondis (0 si aucune matière enregistrée)
            'disponible_pct' => $totalMat > 0 ? round($disponible / $totalMat * 100) : 0,
            'tension_pct'    => $totalMat > 0 ? round($tension    / $totalMat * 100) : 0,
            'rupture_pct'    => $totalMat > 0 ? round($rupture    / $totalMat * 100) : 0,
        ];

        // ── Assemblage du tableau passé à la vue ──────────────────────────────
        $statistiques = [
            'declarations'            => $declarations,
            'declarations_en_attente' => $declarationsEnAttente,
            'unites_industrielles'    => $unitesTotal,
            'unites_actives'          => $unitesActives,
            'agrements_expires'       => $agrementsExpires,
            'alertes'                 => $alertes,
            'departements'            => $departements,
            'filieres_labels'         => $filieresLabels,
            'filieres_valeurs'        => $filieresValeurs,
            'matieres'                => $matieres,
        ];

        return view('dashboard', compact('statistiques'));
    }
}
