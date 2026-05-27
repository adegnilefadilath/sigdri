<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\JournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Contrôleur admin — Profil de l'utilisateur connecté
 *
 * Permet à tout agent admin de consulter ses informations personnelles,
 * de les modifier et de changer son mot de passe.
 * Chaque modification est tracée dans le journal d'audit.
 */
class ProfilController extends Controller
{
    public function __construct(private JournalService $journal) {}

    // ── Affichage du profil ───────────────────────────────────────────────────

    public function index(): View
    {
        $utilisateur = DB::table('utilisateurs')
            ->where('id', Auth::id())
            ->first();

        abort_if(! $utilisateur, 404, 'Compte introuvable.');

        return view('admin.profil.index', compact('utilisateur'));
    }

    // ── Mise à jour des informations personnelles ────────────────────────────

    public function update(Request $request): RedirectResponse
    {
        $id          = Auth::id();
        $utilisateur = DB::table('utilisateurs')->where('id', $id)->first();
        abort_if(! $utilisateur, 404);

        $validated = $request->validate([
            'nom'    => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email'  => ['required', 'email', 'max:255', Rule::unique('utilisateurs')->ignore($id)],
        ], [
            'nom.required'    => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.required'  => 'L\'adresse e-mail est obligatoire.',
            'email.email'     => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique'    => 'Cette adresse e-mail est déjà utilisée par un autre compte.',
        ]);

        DB::table('utilisateurs')->where('id', $id)->update([
            'nom'        => $validated['nom'],
            'prenom'     => $validated['prenom'],
            'email'      => $validated['email'],
            'updated_at' => now(),
        ]);

        $this->journal->log(
            'modification_profil',
            'Mise à jour des informations personnelles',
            $id,
            ['table' => 'utilisateurs', 'id' => $id],
        );

        return back()->with('statut', 'Vos informations ont été mises à jour.');
    }

    // ── Changement du mot de passe ────────────────────────────────────────────

    public function updatePassword(Request $request): RedirectResponse
    {
        $id          = Auth::id();
        $utilisateur = DB::table('utilisateurs')->where('id', $id)->first();
        abort_if(! $utilisateur, 404);

        $request->validate([
            'ancien_mot_de_passe'                  => ['required'],
            'nouveau_mot_de_passe'                 => ['required', 'string', 'min:8', 'confirmed'],
            'nouveau_mot_de_passe_confirmation'    => ['required'],
        ], [
            'ancien_mot_de_passe.required'               => 'L\'ancien mot de passe est obligatoire.',
            'nouveau_mot_de_passe.required'              => 'Le nouveau mot de passe est obligatoire.',
            'nouveau_mot_de_passe.min'                   => 'Le nouveau mot de passe doit comporter au moins 8 caractères.',
            'nouveau_mot_de_passe.confirmed'             => 'La confirmation ne correspond pas au nouveau mot de passe.',
            'nouveau_mot_de_passe_confirmation.required' => 'La confirmation du mot de passe est obligatoire.',
        ]);

        // Vérification de l'ancien mot de passe avant tout changement
        if (! Hash::check($request->ancien_mot_de_passe, $utilisateur->mot_de_passe)) {
            return back()
                ->withErrors(['ancien_mot_de_passe' => 'L\'ancien mot de passe est incorrect.'])
                ->with('onglet_actif', 'password');
        }

        DB::table('utilisateurs')->where('id', $id)->update([
            'mot_de_passe' => Hash::make($request->nouveau_mot_de_passe),
            'updated_at'   => now(),
        ]);

        $this->journal->log(
            'changement_mot_de_passe',
            'Changement du mot de passe',
            $id,
            ['table' => 'utilisateurs', 'id' => $id],
        );

        return back()->with('statut_mdp', 'Mot de passe changé avec succès.');
    }
}
