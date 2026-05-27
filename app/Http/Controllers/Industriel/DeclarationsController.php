<?php

namespace App\Http\Controllers\Industriel;

use App\Http\Controllers\Controller;
use App\Services\JournalService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôleur industriel — Déclarations de production
 *
 * L'industriel peut déposer une déclaration à tout moment tant que son agrément
 * est valide. La seule contrainte d'unicité est : une déclaration par mois/année
 * par unité industrielle. Il n'y a plus de notion de "période ouverte".
 */
class DeclarationsController extends Controller
{
    public function __construct(
        private JournalService $journal,
        private NotificationService $notifications,
    ) {}

    // ── Liste des déclarations de l'industriel connecté ──────────────────────
    public function index(): View
    {
        $uniteId = Auth::user()->unite_industrielle_id;

        $declarations = DB::table('declarations')
            ->where('unite_industrielle_id', $uniteId)
            ->select(
                'id', 'numero_declaration', 'statut',
                'date_soumission', 'date_validation',
                'chiffre_affaires_total', 'motif_rejet',
                'mois', 'annee'
            )
            ->orderByDesc('annee')
            ->orderByDesc('mois')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('industriel.declarations.index', compact('declarations'));
    }

    // ── Formulaire de nouvelle déclaration ────────────────────────────────────
    public function create(): View|RedirectResponse
    {
        $uniteId = Auth::user()->unite_industrielle_id;

        // Vérification : agrément valide et non expiré
        $agrement = DB::table('agrements')
            ->where('unite_industrielle_id', $uniteId)
            ->where('statut', 'valide')
            ->where(function ($q) {
                $q->whereNull('date_expiration')
                  ->orWhere('date_expiration', '>=', now()->toDateString());
            })
            ->first();

        if (! $agrement) {
            return redirect()->route('industriel.declarations.index')
                ->with('erreur', 'Votre agrément est expiré ou suspendu. Vous ne pouvez pas soumettre de déclaration.');
        }

        // Valeurs par défaut : mois et année en cours
        $moisDefaut  = (int) now()->format('n');
        $anneeDefaut = (int) now()->format('Y');

        // Catalogue des produits de l'unité (pour pré-remplir les lignes)
        $produits = DB::table('produits')
            ->where('unite_industrielle_id', $uniteId)
            ->where('actif', true)
            ->orderBy('designation')
            ->get();

        return view('industriel.declarations.create', compact('produits', 'moisDefaut', 'anneeDefaut'));
    }

    // ── Enregistrement (brouillon ou soumission) ──────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $uniteId = Auth::user()->unite_industrielle_id;

        // ── Garde-fou : agrément valide ───────────────────────────────────────
        $agrement = DB::table('agrements')
            ->where('unite_industrielle_id', $uniteId)
            ->where('statut', 'valide')
            ->where(function ($q) {
                $q->whereNull('date_expiration')
                  ->orWhere('date_expiration', '>=', now()->toDateString());
            })
            ->first();

        if (! $agrement) {
            return back()->with('erreur', 'Soumission impossible : agrément expiré ou suspendu.');
        }

        $request->validate([
            'mois'                   => ['required', 'integer', 'min:1', 'max:12'],
            'annee'                  => ['required', 'integer', 'min:2020', 'max:2100'],
            'chiffre_affaires_total' => ['nullable', 'numeric', 'min:0'],
            'observations'           => ['nullable', 'string', 'max:2000'],
            'produits'               => ['nullable', 'array'],
            'matieres'               => ['nullable', 'array'],
        ], [
            'mois.required'   => 'Le mois est obligatoire.',
            'mois.min'        => 'Le mois doit être compris entre 1 et 12.',
            'mois.max'        => 'Le mois doit être compris entre 1 et 12.',
            'annee.required'  => 'L\'année est obligatoire.',
            'annee.min'       => 'L\'année doit être supérieure ou égale à 2020.',
        ]);

        // ── Unicité : une seule déclaration par mois/année par unité ─────────
        $doublon = DB::table('declarations')
            ->where('unite_industrielle_id', $uniteId)
            ->where('mois',  $request->mois)
            ->where('annee', $request->annee)
            ->exists();

        if ($doublon) {
            $nomsMois = ['','Janvier','Février','Mars','Avril','Mai','Juin',
                         'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
            $nomMois  = $nomsMois[(int) $request->mois] ?? 'ce mois';
            $annee    = (int) $request->annee;

            return redirect()->route('industriel.declarations.create')
                ->withInput()
                ->withErrors([
                    'doublon' => "Une déclaration a déjà été soumise pour le mois de {$nomMois} {$annee}."
                               . " Vous ne pouvez soumettre qu'une seule déclaration par mois.",
                ]);
        }

        $action = $request->input('action', 'brouillon');
        $statut = $action === 'soumettre' ? 'soumise' : 'brouillon';

        // ── Génération du numéro de déclaration ───────────────────────────────
        $seq    = DB::table('declarations')->whereYear('created_at', now()->year)->count() + 1;
        $numero = 'DECL-' . now()->year . '-' . str_pad($seq, 5, '0', STR_PAD_LEFT);

        // ── Insertion de la déclaration ───────────────────────────────────────
        $id = DB::table('declarations')->insertGetId([
            'numero_declaration'     => $numero,
            'unite_industrielle_id'  => $uniteId,
            'mois'                   => (int) $request->mois,
            'annee'                  => (int) $request->annee,
            'declarant_id'           => Auth::id(),
            'statut'                 => $statut,
            'chiffre_affaires_total' => $request->chiffre_affaires_total ?? 0,
            'date_soumission'        => $statut === 'soumise' ? now() : null,
            'observations'           => $request->observations,
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        // ── Lignes de production (produits) ───────────────────────────────────
        foreach ($request->input('produits', []) as $prod) {
            $designation = trim($prod['designation'] ?? '');
            if ($designation === '') {
                continue; // ligne vide ignorée
            }

            // Si le produit n'existe pas encore dans le catalogue, on le crée
            if (! empty($prod['produit_id'])) {
                $produitId = (int) $prod['produit_id'];
            } else {
                $produitId = DB::table('produits')->insertGetId([
                    'unite_industrielle_id' => $uniteId,
                    'designation'           => $designation,
                    'unite_mesure'          => trim($prod['unite_mesure'] ?? 'unité'),
                    'actif'                 => true,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);
            }

            DB::table('lignes_declaration')->insert([
                'declaration_id'        => $id,
                'produit_id'            => $produitId,
                'quantite_produite'     => $this->toDecimal($prod['quantite_produite'] ?? 0),
                'quantite_vendue_local' => $this->toDecimal($prod['quantite_vendue_local'] ?? 0),
                'quantite_exportee'     => $this->toDecimal($prod['quantite_exportee'] ?? 0),
                'valeur_fcfa'           => $this->toDecimal($prod['valeur_fcfa'] ?? 0),
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
        }

        // ── Matières premières ────────────────────────────────────────────────
        foreach ($request->input('matieres', []) as $mat) {
            $designation = trim($mat['designation'] ?? '');
            if ($designation === '') {
                continue;
            }

            DB::table('matieres_premieres')->insert([
                'declaration_id'    => $id,
                'designation'       => $designation,
                'origine'           => in_array($mat['origine'] ?? '', ['locale', 'importee'])
                                        ? $mat['origine'] : 'locale',
                'unite_mesure'      => trim($mat['unite_mesure'] ?? 'kg'),
                'quantite_consommee'=> $this->toDecimal($mat['quantite_consommee'] ?? 0),
                'valeur_fcfa'       => $this->toDecimal($mat['valeur_fcfa'] ?? 0),
                'fournisseur'       => trim($mat['fournisseur'] ?? '') ?: null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        $actionLabel = $statut === 'soumise' ? 'soumission' : 'creation';
        $descLabel   = $statut === 'soumise' ? 'Soumission' : 'Enregistrement brouillon';
        $this->journal->log($actionLabel, "{$descLabel} de la déclaration {$numero}", null, [
            'table' => 'declarations',
            'id'    => $id,
            'apres' => ['numero_declaration' => $numero, 'statut' => $statut],
        ]);

        // Notification aux agents admin uniquement si la déclaration est soumise
        if ($statut === 'soumise') {
            $denomination = DB::table('unites_industrielles')
                ->where('id', $uniteId)
                ->value('denomination') ?? 'Unité industrielle';

            $this->notifications->notifierNouvelleDeclaration($numero, $denomination);
        }

        $msg = $statut === 'soumise'
            ? '« ' . $numero . ' » soumise avec succès. En attente de validation.'
            : '« ' . $numero . ' » enregistrée comme brouillon.';

        return redirect()->route('industriel.declarations.show', $id)->with('statut', $msg);
    }

    // ── Détail d'une déclaration ──────────────────────────────────────────────
    public function show(int $declaration): View
    {
        $uniteId = Auth::user()->unite_industrielle_id;

        $d = DB::table('declarations')
            ->where('id', $declaration)
            ->where('unite_industrielle_id', $uniteId)
            ->first();

        abort_if(! $d, 404, 'Déclaration introuvable.');

        $lignes = DB::table('lignes_declaration')
            ->join('produits', 'lignes_declaration.produit_id', '=', 'produits.id')
            ->where('lignes_declaration.declaration_id', $declaration)
            ->select('lignes_declaration.*', 'produits.designation', 'produits.unite_mesure')
            ->get();

        $matieres = DB::table('matieres_premieres')
            ->where('declaration_id', $declaration)
            ->orderBy('origine')
            ->get();

        return view('industriel.declarations.show', compact('d', 'lignes', 'matieres'));
    }

    // ── Formulaire de correction (déclaration rejetée uniquement) ─────────────
    public function edit(int $declaration): View|RedirectResponse
    {
        $uniteId = Auth::user()->unite_industrielle_id;

        $d = DB::table('declarations')
            ->where('id', $declaration)
            ->where('unite_industrielle_id', $uniteId)
            ->first();

        abort_if(! $d, 404);

        if ($d->statut !== 'rejetee') {
            return redirect()->route('industriel.declarations.show', $declaration)
                ->with('erreur', 'Seules les déclarations rejetées peuvent être corrigées.');
        }

        $lignes = DB::table('lignes_declaration')
            ->join('produits', 'lignes_declaration.produit_id', '=', 'produits.id')
            ->where('lignes_declaration.declaration_id', $declaration)
            ->select('lignes_declaration.*', 'produits.designation', 'produits.unite_mesure')
            ->get();

        $matieres = DB::table('matieres_premieres')
            ->where('declaration_id', $declaration)
            ->get();

        $produits = DB::table('produits')
            ->where('unite_industrielle_id', $uniteId)
            ->where('actif', true)
            ->orderBy('designation')
            ->get();

        return view('industriel.declarations.edit', compact('d', 'lignes', 'matieres', 'produits'));
    }

    // ── Re-soumission après correction ────────────────────────────────────────
    public function update(Request $request, int $declaration): RedirectResponse
    {
        $uniteId = Auth::user()->unite_industrielle_id;

        $d = DB::table('declarations')
            ->where('id', $declaration)
            ->where('unite_industrielle_id', $uniteId)
            ->first();

        abort_if(! $d, 404);

        if ($d->statut !== 'rejetee') {
            return back()->with('erreur', 'Seules les déclarations rejetées peuvent être corrigées.');
        }

        $request->validate([
            'chiffre_affaires_total' => ['nullable', 'numeric', 'min:0'],
            'observations'           => ['nullable', 'string', 'max:2000'],
        ]);

        $action = $request->input('action', 'brouillon');
        $statut = $action === 'soumettre' ? 'soumise' : 'brouillon';

        // Suppression des lignes et matières existantes avant recréation
        DB::table('lignes_declaration')->where('declaration_id', $declaration)->delete();
        DB::table('matieres_premieres')->where('declaration_id', $declaration)->delete();

        // Recréation des lignes de production
        foreach ($request->input('produits', []) as $prod) {
            $designation = trim($prod['designation'] ?? '');
            if ($designation === '') {
                continue;
            }

            if (! empty($prod['produit_id'])) {
                $produitId = (int) $prod['produit_id'];
            } else {
                $produitId = DB::table('produits')->insertGetId([
                    'unite_industrielle_id' => $uniteId,
                    'designation'           => $designation,
                    'unite_mesure'          => trim($prod['unite_mesure'] ?? 'unité'),
                    'actif'                 => true,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);
            }

            DB::table('lignes_declaration')->insert([
                'declaration_id'        => $declaration,
                'produit_id'            => $produitId,
                'quantite_produite'     => $this->toDecimal($prod['quantite_produite'] ?? 0),
                'quantite_vendue_local' => $this->toDecimal($prod['quantite_vendue_local'] ?? 0),
                'quantite_exportee'     => $this->toDecimal($prod['quantite_exportee'] ?? 0),
                'valeur_fcfa'           => $this->toDecimal($prod['valeur_fcfa'] ?? 0),
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
        }

        // Recréation des matières premières
        foreach ($request->input('matieres', []) as $mat) {
            $designation = trim($mat['designation'] ?? '');
            if ($designation === '') {
                continue;
            }

            DB::table('matieres_premieres')->insert([
                'declaration_id'    => $declaration,
                'designation'       => $designation,
                'origine'           => in_array($mat['origine'] ?? '', ['locale', 'importee'])
                                        ? $mat['origine'] : 'locale',
                'unite_mesure'      => trim($mat['unite_mesure'] ?? 'kg'),
                'quantite_consommee'=> $this->toDecimal($mat['quantite_consommee'] ?? 0),
                'valeur_fcfa'       => $this->toDecimal($mat['valeur_fcfa'] ?? 0),
                'fournisseur'       => trim($mat['fournisseur'] ?? '') ?: null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        $this->journal->log('correction', "Correction de la déclaration {$d->numero_declaration}", null, [
            'table' => 'declarations',
            'id'    => $declaration,
            'avant' => ['statut' => 'rejetee'],
            'apres' => ['statut' => $statut],
        ]);

        DB::table('declarations')->where('id', $declaration)->update([
            'statut'                 => $statut,
            'motif_rejet'            => null,
            'chiffre_affaires_total' => $request->chiffre_affaires_total ?? 0,
            'date_soumission'        => $statut === 'soumise' ? now() : $d->date_soumission,
            'date_validation'        => null,
            'observations'           => $request->observations,
            'updated_at'             => now(),
        ]);

        // Notification aux agents admin lors d'une re-soumission après correction
        if ($statut === 'soumise') {
            $denomination = DB::table('unites_industrielles')
                ->where('id', $uniteId)
                ->value('denomination') ?? 'Unité industrielle';

            $this->notifications->notifierNouvelleDeclaration($d->numero_declaration, $denomination);
        }

        $msg = $statut === 'soumise'
            ? '« ' . $d->numero_declaration . ' » resoumise. En attente de validation.'
            : '« ' . $d->numero_declaration . ' » enregistrée comme brouillon.';

        return redirect()->route('industriel.declarations.show', $declaration)->with('statut', $msg);
    }

    // ── Conversion sécurisée en décimal ──────────────────────────────────────
    private function toDecimal(mixed $val): float
    {
        // Accepte la virgule comme séparateur décimal (saisie française)
        return (float) str_replace(',', '.', (string) $val);
    }

}
