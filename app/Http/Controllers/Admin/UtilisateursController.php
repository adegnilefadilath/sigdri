<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\JournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Contrôleur admin — Module 6 : Gestion des utilisateurs
 *
 * Couvre l'intégralité du cycle de vie d'un compte :
 * création, consultation, modification, activation/désactivation et
 * réinitialisation du mot de passe.
 *
 * Règles métier :
 *   - Un compte de rôle 'industriel' doit obligatoirement être lié à une unité.
 *   - Un administrateur ne peut pas désactiver son propre compte.
 *   - Le compte super_admin ne peut pas être désactivé.
 */
class UtilisateursController extends Controller
{
    public function __construct(private JournalService $journal) {}

    // ── Libellés des rôles disponibles dans l'application ───────────────────
    private const ROLES = [
        'super_admin' => 'Super Administrateur',
        'admin'       => 'Administrateur',
        'agent_mic'   => 'Agent MIC',
        'decideur'    => 'Décideur',
        'industriel'  => 'Industriel',
    ];

    // ── Liste paginée avec filtres ───────────────────────────────────────────
    public function index(Request $request): View
    {
        $query = DB::table('utilisateurs')->orderByDesc('created_at');

        // Filtre par rôle exact
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filtre par statut actif / inactif
        if ($request->filled('statut')) {
            $query->where('actif', $request->statut === 'actif' ? 1 : 0);
        }

        // Recherche sur nom, prénom ou e-mail
        if ($request->filled('recherche')) {
            $terme = '%' . $request->recherche . '%';
            $query->where(function ($q) use ($terme) {
                $q->where('nom',    'like', $terme)
                  ->orWhere('prenom', 'like', $terme)
                  ->orWhere('email',  'like', $terme);
            });
        }

        $utilisateurs = $query->paginate(20)->withQueryString();

        // Compteurs pour les cartes du haut de page
        $total       = DB::table('utilisateurs')->count();
        $totalActifs = DB::table('utilisateurs')->where('actif', true)->count();

        return view('admin.utilisateurs.index', [
            'utilisateurs' => $utilisateurs,
            'roles'        => self::ROLES,
            'total'        => $total,
            'totalActifs'  => $totalActifs,
        ]);
    }

    // ── Formulaire de création ──────────────────────────────────────────────
    public function create(): View
    {
        // Seules les unités actives peuvent recevoir un compte industriel
        $unites = DB::table('unites_industrielles')
            ->where('actif', true)
            ->orderBy('denomination')
            ->get(['id', 'denomination', 'departement']);

        return view('admin.utilisateurs.create', [
            'roles'  => self::ROLES,
            'unites' => $unites,
        ]);
    }

    // ── Enregistrement d'un nouveau compte ──────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'nom'                   => ['required', 'string', 'max:100'],
            'prenom'                => ['required', 'string', 'max:100'],
            'email'                 => ['required', 'email', 'max:200', 'unique:utilisateurs,email'],
            'role'                  => ['required', Rule::in(array_keys(self::ROLES))],
            'unite_industrielle_id' => ['required_if:role,industriel', 'nullable', 'exists:unites_industrielles,id'],
            'mot_de_passe'          => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'nom.required'                      => 'Le nom est obligatoire.',
            'prenom.required'                   => 'Le prénom est obligatoire.',
            'email.required'                    => 'L\'adresse e-mail est obligatoire.',
            'email.unique'                      => 'Cette adresse e-mail est déjà utilisée.',
            'role.required'                     => 'Le rôle est obligatoire.',
            'role.in'                           => 'Le rôle sélectionné est invalide.',
            'unite_industrielle_id.required_if' => 'L\'unité industrielle est obligatoire pour un compte industriel.',
            'unite_industrielle_id.exists'      => 'L\'unité industrielle sélectionnée n\'existe pas.',
            'mot_de_passe.required'             => 'Le mot de passe est obligatoire.',
            'mot_de_passe.min'                  => 'Le mot de passe doit contenir au moins 8 caractères.',
            'mot_de_passe.confirmed'            => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        // Seul le rôle 'industriel' peut avoir une unité liée
        if ($donnees['role'] !== 'industriel') {
            $donnees['unite_industrielle_id'] = null;
        }

        $nouvelId = DB::table('utilisateurs')->insertGetId([
            'nom'                   => $donnees['nom'],
            'prenom'                => $donnees['prenom'],
            'email'                 => $donnees['email'],
            'role'                  => $donnees['role'],
            'unite_industrielle_id' => $donnees['unite_industrielle_id'] ?? null,
            'mot_de_passe'          => Hash::make($donnees['mot_de_passe']),
            'actif'                 => true,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        $this->journal->log('creation', "Création du compte {$donnees['prenom']} {$donnees['nom']}", null, [
            'table' => 'utilisateurs',
            'id'    => $nouvelId,
            'apres' => ['email' => $donnees['email'], 'role' => $donnees['role']],
        ]);

        return redirect()->route('admin.utilisateurs.index')
            ->with('statut', 'Compte utilisateur créé avec succès.');
    }

    // ── Détail d'un utilisateur + historique de connexions ──────────────────
    public function show(int $utilisateur): View
    {
        $u = DB::table('utilisateurs')->where('id', $utilisateur)->first();
        abort_if(! $u, 404, 'Utilisateur introuvable.');

        // Dix dernières sessions enregistrées dans la table sessions (driver DB)
        $connexions = DB::table('sessions')
            ->where('user_id', $utilisateur)
            ->orderByDesc('last_activity')
            ->limit(10)
            ->get(['ip_address', 'user_agent', 'last_activity']);

        // Unité industrielle liée (uniquement pour le rôle industriel)
        $unite = $u->unite_industrielle_id
            ? DB::table('unites_industrielles')->where('id', $u->unite_industrielle_id)->first()
            : null;

        return view('admin.utilisateurs.show', [
            'utilisateur' => $u,
            'connexions'  => $connexions,
            'unite'       => $unite,
            'roles'       => self::ROLES,
        ]);
    }

    // ── Formulaire de modification ───────────────────────────────────────────
    public function edit(int $utilisateur): View
    {
        $u = DB::table('utilisateurs')->where('id', $utilisateur)->first();
        abort_if(! $u, 404, 'Utilisateur introuvable.');

        $unites = DB::table('unites_industrielles')
            ->where('actif', true)
            ->orderBy('denomination')
            ->get(['id', 'denomination', 'departement']);

        return view('admin.utilisateurs.edit', [
            'utilisateur' => $u,
            'roles'       => self::ROLES,
            'unites'      => $unites,
        ]);
    }

    // ── Mise à jour d'un compte existant ────────────────────────────────────
    public function update(Request $request, int $utilisateur): RedirectResponse
    {
        $u = DB::table('utilisateurs')->where('id', $utilisateur)->first();
        abort_if(! $u, 404, 'Utilisateur introuvable.');

        $donnees = $request->validate([
            'nom'                   => ['required', 'string', 'max:100'],
            'prenom'                => ['required', 'string', 'max:100'],
            'email'                 => ['required', 'email', 'max:200',
                                        Rule::unique('utilisateurs', 'email')->ignore($utilisateur)],
            'role'                  => ['required', Rule::in(array_keys(self::ROLES))],
            'unite_industrielle_id' => ['required_if:role,industriel', 'nullable', 'exists:unites_industrielles,id'],
            // Mot de passe optionnel : laissé vide = inchangé
            'mot_de_passe'          => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique'                      => 'Cette adresse e-mail est déjà utilisée par un autre compte.',
            'role.in'                           => 'Le rôle sélectionné est invalide.',
            'unite_industrielle_id.required_if' => 'L\'unité industrielle est obligatoire pour un compte industriel.',
            'mot_de_passe.min'                  => 'Le mot de passe doit contenir au moins 8 caractères.',
            'mot_de_passe.confirmed'            => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $miseAJour = [
            'nom'                   => $donnees['nom'],
            'prenom'                => $donnees['prenom'],
            'email'                 => $donnees['email'],
            'role'                  => $donnees['role'],
            'unite_industrielle_id' => $donnees['role'] === 'industriel'
                                        ? ($donnees['unite_industrielle_id'] ?? null)
                                        : null,
            'updated_at'            => now(),
        ];

        // Changement de mot de passe uniquement si le champ est rempli
        if (! empty($donnees['mot_de_passe'])) {
            $miseAJour['mot_de_passe'] = Hash::make($donnees['mot_de_passe']);
        }

        DB::table('utilisateurs')->where('id', $utilisateur)->update($miseAJour);

        $this->journal->log('modification', "Modification du compte {$donnees['prenom']} {$donnees['nom']}", null, [
            'table' => 'utilisateurs',
            'id'    => $utilisateur,
            'apres' => ['email' => $donnees['email'], 'role' => $donnees['role']],
        ]);

        return redirect()->route('admin.utilisateurs.show', $utilisateur)
            ->with('statut', 'Compte mis à jour avec succès.');
    }

    // ── Basculer le statut actif / inactif ──────────────────────────────────
    public function toggleStatut(int $utilisateur): RedirectResponse
    {
        $u = DB::table('utilisateurs')->where('id', $utilisateur)->first();
        abort_if(! $u, 404, 'Utilisateur introuvable.');

        // Un administrateur ne peut pas désactiver son propre compte
        if (Auth::id() === $utilisateur) {
            return back()->with('erreur', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        // Le compte super_admin est protégé contre toute désactivation
        if ($u->role === 'super_admin') {
            return back()->with('erreur', 'Le compte Super Administrateur ne peut pas être désactivé.');
        }

        $nouveauStatut = ! $u->actif;

        DB::table('utilisateurs')
            ->where('id', $utilisateur)
            ->update(['actif' => $nouveauStatut, 'updated_at' => now()]);

        $message = $nouveauStatut ? 'Compte activé avec succès.' : 'Compte désactivé avec succès.';

        return back()->with('statut', $message);
    }

    // ── Réinitialisation du mot de passe ────────────────────────────────────
    // Génère un mot de passe aléatoire et l'affiche une seule fois via la session.
    // En production, remplacer par un envoi d'e-mail sécurisé.
    public function resetPassword(int $utilisateur): RedirectResponse
    {
        $u = DB::table('utilisateurs')->where('id', $utilisateur)->first();
        abort_if(! $u, 404, 'Utilisateur introuvable.');

        // Mot de passe temporaire : 12 caractères alphanumériques aléatoires
        $nouveauMdp = Str::random(12);

        DB::table('utilisateurs')
            ->where('id', $utilisateur)
            ->update([
                'mot_de_passe' => Hash::make($nouveauMdp),
                'updated_at'   => now(),
            ]);

        return back()->with('statut',
            "Mot de passe réinitialisé. Mot de passe temporaire : {$nouveauMdp}"
        );
    }
}
