<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use App\Services\JournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Contrôleur d'authentification — Module 1 SIGDRI
 * Gère l'affichage du formulaire de connexion, la vérification des identifiants
 * et la déconnexion avec révocation du token Sanctum.
 */
class AuthController extends Controller
{
    public function __construct(private JournalService $journal) {}
    /**
     * Affiche le formulaire de connexion.
     * Redirige vers le tableau de bord si l'utilisateur est déjà connecté.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Traite la soumission du formulaire de connexion.
     *
     * Étapes :
     *  1. Valide les données saisies
     *  2. Tente l'authentification via le guard web (session)
     *  3. Vérifie que le compte est actif
     *  4. Crée un token Sanctum pour les appels API ultérieurs
     *  5. Met à jour la date de dernière connexion
     *  6. Redirige vers le tableau de bord
     */
    public function login(Request $request): RedirectResponse
    {
        // Validation des champs du formulaire
        $request->validate([
            'email'        => ['required', 'email', 'max:150'],
            'mot_de_passe' => ['required', 'string', 'min:6'],
        ], [
            'email.required'        => 'L\'adresse e-mail est obligatoire.',
            'email.email'           => 'L\'adresse e-mail n\'est pas valide.',
            'mot_de_passe.required' => 'Le mot de passe est obligatoire.',
            'mot_de_passe.min'      => 'Le mot de passe doit contenir au moins 6 caractères.',
        ]);

        // Auth::attempt attend toujours la clé 'password' (convention Laravel),
        // mais le modèle redirige la vérification vers la colonne 'mot_de_passe'
        // via getAuthPasswordName().
        $identifiants = [
            'email'    => $request->input('email'),
            'password' => $request->input('mot_de_passe'),
        ];

        if (! Auth::attempt($identifiants, $request->boolean('se_souvenir'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Adresse e-mail ou mot de passe incorrect.']);
        }

        /** @var Utilisateur $utilisateur */
        $utilisateur = Auth::user();

        // Vérification que le compte n'a pas été suspendu
        if (! $utilisateur->actif) {
            Auth::logout();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Votre compte est suspendu. Contactez un administrateur.']);
        }

        // Régénération du token CSRF pour prévenir la fixation de session
        $request->session()->regenerate();

        // Création du token Sanctum pour les appels API (SPA / mobile)
        $tokenSanctum = $utilisateur->createToken('sigdri-web-session')->plainTextToken;
        // Stockage en session pour que les vues puissent initier des requêtes API
        session(['api_token' => $tokenSanctum]);

        // Horodatage de la connexion pour l'audit et le tableau de bord admin
        $utilisateur->update(['derniere_connexion' => now()]);

        // Trace la connexion dans le journal d'audit
        $this->journal->logAuth(
            'connexion',
            'Connexion de ' . $utilisateur->prenom . ' ' . $utilisateur->nom,
            $utilisateur->id
        );

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Déconnecte l'utilisateur courant.
     *
     * Révoque tous ses tokens Sanctum, invalide la session HTTP
     * et redirige vers la page de connexion.
     */
    public function logout(Request $request): RedirectResponse
    {
        /** @var Utilisateur|null $utilisateur */
        $utilisateur = Auth::user();

        if ($utilisateur) {
            // Trace la déconnexion avant de détruire la session
            $this->journal->logAuth(
                'deconnexion',
                'Déconnexion de ' . $utilisateur->prenom . ' ' . $utilisateur->nom,
                $utilisateur->id
            );
            // Révocation de tous les tokens Sanctum de l'utilisateur
            $utilisateur->tokens()->delete();
        }

        Auth::logout();

        // Invalidation complète de la session pour éviter la réutilisation
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('statut', 'Vous avez été déconnecté avec succès.');
    }
}
