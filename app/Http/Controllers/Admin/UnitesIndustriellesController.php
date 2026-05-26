<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\JournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôleur — Gestion des unités industrielles (espace admin)
 *
 * Couvre l'intégralité du cycle de vie d'une unité :
 * création, consultation, modification et désactivation (soft delete).
 * Chaque opération d'écriture est tracée dans le journal d'audit.
 */
class UnitesIndustriellesController extends Controller
{
    public function __construct(private JournalService $journal) {}
    // ── Liste avec filtres et pagination ────────────────────────────────────
    public function index(Request $request): View
    {
        $query = DB::table('unites_industrielles')->orderByDesc('created_at');

        // Filtre par département (valeur exacte)
        if ($request->filled('departement')) {
            $query->where('departement', $request->departement);
        }

        // Filtre par secteur (recherche partielle)
        if ($request->filled('secteur')) {
            $query->where('secteur_activite', 'like', '%' . $request->secteur . '%');
        }

        // Filtre par statut actif / inactif
        if ($request->filled('statut')) {
            $query->where('actif', $request->statut === 'actif' ? 1 : 0);
        }

        $unites = $query->paginate(20)->withQueryString();

        // Valeurs distinctes pour le select départements
        $departements = DB::table('unites_industrielles')
            ->distinct()->orderBy('departement')->pluck('departement');

        $total        = DB::table('unites_industrielles')->count();
        $totalActives = DB::table('unites_industrielles')->where('actif', true)->count();

        return view('admin.unites.index', compact('unites', 'departements', 'total', 'totalActives'));
    }

    // ── Formulaire de création ──────────────────────────────────────────────
    public function create(): View
    {
        return view('admin.unites.create');
    }

    // ── Enregistrement d'une unité déjà autorisée ────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'denomination'              => ['required', 'string', 'max:200'],
            'numero_immatriculation'    => ['required', 'string', 'max:50',
                                            'unique:unites_industrielles,numero_immatriculation'],
            'secteur_activite'          => ['required', 'string', 'max:150'],
            'departement'               => ['required', 'string', 'max:100'],
            'commune'                   => ['required', 'string', 'max:100'],
            'adresse'                   => ['required', 'string', 'max:255'],
            'coordonnees_geographiques' => ['nullable', 'string', 'max:100'],
            'responsable_nom'           => ['required', 'string', 'max:150'],
            'responsable_fonction'      => ['nullable', 'string', 'max:100'],
            'email'                     => ['required', 'email', 'max:150'],
            'telephone'                 => ['required', 'string', 'max:20'],
            'nombre_employes'           => ['nullable', 'integer', 'min:0'],
            'capacite_production'       => ['nullable', 'string', 'max:255'],
        ], $this->messagesValidation());

        $id = DB::table('unites_industrielles')->insertGetId([
            'denomination'              => $request->denomination,
            'numero_immatriculation'    => $request->numero_immatriculation,
            'secteur_activite'          => $request->secteur_activite,
            'departement'               => $request->departement,
            'commune'                   => $request->commune,
            'adresse'                   => $request->adresse,
            'coordonnees_geographiques' => $request->coordonnees_geographiques,
            'responsable_nom'           => $request->responsable_nom,
            'responsable_fonction'      => $request->responsable_fonction,
            'email'                     => $request->email,
            'telephone'                 => $request->telephone,
            'nombre_employes'           => $request->nombre_employes ?: null,
            'capacite_production'       => $request->capacite_production,
            'actif'                     => true,
            'created_at'                => now(),
            'updated_at'                => now(),
        ]);

        $this->journal->log('creation', "Création de l'unité « {$request->denomination} »", null, [
            'table' => 'unites_industrielles',
            'id'    => $id,
            'apres' => ['denomination' => $request->denomination, 'numero_immatriculation' => $request->numero_immatriculation],
        ]);

        return redirect()->route('admin.unites.show', $id)
            ->with('statut', '« ' . $request->denomination . ' » enregistrée avec succès.');
    }

    // ── Détail : fiche complète + agréments + comptes liés ─────────────────
    public function show(int $unite): View
    {
        $u = DB::table('unites_industrielles')->where('id', $unite)->first();
        abort_if(! $u, 404, 'Unité industrielle introuvable.');

        // Agréments de cette unité, du plus récent au plus ancien
        $agrements = DB::table('agrements')
            ->where('unite_industrielle_id', $unite)
            ->orderByDesc('date_delivrance')
            ->get();

        // Comptes utilisateurs industriels rattachés à cette unité
        $comptes = DB::table('utilisateurs')
            ->where('unite_industrielle_id', $unite)
            ->where('role', 'industriel')
            ->get();

        return view('admin.unites.show', [
            'unite'    => $u,
            'agrements' => $agrements,
            'comptes'  => $comptes,
        ]);
    }

    // ── Formulaire de modification ──────────────────────────────────────────
    public function edit(int $unite): View
    {
        $u = DB::table('unites_industrielles')->where('id', $unite)->first();
        abort_if(! $u, 404, 'Unité industrielle introuvable.');

        return view('admin.unites.edit', ['unite' => $u]);
    }

    // ── Mise à jour ────────────────────────────────────────────────────────
    public function update(Request $request, int $unite): RedirectResponse
    {
        $u = DB::table('unites_industrielles')->where('id', $unite)->first();
        abort_if(! $u, 404, 'Unité industrielle introuvable.');

        $donnees = $request->validate([
            'denomination'           => ['required', 'string', 'max:200'],
            // Exclusion de l'unité courante du contrôle d'unicité
            'numero_immatriculation' => ['required', 'string', 'max:50',
                                         'unique:unites_industrielles,numero_immatriculation,' . $unite],
            'secteur_activite'       => ['required', 'string', 'max:150'],
            'regime'                 => ['nullable', 'string', 'max:100'],
            'adresse'                => ['required', 'string', 'max:255'],
            'commune'                => ['required', 'string', 'max:100'],
            'departement'            => ['required', 'string', 'max:100'],
            'telephone'              => ['nullable', 'string', 'max:20'],
            'email'                  => ['nullable', 'email', 'max:150'],
            'responsable_nom'        => ['nullable', 'string', 'max:150'],
            'responsable_fonction'   => ['nullable', 'string', 'max:100'],
        ], $this->messagesValidation());

        // Capture de l'état avant modification pour le journal
        $this->journal->log('modification', "Modification de l'unité « {$u->denomination} »", null, [
            'table' => 'unites_industrielles',
            'id'    => $unite,
            'avant' => (array) $u,
            'apres' => $donnees,
        ]);

        DB::table('unites_industrielles')
            ->where('id', $unite)
            ->update(array_merge($donnees, ['updated_at' => now()]));

        return redirect()->route('admin.unites.show', $unite)
            ->with('statut', 'Unité industrielle mise à jour avec succès.');
    }

    // ── Désactivation (soft delete — actif = false) ─────────────────────────
    public function destroy(int $unite): RedirectResponse
    {
        $u = DB::table('unites_industrielles')->where('id', $unite)->first();
        abort_if(! $u, 404, 'Unité industrielle introuvable.');

        $this->journal->log('desactivation', "Désactivation de l'unité « {$u->denomination} »", null, [
            'table' => 'unites_industrielles',
            'id'    => $unite,
            'avant' => (array) $u,
            'apres' => ['actif' => false],
        ]);

        DB::table('unites_industrielles')
            ->where('id', $unite)
            ->update(['actif' => false, 'updated_at' => now()]);

        return redirect()->route('admin.unites.index')
            ->with('statut', '« ' . $u->denomination . ' » a été désactivée.');
    }

    // ── Messages de validation partagés ────────────────────────────────────
    private function messagesValidation(): array
    {
        return [
            'denomination.required'          => 'La raison sociale est obligatoire.',
            'numero_immatriculation.required' => 'Le numéro RCCM est obligatoire.',
            'numero_immatriculation.unique'   => 'Ce numéro RCCM est déjà enregistré dans SIGDRI.',
            'secteur_activite.required'       => 'Le secteur d\'activité est obligatoire.',
            'departement.required'            => 'Le département est obligatoire.',
            'commune.required'                => 'La commune est obligatoire.',
            'adresse.required'                => 'Le quartier / l\'adresse est obligatoire.',
            'responsable_nom.required'        => 'Le nom du responsable est obligatoire.',
            'email.required'                  => 'L\'e-mail de contact est obligatoire.',
            'email.email'                     => 'L\'adresse e-mail n\'est pas valide.',
            'telephone.required'              => 'Le téléphone de contact est obligatoire.',
            'nombre_employes.integer'         => 'Le nombre d\'employés doit être un entier.',
            'nombre_employes.min'             => 'Le nombre d\'employés ne peut pas être négatif.',
        ];
    }

}
