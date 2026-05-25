<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôleur — Gestion des agréments industriels (espace admin)
 *
 * Fichier nommé AgrementController.php (sans accent) pour la compatibilité
 * des systèmes de fichiers Windows/Linux avec l'autoloader PSR-4.
 *
 * Actions couvertes :
 *  - CRUD complet (création avec génération de numéro, lecture, modification)
 *  - Suspension avec motif obligatoire (tracé dans journaux)
 *  - Réactivation d'un agrément suspendu
 */
class AgrementController extends Controller
{
    // ── Liste avec filtres et compteurs ────────────────────────────────────
    public function index(Request $request): View
    {
        $query = DB::table('agrements')
            ->join('unites_industrielles', 'agrements.unite_industrielle_id', '=', 'unites_industrielles.id')
            ->select(
                'agrements.*',
                'unites_industrielles.denomination as denomination_unite'
            )
            ->orderByDesc('agrements.created_at');

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('agrements.statut', $request->statut);
        }

        // Filtre agréments qui expirent dans les 30 prochains jours
        if ($request->filled('expiration') && $request->expiration === 'bientot') {
            $query->whereBetween('agrements.date_expiration', [
                now()->toDateString(),
                now()->addDays(30)->toDateString(),
            ]);
        }

        // Filtre par unité industrielle spécifique
        if ($request->filled('unite_id')) {
            $query->where('agrements.unite_industrielle_id', (int) $request->unite_id);
        }

        $agrements = $query->paginate(20)->withQueryString();

        // Compteurs pour les onglets de filtrage rapide
        $compteurs = [
            'total'    => DB::table('agrements')->count(),
            'valide'   => DB::table('agrements')->where('statut', 'valide')->count(),
            'expire'   => DB::table('agrements')->where('statut', 'expire')->count(),
            'suspendu' => DB::table('agrements')->where('statut', 'suspendu')->count(),
        ];

        return view('admin.agrements.index', compact('agrements', 'compteurs'));
    }

    // ── Formulaire de création ──────────────────────────────────────────────
    public function create(Request $request): View
    {
        // La liste des unités actives pour le select
        $unites = DB::table('unites_industrielles')
            ->where('actif', true)
            ->orderBy('denomination')
            ->get(['id', 'denomination', 'numero_immatriculation']);

        // Pré-sélection d'une unité via le paramètre query ?unite_id=X
        // (utilisé depuis la page de détail d'une unité)
        $unitePreselectionnee = $request->filled('unite_id') ? (int) $request->unite_id : null;

        return view('admin.agrements.create', compact('unites', 'unitePreselectionnee'));
    }

    // ── Enregistrement avec génération automatique du numéro ───────────────
    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'unite_industrielle_id' => ['required', 'integer', 'exists:unites_industrielles,id'],
            'type_agrement'         => ['required', 'string', 'max:150'],
            'date_delivrance'       => ['required', 'date'],
            'date_expiration'       => ['nullable', 'date', 'after:date_delivrance'],
            'observations'          => ['nullable', 'string', 'max:2000'],
        ], [
            'unite_industrielle_id.required' => 'L\'unité industrielle est obligatoire.',
            'unite_industrielle_id.exists'   => 'Unité industrielle introuvable.',
            'type_agrement.required'         => 'Le type d\'agrément est obligatoire.',
            'date_delivrance.required'       => 'La date de délivrance est obligatoire.',
            'date_delivrance.date'           => 'La date de délivrance n\'est pas valide.',
            'date_expiration.after'          => 'La date d\'expiration doit être postérieure à la date de délivrance.',
        ]);

        $numero = $this->genererNumero();

        $id = DB::table('agrements')->insertGetId(array_merge($donnees, [
            'numero_agrement' => $numero,
            'statut'          => 'valide',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]));

        $this->journaliser('creation', 'agrements', $id, null,
            array_merge($donnees, ['numero_agrement' => $numero, 'statut' => 'valide']));

        return redirect()->route('admin.agrements.show', $id)
            ->with('statut', "Agrément $numero créé avec succès.");
    }

    // ── Détail d'un agrément + historique des actions ──────────────────────
    public function show(int $agrement): View
    {
        $a = DB::table('agrements')
            ->join('unites_industrielles', 'agrements.unite_industrielle_id', '=', 'unites_industrielles.id')
            ->select(
                'agrements.*',
                'unites_industrielles.denomination as denomination_unite',
                'unites_industrielles.departement  as departement_unite',
                'unites_industrielles.id           as unite_id'
            )
            ->where('agrements.id', $agrement)
            ->first();

        abort_if(! $a, 404, 'Agrément introuvable.');

        // Journal d'audit trié du plus récent au plus ancien
        $historique = DB::table('journaux')
            ->leftJoin('utilisateurs', 'journaux.utilisateur_id', '=', 'utilisateurs.id')
            ->select(
                'journaux.*',
                DB::raw("TRIM(CONCAT(COALESCE(utilisateurs.prenom,''), ' ', COALESCE(utilisateurs.nom,''))) as auteur")
            )
            ->where('journaux.table_concernee', 'agrements')
            ->where('journaux.enregistrement_id', $agrement)
            ->orderByDesc('journaux.created_at')
            ->get();

        return view('admin.agrements.show', compact('a', 'historique'));
    }

    // ── Formulaire de modification ──────────────────────────────────────────
    public function edit(int $agrement): View
    {
        $a = DB::table('agrements')->where('id', $agrement)->first();
        abort_if(! $a, 404, 'Agrément introuvable.');

        $unites = DB::table('unites_industrielles')
            ->where('actif', true)
            ->orderBy('denomination')
            ->get(['id', 'denomination', 'numero_immatriculation']);

        return view('admin.agrements.edit', compact('a', 'unites'));
    }

    // ── Mise à jour ────────────────────────────────────────────────────────
    public function update(Request $request, int $agrement): RedirectResponse
    {
        $a = DB::table('agrements')->where('id', $agrement)->first();
        abort_if(! $a, 404, 'Agrément introuvable.');

        $donnees = $request->validate([
            'unite_industrielle_id' => ['required', 'integer', 'exists:unites_industrielles,id'],
            'type_agrement'         => ['required', 'string', 'max:150'],
            'date_delivrance'       => ['required', 'date'],
            'date_expiration'       => ['nullable', 'date', 'after:date_delivrance'],
            'statut'                => ['required', 'in:valide,expire,suspendu,revoque'],
            'observations'          => ['nullable', 'string', 'max:2000'],
        ], [
            'unite_industrielle_id.required' => 'L\'unité industrielle est obligatoire.',
            'type_agrement.required'         => 'Le type d\'agrément est obligatoire.',
            'date_delivrance.required'       => 'La date de délivrance est obligatoire.',
            'date_expiration.after'          => 'La date d\'expiration doit être postérieure à la date de délivrance.',
            'statut.required'                => 'Le statut est obligatoire.',
            'statut.in'                      => 'Statut invalide.',
        ]);

        $this->journaliser('modification', 'agrements', $agrement, (array) $a, $donnees);

        DB::table('agrements')
            ->where('id', $agrement)
            ->update(array_merge($donnees, ['updated_at' => now()]));

        return redirect()->route('admin.agrements.show', $agrement)
            ->with('statut', 'Agrément mis à jour.');
    }

    // ── Suspension avec motif obligatoire ──────────────────────────────────
    public function suspendre(Request $request, int $agrement): RedirectResponse
    {
        $a = DB::table('agrements')->where('id', $agrement)->first();
        abort_if(! $a, 404, 'Agrément introuvable.');

        if ($a->statut === 'revoque') {
            return redirect()->route('admin.agrements.show', $agrement)
                ->with('erreur', 'Un agrément révoqué ne peut pas être suspendu.');
        }

        $request->validate([
            'motif' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'motif.required' => 'Le motif de suspension est obligatoire.',
            'motif.min'      => 'Le motif doit contenir au moins 10 caractères.',
        ]);

        $this->journaliser('suspension', 'agrements', $agrement,
            (array) $a,
            ['statut' => 'suspendu', 'motif_suspension' => $request->motif]
        );

        DB::table('agrements')
            ->where('id', $agrement)
            ->update(['statut' => 'suspendu', 'updated_at' => now()]);

        return redirect()->route('admin.agrements.show', $agrement)
            ->with('statut', 'Agrément suspendu. Le motif est enregistré dans le journal.');
    }

    // ── Réactivation d'un agrément suspendu ────────────────────────────────
    public function reactiver(int $agrement): RedirectResponse
    {
        $a = DB::table('agrements')->where('id', $agrement)->first();
        abort_if(! $a, 404, 'Agrément introuvable.');

        if ($a->statut !== 'suspendu') {
            return redirect()->route('admin.agrements.show', $agrement)
                ->with('erreur', 'Seul un agrément suspendu peut être réactivé.');
        }

        $this->journaliser('reactivation', 'agrements', $agrement, (array) $a, ['statut' => 'valide']);

        DB::table('agrements')
            ->where('id', $agrement)
            ->update(['statut' => 'valide', 'updated_at' => now()]);

        return redirect()->route('admin.agrements.show', $agrement)
            ->with('statut', 'Agrément réactivé. La vérification quotidienne le passera automatiquement à "expiré" si la date est dépassée.');
    }

    // ── Génération automatique du numéro d'agrément ────────────────────────
    // Format : AGR-{ANNÉE}-{SÉQUENCE sur 3 chiffres}
    private function genererNumero(): string
    {
        $annee    = date('Y');
        $compteur = DB::table('agrements')
            ->where('numero_agrement', 'like', "AGR-{$annee}-%")
            ->count();

        return 'AGR-' . $annee . '-' . str_pad($compteur + 1, 3, '0', STR_PAD_LEFT);
    }

    // ── Écriture dans le journal d'audit ────────────────────────────────────
    private function journaliser(
        string $action,
        string $table,
        int    $id,
        ?array $avant,
        ?array $apres
    ): void {
        DB::table('journaux')->insert([
            'utilisateur_id'    => Auth::id(),
            'action'            => $action,
            'table_concernee'   => $table,
            'enregistrement_id' => $id,
            'anciennes_valeurs' => $avant ? json_encode($avant) : null,
            'nouvelles_valeurs' => $apres ? json_encode($apres) : null,
            'ip_address'        => request()->ip(),
            'user_agent'        => request()->userAgent(),
            'created_at'        => now(),
        ]);
    }
}
