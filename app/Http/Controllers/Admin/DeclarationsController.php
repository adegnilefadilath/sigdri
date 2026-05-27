<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\JournalService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôleur admin — Gestion des déclarations industrielles
 *
 * L'inspecteur peut consulter toutes les déclarations soumises, les valider
 * ou les rejeter avec motif, et exporter la liste au format CSV.
 * Les déclarations sont identifiées par mois + année (plus de notion de période).
 */
class DeclarationsController extends Controller
{
    public function __construct(
        private JournalService $journal,
        private NotificationService $notifications,
    ) {}

    // ── Liste avec filtres (mois, année, statut, département, secteur) ─────────
    public function index(Request $request): View
    {
        $query = DB::table('declarations')
            ->join('unites_industrielles', 'declarations.unite_industrielle_id', '=', 'unites_industrielles.id')
            ->join('utilisateurs as declarant', 'declarations.declarant_id', '=', 'declarant.id')
            ->select(
                'declarations.id',
                'declarations.numero_declaration',
                'declarations.statut',
                'declarations.date_soumission',
                'declarations.chiffre_affaires_total',
                'declarations.mois',
                'declarations.annee',
                'unites_industrielles.denomination as denomination_unite',
                'unites_industrielles.departement as departement_unite',
                'unites_industrielles.secteur_activite',
                DB::raw("CONCAT(declarant.prenom, ' ', declarant.nom) as declarant_nom")
            )
            ->orderByDesc('declarations.annee')
            ->orderByDesc('declarations.mois')
            ->orderByDesc('declarations.created_at');

        // Filtre par mois
        if ($request->filled('mois')) {
            $query->where('declarations.mois', (int) $request->mois);
        }

        // Filtre par année
        if ($request->filled('annee')) {
            $query->where('declarations.annee', (int) $request->annee);
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('declarations.statut', $request->statut);
        }

        // Filtre par département
        if ($request->filled('departement')) {
            $query->where('unites_industrielles.departement', $request->departement);
        }

        // Filtre par secteur (recherche partielle)
        if ($request->filled('secteur')) {
            $query->where('unites_industrielles.secteur_activite', 'like', '%' . $request->secteur . '%');
        }

        $declarations = $query->paginate(25)->withQueryString();

        // Données pour les sélecteurs de filtres
        $departements = DB::table('unites_industrielles')
            ->distinct()->orderBy('departement')->pluck('departement');

        // Années présentes dans les déclarations (pour le sélecteur)
        $annees = DB::table('declarations')
            ->distinct()->orderByDesc('annee')->pluck('annee');

        // Compteurs par statut (toutes déclarations, sans filtre)
        $compteurs = DB::table('declarations')
            ->selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        return view('admin.declarations.index', compact(
            'declarations', 'departements', 'annees', 'compteurs'
        ));
    }

    // ── Détail d'une déclaration avec lignes produits et matières premières ──
    public function show(int $declaration): View
    {
        $d = DB::table('declarations')
            ->join('unites_industrielles', 'declarations.unite_industrielle_id', '=', 'unites_industrielles.id')
            ->join('utilisateurs as declarant', 'declarations.declarant_id', '=', 'declarant.id')
            ->leftJoin('utilisateurs as validateur', 'declarations.validateur_id', '=', 'validateur.id')
            ->select(
                'declarations.*',
                'unites_industrielles.denomination as denomination_unite',
                'unites_industrielles.departement as departement_unite',
                'unites_industrielles.secteur_activite',
                'unites_industrielles.commune as commune_unite',
                DB::raw("CONCAT(declarant.prenom, ' ', declarant.nom) as declarant_nom"),
                DB::raw("CONCAT(validateur.prenom, ' ', validateur.nom) as validateur_nom")
            )
            ->where('declarations.id', $declaration)
            ->first();

        abort_if(! $d, 404, 'Déclaration introuvable.');

        // Lignes de production (produits déclarés)
        $lignes = DB::table('lignes_declaration')
            ->join('produits', 'lignes_declaration.produit_id', '=', 'produits.id')
            ->where('lignes_declaration.declaration_id', $declaration)
            ->select(
                'lignes_declaration.*',
                'produits.designation',
                'produits.unite_mesure',
                'produits.code_produit'
            )
            ->get();

        // Matières premières consommées
        $matieres = DB::table('matieres_premieres')
            ->where('declaration_id', $declaration)
            ->orderBy('origine')
            ->get();

        return view('admin.declarations.show', compact('d', 'lignes', 'matieres'));
    }

    // ── Validation d'une déclaration soumise ─────────────────────────────────
    public function valider(int $declaration): RedirectResponse
    {
        $d = DB::table('declarations')->where('id', $declaration)->first();
        abort_if(! $d, 404);

        if (! in_array($d->statut, ['soumise', 'en_revision'])) {
            return back()->with('erreur', 'Cette déclaration ne peut pas être validée dans son état actuel.');
        }

        $this->journal->log('validation', "Validation de la déclaration {$d->numero_declaration}", null, [
            'table' => 'declarations',
            'id'    => $declaration,
            'avant' => ['statut' => $d->statut],
            'apres' => ['statut' => 'validee'],
        ]);

        DB::table('declarations')->where('id', $declaration)->update([
            'statut'          => 'validee',
            'validateur_id'   => Auth::id(),
            'date_validation' => now(),
            'motif_rejet'     => null,
            'updated_at'      => now(),
        ]);

        // Notification au déclarant (in-app + email)
        $this->notifications->notifierDeclarationValidee(
            $d->declarant_id,
            $d->numero_declaration,
        );

        return redirect()->route('admin.declarations.show', $declaration)
            ->with('statut', 'Déclaration « ' . $d->numero_declaration . ' » validée avec succès.');
    }

    // ── Rejet avec motif obligatoire ─────────────────────────────────────────
    public function rejeter(Request $request, int $declaration): RedirectResponse
    {
        $d = DB::table('declarations')->where('id', $declaration)->first();
        abort_if(! $d, 404);

        if (! in_array($d->statut, ['soumise', 'en_revision'])) {
            return back()->with('erreur', 'Cette déclaration ne peut pas être rejetée dans son état actuel.');
        }

        $request->validate([
            'motif_rejet' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'motif_rejet.required' => 'Le motif de rejet est obligatoire.',
            'motif_rejet.min'      => 'Le motif doit comporter au moins 10 caractères.',
        ]);

        $this->journal->log('rejet', "Rejet de la déclaration {$d->numero_declaration}", null, [
            'table' => 'declarations',
            'id'    => $declaration,
            'avant' => ['statut' => $d->statut],
            'apres' => ['statut' => 'rejetee', 'motif_rejet' => $request->motif_rejet],
        ]);

        DB::table('declarations')->where('id', $declaration)->update([
            'statut'          => 'rejetee',
            'validateur_id'   => Auth::id(),
            'date_validation' => now(),
            'motif_rejet'     => $request->motif_rejet,
            'updated_at'      => now(),
        ]);

        // Notification au déclarant (in-app + email)
        $this->notifications->notifierDeclarationRejetee(
            $d->declarant_id,
            $d->numero_declaration,
            $request->motif_rejet,
        );

        return redirect()->route('admin.declarations.show', $declaration)
            ->with('statut', 'Déclaration « ' . $d->numero_declaration . ' » rejetée.');
    }

    // ── Export CSV ouvrable sous Excel ────────────────────────────────────────
    public function exporter(Request $request): Response
    {
        $query = DB::table('declarations')
            ->join('unites_industrielles', 'declarations.unite_industrielle_id', '=', 'unites_industrielles.id')
            ->select(
                'declarations.numero_declaration',
                'declarations.mois',
                'declarations.annee',
                'declarations.statut',
                'declarations.date_soumission',
                'declarations.chiffre_affaires_total',
                'unites_industrielles.denomination as denomination_unite',
                'unites_industrielles.departement as departement_unite',
                'unites_industrielles.secteur_activite'
            )
            ->orderByDesc('declarations.annee')
            ->orderByDesc('declarations.mois');

        // Reprend les mêmes filtres que index()
        if ($request->filled('mois'))       { $query->where('declarations.mois', (int) $request->mois); }
        if ($request->filled('annee'))      { $query->where('declarations.annee', (int) $request->annee); }
        if ($request->filled('statut'))     { $query->where('declarations.statut', $request->statut); }
        if ($request->filled('departement')){ $query->where('unites_industrielles.departement', $request->departement); }
        if ($request->filled('secteur'))    { $query->where('unites_industrielles.secteur_activite', 'like', '%' . $request->secteur . '%'); }

        $rows = $query->get();
        $nom  = 'declarations_' . date('Y-m-d_H-i') . '.csv';

        $callback = function () use ($rows) {
            $fp = fopen('php://output', 'w');
            // BOM UTF-8 pour qu'Excel détecte l'encodage correctement
            fwrite($fp, "\xEF\xBB\xBF");
            fputcsv($fp, [
                'N° Déclaration', 'Unité industrielle', 'Département', 'Secteur',
                'Mois', 'Année', 'Statut', 'CA Total (FCFA)', 'Date soumission',
            ], ';');
            foreach ($rows as $r) {
                fputcsv($fp, [
                    $r->numero_declaration,
                    $r->denomination_unite,
                    $r->departement_unite,
                    $r->secteur_activite,
                    $this->libelleMois($r->mois),
                    $r->annee,
                    $r->statut,
                    number_format($r->chiffre_affaires_total, 2, ',', ' '),
                    $r->date_soumission ? date('d/m/Y H:i', strtotime($r->date_soumission)) : '',
                ], ';');
            }
            fclose($fp);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nom . '"',
        ]);
    }

    // ── Libellé d'un mois (ex : 5 → "Mai") ──────────────────────────────────
    private function libelleMois(int $mois): string
    {
        return ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'][$mois] ?? '?';
    }

}
