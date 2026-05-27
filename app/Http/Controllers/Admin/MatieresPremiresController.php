<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôleur — Consultation des matières premières (espace admin)
 *
 * Les matières premières sont saisies par les industriels lors de leurs déclarations.
 * Ce contrôleur offre une vue agrégée (par désignation) avec un indicateur de
 * disponibilité calculé à partir de la date de la dernière déclaration les mentionnant.
 */
class MatieresPremiresController extends Controller
{
    // ── Liste agrégée avec disponibilité calculée ───────────────────────────
    public function index(Request $request): View
    {
        // Agrégation des matières premières par désignation + origine
        // On calcule la date de dernière utilisation via la date de soumission
        // de la déclaration associée, pour déterminer la disponibilité.
        $query = DB::table('matieres_premieres as mp')
            ->join('declarations as d', 'mp.declaration_id', '=', 'd.id')
            ->select(
                'mp.designation',
                'mp.origine',
                DB::raw('COUNT(DISTINCT mp.declaration_id) as nb_declarations'),
                DB::raw('SUM(mp.quantite_consommee) as quantite_totale'),
                DB::raw('SUM(mp.valeur_fcfa) as valeur_totale'),
                DB::raw('MAX(d.date_soumission) as derniere_utilisation'),
                // Disponibilité : < 90 j = Disponible, 90-180 = Tension, > 180 = Rupture
                DB::raw('CASE
                    WHEN MAX(d.date_soumission) IS NULL THEN "Inconnu"
                    WHEN DATEDIFF(NOW(), MAX(d.date_soumission)) <= 90 THEN "Disponible"
                    WHEN DATEDIFF(NOW(), MAX(d.date_soumission)) <= 180 THEN "Tension"
                    ELSE "Rupture"
                END as disponibilite'),
                DB::raw('GROUP_CONCAT(DISTINCT mp.unite_mesure ORDER BY mp.unite_mesure SEPARATOR ", ") as unites_mesure')
            )
            ->groupBy('mp.designation', 'mp.origine')
            ->orderBy('mp.designation');

        // Filtre par origine (locale / importée)
        if ($request->filled('origine')) {
            $query->where('mp.origine', $request->origine);
        }

        // Filtre par disponibilité (calculée avec HAVING)
        if ($request->filled('disponibilite')) {
            $disponibilite = $request->disponibilite;
            if ($disponibilite === 'Disponible') {
                $query->havingRaw('DATEDIFF(NOW(), MAX(d.date_soumission)) <= 90');
            } elseif ($disponibilite === 'Tension') {
                $query->havingRaw('DATEDIFF(NOW(), MAX(d.date_soumission)) > 90')
                      ->havingRaw('DATEDIFF(NOW(), MAX(d.date_soumission)) <= 180');
            } elseif ($disponibilite === 'Rupture') {
                $query->havingRaw('DATEDIFF(NOW(), MAX(d.date_soumission)) > 180');
            }
        }

        // Recherche par désignation
        if ($request->filled('recherche')) {
            $query->where('mp.designation', 'like', '%' . $request->recherche . '%');
        }

        $matieres = $query->paginate(25)->withQueryString();

        // Statistiques globales
        $stats = DB::table('matieres_premieres as mp')
            ->join('declarations as d', 'mp.declaration_id', '=', 'd.id')
            ->selectRaw('
                COUNT(DISTINCT mp.designation) as nb_matieres_distinctes,
                SUM(mp.valeur_fcfa) as valeur_totale,
                COUNT(CASE WHEN mp.origine = "locale" THEN 1 END) as nb_locale,
                COUNT(CASE WHEN mp.origine = "importee" THEN 1 END) as nb_importee
            ')
            ->first();

        return view('admin.matieres.index', compact('matieres', 'stats'));
    }

    // ── Détail d'une matière première avec historique des déclarations ──────
    public function show(Request $request, string $designation): View
    {
        // Décodage de la désignation passée en paramètre URL
        $designation = urldecode($designation);

        // Historique des déclarations utilisant cette matière première
        $historique = DB::table('matieres_premieres as mp')
            ->join('declarations as d', 'mp.declaration_id', '=', 'd.id')
            ->join('unites_industrielles as u', 'd.unite_industrielle_id', '=', 'u.id')
            ->select(
                'mp.*',
                'd.mois',
                'd.annee',
                'd.statut as statut_declaration',
                'd.date_soumission',
                'u.denomination as denomination_unite',
                'u.secteur_activite'
            )
            ->where('mp.designation', $designation)
            ->orderByDesc('d.annee')
            ->orderByDesc('d.mois')
            ->paginate(20)
            ->withQueryString();

        // Indicateurs agrégés pour cette matière
        $agregats = DB::table('matieres_premieres as mp')
            ->join('declarations as d', 'mp.declaration_id', '=', 'd.id')
            ->where('mp.designation', $designation)
            ->selectRaw('
                SUM(mp.quantite_consommee) as quantite_totale,
                SUM(mp.valeur_fcfa) as valeur_totale,
                COUNT(DISTINCT mp.declaration_id) as nb_declarations,
                COUNT(DISTINCT d.unite_industrielle_id) as nb_unites,
                MAX(d.date_soumission) as derniere_utilisation,
                GROUP_CONCAT(DISTINCT mp.origine) as origines,
                GROUP_CONCAT(DISTINCT mp.unite_mesure ORDER BY mp.unite_mesure SEPARATOR ", ") as unites_mesure
            ')
            ->first();

        abort_if($historique->isEmpty() && $historique->currentPage() === 1, 404, 'Matière première introuvable.');

        return view('admin.matieres.show', compact('designation', 'historique', 'agregats'));
    }
}
