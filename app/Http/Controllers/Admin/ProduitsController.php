<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\JournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôleur — Gestion du catalogue de produits finis (espace admin)
 *
 * Permet de recenser les produits fabriqués par chaque unité industrielle.
 * La suppression physique est interdite : on désactive (actif = false).
 */
class ProduitsController extends Controller
{
    public function __construct(private JournalService $journal) {}

    // ── Liste avec filtres et pagination ────────────────────────────────────
    public function index(Request $request): View
    {
        $query = DB::table('produits')
            ->join('unites_industrielles', 'produits.unite_industrielle_id', '=', 'unites_industrielles.id')
            ->select(
                'produits.*',
                'unites_industrielles.denomination   as denomination_unite',
                'unites_industrielles.secteur_activite as secteur'
            )
            ->orderBy('unites_industrielles.denomination')
            ->orderBy('produits.designation');

        // Filtre par secteur d'activité (issu de l'unité industrielle)
        if ($request->filled('secteur')) {
            $query->where('unites_industrielles.secteur_activite', 'like', '%' . $request->secteur . '%');
        }

        // Filtre par statut actif / inactif
        if ($request->filled('statut')) {
            $query->where('produits.actif', $request->statut === 'actif' ? 1 : 0);
        }

        // Filtre par unité industrielle
        if ($request->filled('unite_id')) {
            $query->where('produits.unite_industrielle_id', (int) $request->unite_id);
        }

        $produits = $query->paginate(20)->withQueryString();

        // Données pour les selects de filtres
        $secteurs = DB::table('unites_industrielles')
            ->whereNotNull('secteur_activite')
            ->distinct()
            ->orderBy('secteur_activite')
            ->pluck('secteur_activite');

        $unites = DB::table('unites_industrielles')
            ->where('actif', true)
            ->orderBy('denomination')
            ->get(['id', 'denomination']);

        $total       = DB::table('produits')->count();
        $totalActifs = DB::table('produits')->where('actif', true)->count();

        return view('admin.produits.index', compact('produits', 'secteurs', 'unites', 'total', 'totalActifs'));
    }

    // ── Formulaire de création ──────────────────────────────────────────────
    public function create(): View
    {
        $unites = DB::table('unites_industrielles')
            ->where('actif', true)
            ->orderBy('denomination')
            ->get(['id', 'denomination']);

        return view('admin.produits.create', compact('unites'));
    }

    // ── Enregistrement d'un nouveau produit ─────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'unite_industrielle_id' => ['required', 'integer', 'exists:unites_industrielles,id'],
            'designation'           => ['required', 'string', 'max:200'],
            'code_produit'          => ['nullable', 'string', 'max:50'],
            'unite_mesure'          => ['required', 'string', 'max:30'],
            'description'           => ['nullable', 'string', 'max:1000'],
        ], $this->messagesValidation());

        $id = DB::table('produits')->insertGetId([
            'unite_industrielle_id' => $donnees['unite_industrielle_id'],
            'designation'           => $donnees['designation'],
            'code_produit'          => $donnees['code_produit'] ?? null,
            'unite_mesure'          => $donnees['unite_mesure'],
            'description'           => $donnees['description'] ?? null,
            'actif'                 => true,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        $this->journal->log('creation', "Ajout du produit « {$donnees['designation']} »", null, [
            'table' => 'produits',
            'id'    => $id,
            'apres' => $donnees,
        ]);

        return redirect()->route('admin.produits.index')
            ->with('statut', '« ' . $donnees['designation'] . ' » ajouté au catalogue avec succès.');
    }

    // ── Formulaire de modification ──────────────────────────────────────────
    public function edit(int $produit): View
    {
        $p = DB::table('produits')->where('id', $produit)->first();
        abort_if(! $p, 404, 'Produit introuvable.');

        $unites = DB::table('unites_industrielles')
            ->where('actif', true)
            ->orderBy('denomination')
            ->get(['id', 'denomination']);

        return view('admin.produits.edit', ['produit' => $p, 'unites' => $unites]);
    }

    // ── Mise à jour ─────────────────────────────────────────────────────────
    public function update(Request $request, int $produit): RedirectResponse
    {
        $p = DB::table('produits')->where('id', $produit)->first();
        abort_if(! $p, 404, 'Produit introuvable.');

        $donnees = $request->validate([
            'unite_industrielle_id' => ['required', 'integer', 'exists:unites_industrielles,id'],
            'designation'           => ['required', 'string', 'max:200'],
            'code_produit'          => ['nullable', 'string', 'max:50'],
            'unite_mesure'          => ['required', 'string', 'max:30'],
            'description'           => ['nullable', 'string', 'max:1000'],
        ], $this->messagesValidation());

        $this->journal->log('modification', "Modification du produit « {$p->designation} »", null, [
            'table' => 'produits',
            'id'    => $produit,
            'avant' => (array) $p,
            'apres' => $donnees,
        ]);

        DB::table('produits')
            ->where('id', $produit)
            ->update(array_merge($donnees, ['updated_at' => now()]));

        return redirect()->route('admin.produits.index')
            ->with('statut', '« ' . $donnees['designation'] . ' » mis à jour avec succès.');
    }

    // ── Désactivation (actif = false) ───────────────────────────────────────
    public function destroy(int $produit): RedirectResponse
    {
        $p = DB::table('produits')->where('id', $produit)->first();
        abort_if(! $p, 404, 'Produit introuvable.');

        $this->journal->log('desactivation', "Désactivation du produit « {$p->designation} »", null, [
            'table' => 'produits',
            'id'    => $produit,
            'avant' => ['actif' => true],
            'apres' => ['actif' => false],
        ]);

        DB::table('produits')
            ->where('id', $produit)
            ->update(['actif' => false, 'updated_at' => now()]);

        return redirect()->route('admin.produits.index')
            ->with('statut', '« ' . $p->designation . ' » a été désactivé du catalogue.');
    }

    // ── Messages de validation partagés ────────────────────────────────────
    private function messagesValidation(): array
    {
        return [
            'unite_industrielle_id.required' => 'Veuillez sélectionner une unité industrielle.',
            'unite_industrielle_id.exists'   => 'L\'unité industrielle sélectionnée est invalide.',
            'designation.required'           => 'La désignation du produit est obligatoire.',
            'designation.max'                => 'La désignation ne doit pas dépasser 200 caractères.',
            'code_produit.max'               => 'Le code produit ne doit pas dépasser 50 caractères.',
            'unite_mesure.required'          => 'L\'unité de mesure est obligatoire.',
            'unite_mesure.max'               => 'L\'unité de mesure ne doit pas dépasser 30 caractères.',
            'description.max'                => 'La description ne doit pas dépasser 1 000 caractères.',
        ];
    }
}
