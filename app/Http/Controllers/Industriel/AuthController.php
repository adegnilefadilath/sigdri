<?php

namespace App\Http\Controllers\Industriel;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Contrôleur d'authentification — Espace Industriel SIGDRI
 *
 * Gère la connexion et la déconnexion des comptes de rôle 'industriel'.
 * Distinct de l'AuthController admin pour isoler les espaces et
 * appliquer des vérifications métier spécifiques (rôle, unité liée).
 */
class AuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion de l'espace industriel.
     * Redirige vers le dashboard industriel si déjà connecté en tant qu'industriel.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->role === 'industriel') {
            return redirect()->route('industriel.dashboard');
        }

        return view('industriel.auth.login');
    }

    /**
     * Traite la soumission du formulaire de connexion industriel.
     *
     * Étapes :
     *  1. Validation : numéro d'agrément + mot de passe
     *  2. Recherche de l'agrément dans la table agrements
     *  3. Vérification du statut de l'agrément (valide / expiré / suspendu)
     *  4. Vérification de la date d'expiration
     *  5. Récupération de l'utilisateur industriel lié à l'unité de l'agrément
     *  6. Vérification du mot de passe et de l'état du compte
     *  7. Connexion, token Sanctum, redirection dashboard
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'numero_agrement' => ['required', 'string', 'max:100'],
            'mot_de_passe'    => ['required', 'string', 'min:6'],
        ], [
            'numero_agrement.required' => 'Le numéro d\'agrément est obligatoire.',
            'mot_de_passe.required'    => 'Le mot de passe est obligatoire.',
        ]);

        // ── 1. Recherche de l'agrément ────────────────────────────────────────
        $agrement = DB::table('agrements')
            ->where('numero_agrement', $request->input('numero_agrement'))
            ->first();

        if (! $agrement) {
            return back()
                ->withInput($request->only('numero_agrement'))
                ->withErrors(['numero_agrement' => 'Numéro d\'agrément introuvable.']);
        }

        // ── 2. Vérification du statut de l'agrément ───────────────────────────
        if ($agrement->statut === 'suspendu') {
            return back()
                ->withInput($request->only('numero_agrement'))
                ->withErrors(['numero_agrement' => 'Votre agrément est suspendu. Contactez le Ministère.']);
        }

        if ($agrement->statut === 'expire' || now()->toDateString() > $agrement->date_expiration) {
            return back()
                ->withInput($request->only('numero_agrement'))
                ->withErrors(['numero_agrement' => 'Votre agrément est expiré. Contactez le Ministère.']);
        }

        // ── 3. Récupération de l'utilisateur industriel lié à l'unité ─────────
        /** @var Utilisateur|null $utilisateur */
        $utilisateur = Utilisateur::where('unite_industrielle_id', $agrement->unite_industrielle_id)
            ->where('role', 'industriel')
            ->first();

        if (! $utilisateur) {
            return back()
                ->withInput($request->only('numero_agrement'))
                ->withErrors(['numero_agrement' => 'Aucun compte industriel associé à cet agrément.']);
        }

        // ── 4. Vérification du mot de passe ───────────────────────────────────
        if (! Hash::check($request->input('mot_de_passe'), $utilisateur->mot_de_passe)) {
            return back()
                ->withInput($request->only('numero_agrement'))
                ->withErrors(['mot_de_passe' => 'Mot de passe incorrect.']);
        }

        // ── 5. Vérification que le compte est actif ───────────────────────────
        if (! $utilisateur->actif) {
            return back()
                ->withInput($request->only('numero_agrement'))
                ->withErrors(['numero_agrement' => 'Votre compte est suspendu. Contactez l\'administration.']);
        }

        // ── 6. Connexion ──────────────────────────────────────────────────────
        Auth::login($utilisateur, $request->boolean('se_souvenir'));
        $request->session()->regenerate();

        // Stockage en session de la dénomination pour la sidebar
        session(['unite_denomination' => DB::table('unites_industrielles')
            ->where('id', $utilisateur->unite_industrielle_id)
            ->value('denomination')]);

        // Token Sanctum pour les appels API
        $token = $utilisateur->createToken('sigdri-industriel-session')->plainTextToken;
        session(['api_token' => $token]);

        // Horodatage de connexion
        $utilisateur->update(['derniere_connexion' => now()]);

        return redirect()->intended(route('industriel.dashboard'));
    }

    /**
     * Déconnecte l'industriel : révoque les tokens Sanctum et invalide la session.
     */
    public function logout(Request $request): RedirectResponse
    {
        /** @var Utilisateur|null $utilisateur */
        $utilisateur = Auth::user();

        if ($utilisateur) {
            $utilisateur->tokens()->delete();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('industriel.login')
            ->with('statut', 'Vous avez été déconnecté de l\'espace industriel.');
    }
}
